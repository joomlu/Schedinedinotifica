<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        require_once __DIR__.'/Support/TestingEnvironment.php';
        \Tests\Support\TestingEnvironment::requireIsolatedRuntime();

        $app = require __DIR__.'/../bootstrap/app.php';

        \Tests\Support\TestingEnvironment::configureApplication($app);
        $app->make(Kernel::class)->bootstrap();


        return $app;
    }
}
