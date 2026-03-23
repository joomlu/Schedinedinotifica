<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigurazioneSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TipoClienteSeeder::class,
            GruppiSeeder::class,
            TitoloSeeder::class,
            TipoViaSeeder::class,
            RilasciatoDaSeeder::class,
            TipoDocumentoSeeder::class,
            TipoAlloggiatoSeeder::class,
            TipologieSeeder::class,
        ]);
    }
}
