<?php

namespace App\Console\Commands;

use App\Services\ClientiCsvGeoEnricher;
use Illuminate\Console\Command;

class ClientiEnrichGeoCsvCommand extends Command
{
    protected $signature = 'clienti:enrich-csv-geo
                            {input : Percorso del CSV input (assoluto o relativo alla root progetto)}
                            {--output= : Percorso CSV output arricchito}
                            {--report= : Percorso CSV report righe irrisolte}';

    protected $description = 'Arricchisce un CSV clienti esterno completando i campi geo mancanti quando deducibili.';

    public function handle(ClientiCsvGeoEnricher $enricher): int
    {
        $inputArg = (string) $this->argument('input');
        $inputPath = $this->toAbsolutePath($inputArg);

        $outputOpt = (string) ($this->option('output') ?: '');
        $reportOpt = (string) ($this->option('report') ?: '');

        $outputPath = $outputOpt !== ''
            ? $this->toAbsolutePath($outputOpt)
            : $this->defaultOutputPath($inputPath, '_geo_enriched.csv');

        $reportPath = $reportOpt !== ''
            ? $this->toAbsolutePath($reportOpt)
            : $this->defaultOutputPath($inputPath, '_geo_unresolved.csv');

        try {
            $stats = $enricher->enrich($inputPath, $outputPath, $reportPath);
        } catch (\Throwable $e) {
            $this->error('Errore durante l\'arricchimento CSV: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Arricchimento completato');
        $this->line(' - Totale righe: ' . $stats['totale']);
        $this->line(' - Righe aggiornate: ' . $stats['aggiornati']);
        $this->line(' - Righe con campi geo ancora mancanti: ' . $stats['irrisolti']);
        $this->line(' - Output CSV: ' . $stats['output']);
        $this->line(' - Report irrisolti: ' . $stats['report']);

        return self::SUCCESS;
    }

    private function toAbsolutePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function defaultOutputPath(string $inputPath, string $suffix): string
    {
        $dir = dirname($inputPath);
        $name = pathinfo($inputPath, PATHINFO_FILENAME);
        return $dir . DIRECTORY_SEPARATOR . $name . $suffix;
    }
}
