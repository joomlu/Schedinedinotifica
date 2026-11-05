<?php

namespace Tanggo\GeoAddress\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GeoAddressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/geo_address.php', 'geo_address');
    }

    public function boot(): void
    {
        // Views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'geo-address');

        // Blade components alias
        Blade::component('geo-address::components.geo-select', 'geo-address-geo-select');
        Blade::component('geo-address::components.address-fields', 'geo-address-address-fields');

        // Publish assets and config
        $this->publishes([
            __DIR__.'/../../public' => public_path('vendor/geo-address'),
        ], 'geo-address-assets');

        $this->publishes([
            __DIR__.'/../../config/geo_address.php' => config_path('geo_address.php'),
        ], 'geo-address-config');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/geo-address'),
        ], 'geo-address-views');

        // Sample routes (optional)
        if (config('geo_address.enable_sample_routes', false)) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/sample.php');
        }
    }
}
