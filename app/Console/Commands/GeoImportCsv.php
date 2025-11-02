<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeoImportCsv extends Command
{
    protected $signature = 'geo:import-csv';

    protected $description = 'Importa regioni, province e comuni dai CSV in reference/libreria (ANPR + comuni.csv) nelle tabelle geo_*';

    public function handle(): int
    {
        $this->info('Import CSV: avvio Database\\Seeders\\CsvGeoSeeder…');
        $exit = $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\CsvGeoSeeder',
            '--no-interaction' => true,
        ]);
        if ($exit !== self::SUCCESS) {
            $this->error('CsvGeoSeeder fallito');
            return $exit;
        }

        // Tipo Via
        $this->info('Seeding tipi di via (TypeStreetSeeder)…');
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\TypeStreetSeeder',
            '--no-interaction' => true,
        ]);

        $this->info('geo:import-csv completato.');
        return self::SUCCESS;
    }
}
