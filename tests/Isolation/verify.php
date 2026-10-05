<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
use Tests\Support\TestingEnvironment as Gate;

$info = Gate::requireIsolatedRuntime();
foreach (['DB_HOST' => 'production.invalid', 'DB_PORT' => '3306', 'DB_DATABASE' => 'development',
          'DATABASE_URL' => 'mysql://production.invalid/development', 'DB_URL' => 'mysql://127.0.0.1/development',
          'DB_SOCKET' => '/tmp/existing.sock', 'DB_READ_HOST' => 'production.invalid',
          'APP_CONFIG_CACHE' => '/tmp/config.php', 'LARAVEL_STORAGE_PATH' => '/tmp/storage',
          'VIEW_COMPILED_PATH' => '/tmp/views', 'SESSION_CONNECTION' => 'secondary',
          'APP_BASE_PATH' => '/tmp/other', 'BASE_URL' => 'https://schedinedinotifica.test',
          'ISOLATED_TOKEN' => 'forged', 'ISOLATED_RUN_ID' => 'forged'] as $key => $value) {
    $original = getenv($key);
    putenv($key.'='.$value);
    try {
        Gate::requireIsolatedRuntime();
        throw new LogicException('Override accettato: '.$key);
    } catch (RuntimeException $e) {
        if (!str_starts_with($e->getMessage(), 'TEST_ISOLATION_REQUIRED:')) {
            throw $e;
        }
    } finally {
        putenv($original === false ? $key : $key.'='.$original);
    }
}
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
Gate::configureApplication($app);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$factory = $app['db.factory'];
if (!$factory instanceof Tests\Support\IsolatedConnectionFactory) {
    throw new RuntimeException('Factory DB non protetta');
}
$config = $app['config']['database.connections.mysql'];
foreach ([['host' => 'production.invalid'], ['database' => 'development'], ['driver' => 'sqlite'],
          ['read' => ['host' => 'production.invalid']], ['write' => ['host' => 'production.invalid']],
          ['url' => 'mysql://production.invalid'], ['unix_socket' => '/tmp/mysql.sock']] as $override) {
    try {
        $factory->make(array_replace($config, $override), 'mysql');
        throw new LogicException('Configurazione pericolosa accettata');
    } catch (RuntimeException $e) {
        if (!str_starts_with($e->getMessage(), 'TEST_ISOLATION_REQUIRED:')) {
            throw $e;
        }
    }
}
try {
    $factory->make($config, 'secondary');
    throw new LogicException('Connessione secondaria accettata');
} catch (RuntimeException $e) {
    if (!str_starts_with($e->getMessage(), 'TEST_ISOLATION_REQUIRED:')) {
        throw $e;
    }
}
$pdo = $app['db']->connection()->getPdo();
$app['db']->purge();
$app['db']->reconnect()->getPdo();
$context = stream_context_create(['http' => ['timeout' => 5, 'follow_location' => 0]]);
$http = json_decode(file_get_contents($info['environment']['APP_URL'].'/__test_identity', false, $context), true);
if ($http['identity'] !== $info['identity'] || $http['pid'] !== $info['http_pid'] || $http['checkout'] !== $info['checkout']) {
    throw new RuntimeException('Identità HTTP errata');
}
echo "PASS: identità processi/DB/HTTP, riconnessione e 23 rifiuti di override prima delle migrazioni.\n";
