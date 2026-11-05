<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle($input = new \Symfony\Component\Console\Input\ArgvInput(), new \Symfony\Component\Console\Output\ConsoleOutput());
$kernel->terminate($input, $status);
exit($status);
