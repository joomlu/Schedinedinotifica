<?php
// Solo checkout attestato; le prove negative devono avere figli non autorizzati.
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
$negativeEnvironment = getenv();
foreach (array_keys($negativeEnvironment) as $key) {
    if (str_starts_with($key, 'ISOLATED_')) { unset($negativeEnvironment[$key]); }
}
$commands = [
    ['python3 -B '.escapeshellarg(__DIR__.'/test_guards.py'), $negativeEnvironment],
    [escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../Safety/questura_security.php'), getenv()],
];
foreach ($commands as [$command, $environment]) {
    $process = proc_open($command, [1 => STDOUT, 2 => STDERR], $pipes, dirname(__DIR__, 2), $environment);
    if (!is_resource($process)) { exit(1); }
    $status = proc_close($process);
    if ($status !== 0) { exit($status); }
}
