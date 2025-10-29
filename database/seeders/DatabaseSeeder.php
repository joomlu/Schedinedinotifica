<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ComuneLogoSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            QuickTestSeeder::class,
            ComuneLogoSeeder::class,
        ]);
    }
}
