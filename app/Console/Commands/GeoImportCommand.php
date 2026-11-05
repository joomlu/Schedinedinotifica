<?php

namespace App\Console\Commands;

use App\Services\GeoImportService;
use Illuminate\Console\Command;

class GeoImportCommand extends Command
{
    protected $signature = 'geo:import {--fresh : Trunca e reimporta tutte le tabelle geo_*}';

    protected $description = 'Importa dataset geografici ufficiali nelle tabelle geo_* (nazioni, regioni, province, comuni, cap).';

    public function handle(GeoImportService $service): int
    {
        $fresh = (bool) $this->option('fresh');
        if ($fresh && !$this->confirm('Confermi il truncate + reimport di tutte le tabelle geo_*?')) {
            $this->info('Operazione annullata.');
            return self::SUCCESS;
        }

        $stats = $service->import($fresh);

        $this->info('Import geo completato');
        foreach ($stats as $key => $value) {
            $this->line(" - {$key}: {$value}");
        }

        return self::SUCCESS;
    }
}
