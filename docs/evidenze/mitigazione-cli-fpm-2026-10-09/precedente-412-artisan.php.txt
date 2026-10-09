<?php
// Only this reviewed runner may execute deployment Artisan commands.
// Validate configuration BEFORE service-provider boot and again before/after commands.
ini_set('display_errors', '0');
ini_set('log_errors', '0');
// Config caches contain credentials; PHP-FPM must run as the same deployment user.
umask(0077);

function requireSame($actual, string $expected, string $label): void {
    if ($actual !== $expected) { throw new RuntimeException('Disallowed runtime path: '.$label); }
}
function physicalPath(string $path): void {
    $parts = explode('/', ltrim($path, '/')); $part = '';
    foreach ($parts as $piece) {
        $part .= '/'.$piece;
        if (is_link($part)) { throw new RuntimeException('Symlink in runtime path.'); }
        if (is_file($part) && stat($part)['nlink'] !== 1) { throw new RuntimeException('Hardlink in runtime path.'); }
    }
}
function identity($config, array $env): void {
    if (empty($env['APP_KEY']) || !hash_equals($env['APP_KEY'], (string) $config->get('app.key'))) {
        throw new RuntimeException('APP_KEY absent or inconsistent.');
    }
    foreach (['DB_HOST'=>'host', 'DB_PORT'=>'port', 'DB_DATABASE'=>'database', 'DB_USERNAME'=>'username', 'DB_PASSWORD'=>'password', 'DB_SOCKET'=>'unix_socket'] as $name=>$field) {
        if (array_key_exists($name, $env) && (string) $env[$name] !== (string) $config->get('database.connections.mysql.'.$field)) {
            throw new RuntimeException('Inconsistent database configuration.');
        }
    }
}
function paths($app, $config, string $root): void {
    requireSame($app->basePath(), $root, 'base');
    requireSame($app->storagePath(), $root.'/storage', 'storage');
    requireSame($app->publicPath(), $root.'/public', 'public');
    requireSame($app->bootstrapPath(), $root.'/bootstrap', 'bootstrap');
    requireSame($app->environmentPath(), $root, 'environment directory');
    requireSame($app->environmentFile(), '.env', 'environment file');
    foreach (['getCachedConfigPath'=>'config.php', 'getCachedRoutesPath'=>'routes-v7.php',
              'getCachedEventsPath'=>'events.php', 'getCachedServicesPath'=>'services.php',
              'getCachedPackagesPath'=>'packages.php'] as $method=>$file) {
        requireSame($app->$method(), $root.'/bootstrap/cache/'.$file, $method);
        physicalPath($app->$method());
    }
    $expected = [
        'view.compiled'=>'storage/framework/views',
        'session.files'=>'storage/framework/sessions',
        'cache.stores.file.path'=>'storage/framework/cache/data',
        'filesystems.disks.local.root'=>'storage/app',
        'filesystems.disks.public.root'=>'storage/app/public',
    ];
    foreach ($expected as $key=>$path) {
        requireSame($config->get($key), $root.'/'.$path, $key); physicalPath($root.'/'.$path);
    }
    foreach (['cache.stores.file.driver'=>'file', 'filesystems.disks.local.driver'=>'local',
              'filesystems.disks.public.driver'=>'local', 'logging.channels.single.driver'=>'single',
              'logging.channels.daily.driver'=>'daily', 'logging.channels.stack.driver'=>'stack'] as $key=>$driver) {
        requireSame($config->get($key), $driver, $key);
    }
    $lockPath = $config->get('cache.stores.file.lock_path');
    if ($lockPath !== null) { requireSame($lockPath, $root.'/storage/framework/cache/data', 'cache lock'); }
    foreach ($config->get('cache.stores', []) as $name=>$store) {
        if (($store['driver'] ?? '') === 'file' && $name !== 'file') {
            throw new RuntimeException('Extra file cache store.');
        }
    }
    if ($config->get('view.paths') !== [$root.'/resources/views']) { throw new RuntimeException('Unexpected view source paths.'); }
    if ($config->get('filesystems.links') !== [$root.'/public/storage'=>$root.'/storage/app/public']) {
        throw new RuntimeException('Unexpected storage links.');
    }
    foreach ($config->get('filesystems.disks', []) as $name=>$disk) {
        if (($disk['driver'] ?? '') === 'local' && !in_array($name, ['local','public'], true)) {
            throw new RuntimeException('Extra local filesystem disk.');
        }
    }
    if (!in_array($config->get('logging.default'), ['single','daily','stack'], true)
        || array_diff($config->get('logging.channels.stack.channels', []), ['single','daily'])) {
        throw new RuntimeException('Unsupported logging backend.');
    }
    foreach ($config->get('logging.channels', []) as $name=>$channel) {
        if (isset($channel['path'])) {
            requireSame($channel['path'], $root.'/storage/logs/laravel.log', 'log '.$name);
            physicalPath($channel['path']);
        }
        if (isset($channel['with']['stream']) && $channel['with']['stream'] !== 'php://stderr') {
            throw new RuntimeException('Unexpected log stream.');
        }
    }
    if ($config->get('app.env') !== 'production' || $config->get('app.debug')
        || $config->get('app.url') !== 'https://schedinedinotifica.tanggo.software'
        || $config->get('database.default') !== 'mysql' || $config->get('queue.default') !== 'sync'
        || $config->get('cache.default') !== 'file' || $config->get('session.driver') !== 'file'
        || $config->get('filesystems.default') !== 'local' || $config->get('app.maintenance.driver', 'file') !== 'file') {
        throw new RuntimeException('Unsupported production configuration.');
    }
}

