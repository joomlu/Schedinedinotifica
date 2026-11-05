<?php

namespace GeoItalia;

use GeoItalia\Commands\InstallGeoItalia;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class GeoItaliaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadHelpers();
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/geo.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'geo-italia');

        Blade::component('geo-italia::components.geo.italia', 'geo.italia');

        if ($this->app->runningInConsole()) {
            $this->commands([InstallGeoItalia::class]);
            $this->publishables();
        }
    }

    protected function publishables(): void
    {
        $base = __DIR__ . '/..';

        $this->publishes([
            "$base/resources/views/components/geo/italia.blade.php" => resource_path('views/components/geo/italia.blade.php'),
        ], 'geoitalia-views');

        $this->publishes([
            "$base/resources/js/geo-italia.js" => resource_path('js/geo-italia.js'),
        ], 'geoitalia-js');

        $this->publishes([
            "$base/database/migrations" => database_path('migrations'),
        ], 'geoitalia-migrations');

        $this->publishes([
            "$base/database/seeders/GeoItaliaSeeder.php" => database_path('seeders/GeoItaliaSeeder.php'),
        ], 'geoitalia-seeders');

        $this->publishes([
            "$base/database/data" => database_path('data'),
        ], 'geoitalia-data');

        $this->publishes([
            "$base/routes/geo.php" => base_path('routes/geo.php'),
        ], 'geoitalia-routes');

        // Publicación completa con un solo tag
        $this->publishes([
            "$base/resources/views/components/geo/italia.blade.php" => resource_path('views/components/geo/italia.blade.php'),
            "$base/resources/js/geo-italia.js" => resource_path('js/geo-italia.js'),
            "$base/database/migrations" => database_path('migrations'),
            "$base/database/seeders/GeoItaliaSeeder.php" => database_path('seeders/GeoItaliaSeeder.php'),
            "$base/database/data" => database_path('data'),
            "$base/routes/geo.php" => base_path('routes/geo.php'),
        ], 'geoitalia');
    }

    protected function loadHelpers(): void
    {
        $helpers = __DIR__ . '/Support/helpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }
}
