<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportComuni extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comuni:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import comuni from Questura CSV with complete hierarchy (Italia -> Region -> Province -> Comune)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = app_path('Comuni.csv');

        if (! File::exists($csvPath)) {
            $this->error("File not found: {$csvPath}");

            return 1;
        }

        $this->info('Starting comuni import from Questura CSV...');

        // Get Italia stato_id
        $italiaStato = DB::table('stati')->where('sigla', 'IT')->first();
        if (! $italiaStato) {
            $this->error('Stato Italia (IT) not found in stati table. Run "php artisan stati:import" first.');

            return 1;
        }

        $this->info("✓ Found Italia stato (ID: {$italiaStato->id})");

        // Load provinces with their regions for lookup
        $provincesMap = $this->loadProvincesMap();
        $this->info('✓ Loaded '.count($provincesMap).' provinces with region mappings');

        // Read and process CSV
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header: Codice,Descrizione,Provincia,DataFineVal

        $imported = 0;
        $errors = 0;
        $bar = $this->output->createProgressBar();

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 3) {
                    continue;
                } // Skip malformed rows

                [$codice, $descrizione, $sigla_provincia, $dataFineVal] = array_pad($row, 4, null);

                // Clean data
                $codice = trim($codice);
                $descrizione = trim($descrizione);
                $sigla_provincia = trim($sigla_provincia);
                $dataFineVal = ! empty($dataFineVal) && $dataFineVal !== '' ? $this->parseDate($dataFineVal) : null;

                // Lookup province and region
                $provinceData = $provincesMap[$sigla_provincia] ?? null;

                if (! $provinceData) {
                    $this->newLine();
                    $this->warn("Province not found for sigla: {$sigla_provincia} (comune: {$descrizione})");
                    $errors++;

                    continue;
                }

                // Insert or update comune
                DB::table('comuni')->updateOrInsert(
                    ['codice_questura' => $codice],
                    [
                        'denominazione' => $descrizione,
                        'sigla_provincia' => $sigla_provincia,
                        'province_id' => $provinceData['province_id'],
                        'region_id' => $provinceData['region_id'],
                        'stato_id' => $italiaStato->id,
                        'data_fine_validita' => $dataFineVal,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );

                $imported++;
                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            $this->newLine(2);
            $this->info("✓ Comuni imported: {$imported}");
            if ($errors > 0) {
                $this->warn("⚠ Errors/skipped: {$errors}");
            }

            // Show hierarchy summary
            $this->showHierarchySummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        } finally {
            fclose($handle);
        }

        return 0;
    }

    /**
     * Load provinces with their region_id for quick lookup
     */
    private function loadProvincesMap(): array
    {
        $provinces = DB::table('provinces')
            ->select('id', 'sigla_provincia', 'region_id')
            ->get();

        $map = [];
        foreach ($provinces as $province) {
            $map[$province->sigla_provincia] = [
                'province_id' => $province->id,
                'region_id' => $province->region_id,
            ];
        }

        return $map;
    }

    /**
     * Parse Italian date format from CSV
     */
    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Format: DD/MM/YYYY HH:MM:SS or DD/MM/YYYY
        try {
            $parsed = \DateTime::createFromFormat('d/m/Y H:i:s', $date)
                   ?? \DateTime::createFromFormat('d/m/Y', $date);

            return $parsed ? $parsed->format('Y-m-d') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Show summary of the complete hierarchy
     */
    private function showHierarchySummary(): void
    {
        $this->newLine();
        $this->info('=== Hierarchy Summary ===');

        // Count by hierarchy level
        $stati = DB::table('comuni')->distinct('stato_id')->count();
        $regions = DB::table('comuni')->distinct('region_id')->count();
        $provinces = DB::table('comuni')->distinct('province_id')->count();
        $comuni = DB::table('comuni')->count();

        $this->table(
            ['Level', 'Count'],
            [
                ['Stati', $stati],
                ['Regions', $regions],
                ['Provinces', $provinces],
                ['Comuni', $comuni],
            ]
        );

        // Sample data
        $sample = DB::table('comuni as c')
            ->join('provinces as p', 'c.province_id', '=', 'p.id')
            ->join('regions as r', 'c.region_id', '=', 'r.id')
            ->join('stati as s', 'c.stato_id', '=', 's.id')
            ->select(
                's.denominazione as stato',
                'r.denominazione as regione',
                'p.denominazione as provincia',
                'c.denominazione as comune',
                'c.codice_questura'
            )
            ->limit(3)
            ->get();

        $this->newLine();
        $this->info('Sample data (complete hierarchy):');
        $this->table(
            ['Stato', 'Regione', 'Provincia', 'Comune', 'Codice'],
            $sample->map(fn ($r) => [
                $r->stato,
                $r->regione,
                $r->provincia,
                $r->comune,
                $r->codice_questura,
            ])->toArray()
        );
    }
}
