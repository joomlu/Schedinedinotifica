<?php

namespace Tests\Support;

/** Accettazione prima di Composer: un flag o un manifest non bastano. */
final class TestingEnvironment
{
    public static function fail(string $reason): never
    {
        throw new \RuntimeException('TEST_ISOLATION_REQUIRED: '.$reason);
    }

    public static function requireIsolatedRuntime(): array
    {
        $checkout = realpath(__DIR__.'/../..');
        $root = getenv('ISOLATED_ROOT');
        if (!$root || realpath($root) !== $root || !str_starts_with($root, '/private/tmp/schedine-test-')
            || $checkout !== $root.'/checkout' || glob($checkout.'/.env*')
            || file_exists($checkout.'/bootstrap/cache/config.php')) {
            self::fail('checkout temporaneo senza .env e cache richiesto');
        }
        foreach (['storage', 'public', 'vendor', 'bootstrap/cache'] as $folder) {
            if (realpath($checkout.'/'.$folder) !== $checkout.'/'.$folder) {
                self::fail('directory non isolata: '.$folder);
            }
        }
        foreach (array_keys(getenv()) as $key) {
            if (preg_match('/^(DATABASE_|REDIS_|AWS_)|^DB_(URL|SOCKET|READ_|WRITE_)|^(APP_BASE_PATH|APP_CONFIG_CACHE|APP_SERVICES_CACHE|APP_PACKAGES_CACHE|LARAVEL_STORAGE_PATH|VIEW_COMPILED_PATH|SESSION_CONNECTION|MYSQL_ATTR_SSL_CA|BASE_URL|PLAYWRIGHT_BASE_URL|REUSE_EXISTING_SERVER)$/', $key)) {
                self::fail('override non ammesso: '.$key);
            }
        }
        $socketPath = getenv('ISOLATED_BROKER');
        if ($socketPath !== $root.'/broker.sock' || filetype($socketPath) !== 'socket') {
            self::fail('supervisore locale assente');
        }
        $socket = @stream_socket_client('unix://'.$socketPath, $errno, $error, 2);
        if (!$socket) {
            self::fail('supervisore non raggiungibile');
        }
        stream_set_timeout($socket, 3);
        $challenge = bin2hex(random_bytes(32));
        fwrite($socket, json_encode(['token' => getenv('ISOLATED_TOKEN'), 'challenge' => $challenge]));
        $reply = fgets($socket, 65536);
        fclose($socket);
        $info = json_decode($reply ?: '', true);
        if (!$info || ($info['challenge'] ?? null) !== $challenge || ($info['root'] ?? null) !== $root
            || ($info['checkout'] ?? null) !== $checkout || ($info['identity'] ?? null) !== getenv('ISOLATED_RUN_ID')) {
            self::fail('attestazione del supervisore non valida');
        }
        foreach ($info['environment'] as $key => $value) {
            if (getenv($key) !== $value
                || (isset($_SERVER[$key]) && (string) $_SERVER[$key] !== $value)
                || (isset($_ENV[$key]) && (string) $_ENV[$key] !== $value)) {
                self::fail('ambiente alterato: '.$key);
            }
        }
        foreach (array_keys(getenv()) as $key) {
            if (preg_match('/^(DB_|APP_|LARAVEL_|VIEW_|SESSION_|MAIL_|ISOLATED_)/', $key)
                && !array_key_exists($key, $info['environment'])) {
                self::fail('override aggiuntivo: '.$key);
            }
        }
        foreach (['db_pid' => ['mysqld', '--no-defaults', '--datadir='.$root.'/mysql-data', '--port='.$info['db_port'], '--bind-address=127.0.0.1'],
                  'http_pid' => ['php', '127.0.0.1:'.$info['http_port'], 'tests/Isolation/router.php']] as $key => $markers) {
            $pid = (int) ($info[$key] ?? 0);
            if ($pid < 1) {
                self::fail('processo temporaneo mancante');
            }
            $command = self::process($pid, 'command=');
            foreach ($markers as $marker) {
                if (!str_contains($command, $marker)) {
                    self::fail('identità del processo errata: '.$key);
                }
            }
            if ((int) self::process($pid, 'ppid=') !== (int) $info['launcher_pid']) {
                self::fail('processo non appartenente al supervisore');
            }
        }
        // La connessione usa l'endpoint attestato, mai URL o valori DB ereditati.
        self::verifiedPdo($info);
        return $info;
    }

