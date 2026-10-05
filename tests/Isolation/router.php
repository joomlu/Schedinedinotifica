<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
$info = \Tests\Support\TestingEnvironment::requireIsolatedRuntime();
header('X-Test-Isolation: '.$info['identity']);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__test_identity') {
    header('Content-Type: application/json');
    echo json_encode(['identity' => $info['identity'], 'pid' => getmypid(), 'checkout' => $info['checkout'], 'trap' => 'http://127.0.0.1:'.$info['trap_port'], 'trapHits' => $info['trap_hits'], 'deniedRequests' => $info['denied_requests']]);
    return;
}
if ($path === '/__test_external_redirect') {
    header('Location: http://127.0.0.1:'.$info['trap_port'].'/forbidden', true, 302);
    return;
}
if ($path === '/__test_local_redirect') {
    header('Location: /__test_external_redirect', true, 302);
    return;
}
$public = $info['checkout'].'/public';
$file = realpath($public.rawurldecode($path));
if ($file && str_starts_with($file, $public.'/') && is_file($file) && !str_ends_with($file, '.php')) {
    return false;
}
// public/storage è il solo link scrivibile, con destinazione verificata interna.
if (str_starts_with($path, '/storage/') && $file && str_starts_with($file, $info['checkout'].'/storage/app/public/') && is_file($file)) {
    header('Content-Type: '.mime_content_type($file));
    readfile($file);
    return;
}
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());
$response->send();
$kernel->terminate($request, $response);