try {
    $root = $argv[1]; $operation = $argv[2];
    require $root.'/vendor/autoload.php';
    $env = Dotenv\Dotenv::createArrayBacked($root)->load();
    Dotenv\Dotenv::createImmutable($root)->load();
    // Check both dotenv and process overrides, even when a cached config hides them.
    $pathVariables = ['APP_BASE_PATH'=>$root, 'LARAVEL_STORAGE_PATH'=>$root.'/storage',
        'VIEW_COMPILED_PATH'=>$root.'/storage/framework/views',
        'APP_CONFIG_CACHE'=>$root.'/bootstrap/cache/config.php',
        'APP_ROUTES_CACHE'=>$root.'/bootstrap/cache/routes-v7.php',
        'APP_EVENTS_CACHE'=>$root.'/bootstrap/cache/events.php',
        'APP_SERVICES_CACHE'=>$root.'/bootstrap/cache/services.php',
        'APP_PACKAGES_CACHE'=>$root.'/bootstrap/cache/packages.php'];
    foreach ($pathVariables as $name=>$expected) {
        foreach ([$env[$name] ?? null, $_ENV[$name] ?? null, $_SERVER[$name] ?? null, getenv($name)] as $value) {
            if ($value !== null && $value !== false) { requireSame($value, $expected, $name); }
        }
    }
    $app = require $root.'/bootstrap/app.php';
    // No providers or project commands have booted yet.
    $app->bootstrapWith([
        Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    ]);
    paths($app, $app['config'], $root);
    // config:cache starts a fresh app internally. Validate its uncached inputs too,
    // before any provider or cache command can use a previously hidden path.
    $loader = new class extends Illuminate\Foundation\Bootstrap\LoadConfiguration {
        public function fresh($app) {
            $config = new Illuminate\Config\Repository;
            $this->loadConfigurationFiles($app, $config);
            return $config;
        }
    };
    $freshConfig = $loader->fresh($app);
    paths($app, $freshConfig, $root);
    identity($freshConfig, $env);
    identity($app['config'], $env);
    if (($env['APP_ENV'] ?? '') !== 'production' || !in_array(strtolower($env['APP_DEBUG'] ?? ''), ['false','0'], true)) {
        throw new RuntimeException('Unsafe .env flags.');
    }
    if (!str_starts_with($app->version(), '11.')) { throw new RuntimeException('Laravel 11 required.'); }
    $app->bootstrapWith([
        Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    paths($app, $app['config'], $root);
    identity($app['config'], $env);
    // Filesystem warnings must not become successful cache generation.
    set_error_handler(static function ($severity, $message, $file, $line) {
        if (error_reporting() & $severity) { throw new ErrorException('Deployment PHP operation failed', 0, $severity, $file, $line); }
        return false;
    });
    if ($operation === 'pending') {
        $db = $app->make('db')->connection();
        $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
        try {
            if (stripos($db->selectOne('SELECT VERSION() AS version')->version, 'MariaDB') === false) { throw new RuntimeException('MariaDB required.'); }
            $repo = $app->make('migration.repository');
            if (!$repo->repositoryExists()) { throw new RuntimeException('Missing migration history.'); }
            $ran = $repo->getRan();
        } finally { $db->rollBack(); }
        $files = $app->make('migrator')->getMigrationFiles([$argv[3]]);
        if (array_diff($ran, array_keys($files))) { throw new RuntimeException('Applied migration missing.'); }
        echo json_encode(array_values(array_diff(array_keys($files), $ran)), JSON_THROW_ON_ERROR); exit(0);
    }
    $allowed = ['config:clear','route:clear','view:clear','event:clear','config:cache','route:cache','view:cache','event:cache',
                'package:discover','storage:link','route:list','schedule:list','migrate'];
    if (!in_array($operation, $allowed, true)) { throw new RuntimeException('Command not permitted.'); }
    $arguments = ['--no-interaction'=>true];
    if ($operation === 'migrate') {
        $names = json_decode($argv[3], true, 512, JSON_THROW_ON_ERROR);
        if (!$names) { throw new RuntimeException('Empty approved migration set.'); }
        foreach ($names as $name) { if (!preg_match('/^[A-Za-z0-9_]+$/D', $name)) { throw new RuntimeException('Invalid migration name.'); } }
        $arguments['--force'] = true;
        $arguments['--path'] = array_map(fn($name)=>'database/migrations/'.$name.'.php', $names);
    }
    $code = $kernel->call($operation, $arguments);
    if ($code !== 0) { exit($code); }
    paths($app, $app['config'], $root);
    identity($app['config'], $env);
    // Cache commands may internally hide failures: assert their filesystem postconditions too.
    $cacheFiles = ['config'=>'config.php','route'=>'routes-v7.php','event'=>'events.php'];
    foreach ($cacheFiles as $kind=>$file) {
        $path = $root.'/bootstrap/cache/'.$file;
        if ($operation === $kind.':cache' && (!is_file($path) || filesize($path) === 0)) { throw new RuntimeException('Cache not generated.'); }
        if ($operation === $kind.':clear' && file_exists($path)) { throw new RuntimeException('Cache not cleared.'); }
    }
    if ($operation === 'config:cache') {
        $cachedConfig = new Illuminate\Config\Repository(require $root.'/bootstrap/cache/config.php');
        paths($app, $cachedConfig, $root); identity($cachedConfig, $env);
    }
    if ($operation === 'view:clear') {
        $views = glob($root.'/storage/framework/views/*');
        if ($views === false || $views !== []) { throw new RuntimeException('Views not cleared.'); }
    }
    if ($operation === 'view:cache') {
        $views = glob($root.'/storage/framework/views/*.php');
        if ($views === false || $views === []) { throw new RuntimeException('Views not generated.'); }
    }
    echo "OK\n";
} catch (Throwable $e) {
    // Never print exception messages from project/DB code: they can contain secrets.
    fwrite(STDERR, "[deploy] Guarded Artisan operation failed; inspect configuration privately.\n"); exit(1);
}
