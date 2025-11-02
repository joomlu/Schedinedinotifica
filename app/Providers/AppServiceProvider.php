<?php

namespace App\Providers;

use App\Repositories\DbGeoRepository;
use App\Repositories\GeoRepositoryInterface;
use App\Repositories\JsonGeoRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Binding del repository geografico in base alla config
        $this->app->bind(GeoRepositoryInterface::class, function () {
            $source = config('geo.source', 'json');
            if ($source === 'db') {
                return new DbGeoRepository;
            }

            return new JsonGeoRepository;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);

    }
}
