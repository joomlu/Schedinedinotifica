<?php

// This must precede Composer and Laravel: providers may open connections.
require_once __DIR__.'/Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();

require_once __DIR__.'/../vendor/autoload.php';