    private static function process(int $pid, string $field): string
    {
        $pipes = [];
        $p = proc_open(['/bin/ps', '-p', (string) $pid, '-o', $field], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!$p) {
            self::fail('impossibile verificare il processo');
        }
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($p) !== 0) {
            self::fail('processo terminato');
        }
        return trim($output);
    }

    public static function verifiedPdo(array $info): \PDO
    {
        try {
            $pdo = new \PDO('mysql:host=127.0.0.1;port='.$info['db_port'].';dbname='.$info['database'].';charset=utf8mb4',
                $info['username'], $info['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 2]);
            $identity = $pdo->query('SELECT @@datadir AS datadir, @@server_uuid AS uuid, @@port AS port, DATABASE() AS db')->fetch(\PDO::FETCH_ASSOC);
            if (realpath($identity['datadir']) !== $info['datadir'] || $identity['uuid'] !== $info['uuid']
                || (int) $identity['port'] !== $info['db_port'] || $identity['db'] !== $info['database']) {
                self::fail('identità MySQL non corrispondente (directory, UUID, porta o database)');
            }
            return $pdo;
        } catch (\PDOException $e) {
            self::fail('database temporaneo non verificabile');
        }
    }

    public static function configureApplication($app): void
    {
        $info = self::requireIsolatedRuntime();
        $app->beforeBootstrapping(\Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class, function ($app) use ($info): void {
            self::requireIsolatedRuntime();
            if ($app->environmentPath() !== $info['checkout'] || $app->environmentFile() !== '.env') {
                self::fail('percorso ambiente alterato prima di Dotenv');
            }
        });
        $app->beforeBootstrapping(\Illuminate\Foundation\Bootstrap\RegisterProviders::class, function ($app) use ($info): void {
            $config = $app['config'];
            if ($app->basePath() !== $info['checkout'] || $app->storagePath() !== $info['checkout'].'/storage'
                || $app->publicPath() !== $info['checkout'].'/public') {
                self::fail('percorsi applicativi esterni');
            }
            $connection = [
                'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $info['db_port'],
                'database' => $info['database'], 'username' => $info['username'], 'password' => $info['password'],
                'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '',
                'strict' => true, 'engine' => null, 'options' => [],
            ];
            $config->set('database.default', 'mysql');
            $config->set('database.connections', ['mysql' => $connection]);
            $config->set('database.redis', []);
            $config->set('cache.default', 'array');
            $config->set('session.driver', PHP_SAPI === 'cli' ? 'array' : 'file');
            $config->set('session.files', $app->storagePath('framework/sessions'));
            $config->set('session.domain', null);
            $config->set('session.secure', false);
            $config->set('session.cookie', 'test_'.$info['identity']);
            $config->set('view.compiled', $app->storagePath('framework/views'));
            $config->set('mail.default', 'array');
            $config->set('queue.default', 'sync');
            $config->set('filesystems.default', 'local');
            $config->set('filesystems.disks', [
                'local' => ['driver' => 'local', 'root' => $app->storagePath('app')],
                'public' => ['driver' => 'local', 'root' => $app->storagePath('app/public'),
                    'url' => $info['environment']['APP_URL'].'/storage', 'visibility' => 'public'],
            ]);
            $config->set('app.asset_url', null);
            $config->set('logging.default', 'single');
            $config->set('logging.channels.single.path', $app->storagePath('logs/laravel.log'));
            // La factory controlla anche connessioni dinamiche, read/write e riconnessioni.
            $config->set('app.providers', [...$config->get('app.providers'), IsolationServiceProvider::class]);
            $app->afterResolving(\Illuminate\Http\Client\Factory::class, function ($http): void {
                $http->preventStrayRequests();
            });
        });
    }
}
