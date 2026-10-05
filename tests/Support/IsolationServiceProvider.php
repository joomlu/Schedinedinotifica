<?php

namespace Tests\Support;

use Illuminate\Support\ServiceProvider;

final class IsolationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $info = TestingEnvironment::requireIsolatedRuntime();
        $expected = $this->app['config']['database.connections.mysql'];
        $this->app->singleton('db.factory', fn ($app) => new IsolatedConnectionFactory(
            $app, $expected, $info
        ));
    }
}
