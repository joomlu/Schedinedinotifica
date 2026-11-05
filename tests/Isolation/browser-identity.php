<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
$info = \Tests\Support\TestingEnvironment::requireIsolatedRuntime();
$url = $info['environment']['APP_URL'];
$context = stream_context_create(['http' => ['timeout' => 5, 'follow_location' => 0]]);
$response = json_decode(file_get_contents($url.'/__test_identity', false, $context), true);
if (!$response || $response['identity'] !== $info['identity'] || $response['pid'] !== $info['http_pid'] || $response['checkout'] !== $info['checkout']) {
    \Tests\Support\TestingEnvironment::fail('server HTTP non attestato');
}
echo json_encode(['origin' => $url, 'proxy' => 'http://127.0.0.1:'.$info['proxy_port'], 'identity' => $info['identity']]);
