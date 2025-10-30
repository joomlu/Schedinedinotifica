<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportStati extends Command
{
    protected $signature = 'stati:import {--fresh : Truncate table before import}';

    protected $description = 'Import stati (countries) from app/stati.csv and cross-reference with gi_nazioni.json';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('Truncating stati table...');
            DB::table('stati')->truncate();
            $this->info('Table truncated.');
        }

        $csvPath = app_path('stati.csv');
        if (! file_exists($csvPath)) {
            $this->error('File app/stati.csv not found!');

            return 1;
        }

        $jsonPath = storage_path('app/gi_nazioni.json');
        $nazioniMap = [];
        if (file_exists($jsonPath)) {
            $nazioni = json_decode(file_get_contents($jsonPath), true);
            foreach ($nazioni as $n) {
                $denom = mb_strtoupper(trim($n['denominazione_nazione'] ?? ''));
                $sigla = $n['sigla_nazione'] ?? null;
                $nazioniMap[$denom] = [
                    'sigla' => $sigla,
                    'codice_istat' => $n['codice_belfiore'] ?? null,
                    'cittadinanza' => $n['denominazione_cittadinanza'] ?? null,
                ];
                // Also map by sigla for direct lookups
                if ($sigla) {
                    $nazioniMap[$sigla] = $nazioniMap[$denom];
                }
            }
        }

        $this->info('Importing stati from CSV...');
        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_shift($rows); // Skip header: Codice,Descrizione,Provincia,DataFineVal

        $bar = $this->output->createProgressBar(count($rows));
        $imported = 0;

        foreach ($rows as $row) {
            if (count($row) < 2) {
                continue;
            }

            $codiceQuestura = trim($row[0]);
            $descrizione = trim($row[1]);
            $dataFine = isset($row[3]) && ! empty(trim($row[3])) ? trim($row[3]) : null;

            if (empty($codiceQuestura) || empty($descrizione)) {
                continue;
            }

            // Cross-reference with gi_nazioni.json
            $denomUpper = mb_strtoupper($descrizione);
            $match = $nazioniMap[$denomUpper] ?? null;

            $sigla = $match['sigla'] ?? null;
            $codiceIstat = $match['codice_istat'] ?? null;
            $cittadinanza = $match['cittadinanza'] ?? null;

            // Parse DataFineVal if exists
            $dataFineDate = null;
            if ($dataFine) {
                try {
                    $dataFineDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $dataFine)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore parse errors
                }
            }

            DB::table('stati')->updateOrInsert(
                ['codice_questura' => $codiceQuestura],
                [
                    'sigla' => $sigla,
                    'denominazione' => $descrizione,
                    'cittadinanza' => $cittadinanza,
                    'codice_istat' => $codiceIstat,
                    'data_fine_validita' => $dataFineDate,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ Stati imported: {$imported}");

        return 0;
    }
}
