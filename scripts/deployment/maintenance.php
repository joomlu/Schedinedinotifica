<?php
// Independent of Composer/Laravel. Generated from the reviewed deployment bundle.
$deploymentSecret = '__DEPLOY_SECRET__';
$cookie = $_COOKIE['laravel_maintenance'] ?? '';
$payload = is_string($cookie) && strlen($cookie) <= 1024
    ? json_decode(base64_decode($cookie, true) ?: '', true) : null;
if (is_array($payload) && isset($payload['expires_at'], $payload['mac'])
    && is_numeric($payload['expires_at']) && is_string($payload['mac'])
    && (int) $payload['expires_at'] >= time()
    && (int) $payload['expires_at'] <= time() + 3600
    && hash_equals(hash_hmac('sha256', (string) $payload['expires_at'], $deploymentSecret), $payload['mac'])) {
    return; // Only the private health-check cookie may enter the application.
}
http_response_code(503);
header('Content-Type: text/html; charset=UTF-8');
header('Retry-After: 60');
header('Cache-Control: no-store, max-age=0');
echo '<!doctype html><html lang="it"><meta charset="utf-8"><title>Manutenzione</title><h1>Manutenzione in corso</h1><p>Riprovare tra poco.</p><!-- __DEPLOY_MARKER__ --></html>';
exit;
