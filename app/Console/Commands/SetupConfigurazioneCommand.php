<?php

namespace App\Console\Commands;

use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Gruppo;
use App\Models\RilasciatoDa;
use App\Models\TipoAlloggiato;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\TipoVia;
use App\Models\Titolo;
use App\Services\GeoImportService;
use Database\Seeders\ConfigurazioneSistemaSeeder;
use Illuminate\Console\Command;

class SetupConfigurazioneCommand extends Command
{
    protected $signature = 'configurazione:setup
                            {--with-geo : Importa anche i dataset geo_*}
                            {--fresh-geo : Trunca e reimporta i dataset geo_*}';

    protected $description = 'Popola tutte le tabelle di configurazione/dependenze usate dai componenti.';

    public function handle(GeoImportService $geoImportService): int
    {
        $this->info('Avvio setup configurazione...');

        $this->call('db:seed', [
            '--class' => ConfigurazioneSistemaSeeder::class,
            '--force' => true,
        ]);

        $runGeoImport = (bool) $this->option('with-geo') || (bool) $this->option('fresh-geo');
        if ($runGeoImport) {
            $fresh = (bool) $this->option('fresh-geo');
            if ($fresh && $this->input->isInteractive()) {
                if (! $this->confirm('Confermi il truncate + reimport di tutte le tabelle geo_*?', false)) {
                    $this->warn('Import geo saltato su richiesta.');
                    $fresh = false;
                    $runGeoImport = false;
                }
            }

            if ($runGeoImport) {
                $stats = $geoImportService->import($fresh);
                $this->info('Import geo completato:');
                foreach ($stats as $key => $value) {
                    $this->line(" - {$key}: {$value}");
                }
            }
        }

        $this->newLine();
        $this->info('Conteggi configurazione:');
        $this->line(' - tipo_cliente: ' . TipoCliente::count());
        $this->line(' - gruppi: ' . Gruppo::count());
        $this->line(' - titolo: ' . Titolo::count());
        $this->line(' - tipo_via: ' . TipoVia::count());
        $this->line(' - rilasciato_da: ' . RilasciatoDa::count());
        $this->line(' - tipo_documento: ' . TipoDocumento::count());
        $this->line(' - tipo_alloggiato: ' . TipoAlloggiato::count());
        $this->line(' - geo_nazioni: ' . GeoNazione::count());
        $this->line(' - geo_regioni: ' . GeoRegione::count());
        $this->line(' - geo_province: ' . GeoProvincia::count());
        $this->line(' - geo_comuni: ' . GeoComune::count());

        $this->info('Setup configurazione completato.');

        return self::SUCCESS;
    }
}
