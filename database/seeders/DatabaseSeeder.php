<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        if (config('geo.source') === 'db') {
            $this->call(GeoDataSeeder::class);
        }
        // Tipi di via di default
        $this->call(TypeStreetSeeder::class);
    }
}
