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
        $this->call(StrutturaSeeder::class);
        $this->call(ConfigurazioneSistemaSeeder::class);
        $this->call(DemoSaasSeeder::class);
        $this->call(TassaEsenzioniSeeder::class);
    }
}
