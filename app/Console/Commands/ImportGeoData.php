<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportGeoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:import {--fresh : Truncate tables before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import geographic data from JSON files into database tables (regions, provinces, comuni, CAP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('Truncating geo tables...');
            if (Schema::hasTable('comune_cap')) {
                DB::table('comune_cap')->truncate();
            }
            DB::table('comuni')->truncate();
            DB::table('province')->truncate();
            DB::table('regioni')->truncate();
            $this->info('Tables truncated.');
        }

        // Create comune_cap if it doesn't exist
        $this->ensureComuneCapTable();

        $this->info('Starting geo data import...');

        $this->importRegions();
        $this->importProvinces();
        $this->importComuni();
        $this->importCap();

        // Backfill comuni.province_id & comuni.sigla_provincia if columns exist
        $this->backfillComuniProvince();

        $this->info('✓ Geo data import completed successfully!');

        return 0;
    }

    private function backfillComuniProvince()
    {
        if (! Schema::hasColumn('comuni', 'province_id') || ! Schema::hasColumn('comuni', 'sigla_provincia')) {
            $this->info('Skipping comuni backfill: comuni.province_id or comuni.sigla_provincia column missing.');

            return;
        }

        $path = storage_path('app/gi_comuni.json');
        if (! file_exists($path)) {
            $this->warn('gi_comuni.json not found, skipping comuni backfill...');

            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data) {
            $this->error('Failed to parse gi_comuni.json (backfill)');

            return;
        }

        $this->info('Backfilling comuni province links...');
        $bar = $this->output->createProgressBar(count($data));

        $provinceMap = DB::table('province')->pluck('id', 'sigla_provincia')->toArray();

        // Build existing comuni map to reduce updates
        $existing = DB::table('comuni')->select(['id', 'code', 'province_id', 'sigla_provincia'])->get()->keyBy('code');

        $updates = 0;
        foreach ($data as $item) {
            $code = $item['codice_istat'] ?? null;
            $provSigla = $item['sigla_provincia'] ?? null;
            if (! $code || ! $provSigla) {
                $bar->advance();

                continue;
            }

            $row = $existing->get($code);
            $provId = $provinceMap[$provSigla] ?? null;
            if (! $row || ! $provId) {
                $bar->advance();

                continue;
            }

            // Only update if changed or null
            if (($row->sigla_provincia !== $provSigla) || ($row->province_id != $provId)) {
                DB::table('comuni')->where('id', $row->id)->update([
                    'sigla_provincia' => $provSigla,
                    'province_id' => $provId,
                    'updated_at' => now(),
                ]);
                $updates++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Comuni backfilled: '.$updates);
    }

    private function ensureComuneCapTable()
    {
        if (! Schema::hasTable('comune_cap')) {
            $this->info('Creating comune_cap table...');
            Schema::create('comune_cap', function ($table) {
                $table->id();
                $table->unsignedInteger('comune_id'); // Changed from foreignId to match comuni.id type (int)
                $table->string('cap', 10);
                $table->string('sigla_provincia', 10)->nullable();
                $table->string('code', 50)->nullable();
                $table->timestamps();
                $table->unique(['comune_id', 'cap']);
                $table->index('cap');
                $table->index('sigla_provincia');

                $table->foreign('comune_id')->references('id')->on('comuni')->onDelete('cascade');
            });
            $this->info('✓ comune_cap table created.');
        }
    }

    private function importRegions()
    {
        $path = storage_path('app/gi_regioni.json');
        if (! file_exists($path)) {
            $this->warn('gi_regioni.json not found, skipping regions...');

            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data) {
            $this->error('Failed to parse gi_regioni.json');

            return;
        }

        $this->info('Importing regions...');
        $bar = $this->output->createProgressBar(count($data));

        // Get Italy ID from stati table
        $italyId = DB::table('stati')->where('sigla', 'IT')->value('id');

        foreach ($data as $item) {
            DB::table('regions')->updateOrInsert(
                ['codice_regione' => $item['codice_regione']],
                [
                    'denominazione' => $item['denominazione_regione'] ?? $item['codice_regione'],
                    'stato_id' => $italyId,  // Changed from country_id
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Regions imported: '.count($data));
    }

    private function importProvinces()
    {
        $path = storage_path('app/gi_province.json');
        if (! file_exists($path)) {
            $this->warn('gi_province.json not found, skipping provinces...');

            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data) {
            $this->error('Failed to parse gi_province.json');

            return;
        }

        $this->info('Importing provinces...');
        $bar = $this->output->createProgressBar(count($data));

        // Build a map of region codes to IDs
        $regionMap = DB::table('regions')->pluck('id', 'codice_regione')->toArray();

        foreach ($data as $item) {
            $regionCode = $item['codice_regione'] ?? null;
            $regionId = $regionCode ? ($regionMap[$regionCode] ?? null) : null;

            if (! $regionId) {
                $this->warn("\nProvince {$item['sigla_provincia']} has no valid region, skipping...");

                continue;
            }

            DB::table('province')->updateOrInsert(
                ['sigla_provincia' => $item['sigla_provincia']],
                [
                    'denominazione' => $item['denominazione_provincia'] ?? $item['sigla_provincia'],
                    'codice_regione' => $regionCode,
                    'region_id' => $regionId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Provinces imported: '.count($data));
    }

    private function importComuni()
    {
        $path = storage_path('app/gi_comuni.json');
        if (! file_exists($path)) {
            $this->warn('gi_comuni.json not found, skipping comuni...');

            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data) {
            $this->error('Failed to parse gi_comuni.json');

            return;
        }

        $this->info('Importing comuni...');
        $bar = $this->output->createProgressBar(count($data));

        foreach ($data as $item) {
            // Use 'code' and 'name' columns as per actual schema
            DB::table('comuni')->updateOrInsert(
                ['code' => $item['codice_istat']],
                [
                    'name' => $item['denominazione_ita'] ?? $item['denominazione_ufficiale'] ?? $item['codice_istat'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Comuni imported: '.count($data));
    }

    private function importCap()
    {
        $path = storage_path('app/gi_comuni_cap.json');
        if (! file_exists($path)) {
            $this->warn('gi_comuni_cap.json not found, skipping CAP...');

            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data) {
            $this->error('Failed to parse gi_comuni_cap.json');

            return;
        }

        $this->info('Importing CAP...');
        $bar = $this->output->createProgressBar(count($data));

        // Build a map of codice_istat (now stored in 'code') to comune IDs
        $comuneMap = DB::table('comuni')->pluck('id', 'code')->toArray();

        foreach ($data as $item) {
            $comuneCode = $item['codice_comune'] ?? $item['codice_istat'] ?? null;
            $comuneId = $comuneCode ? ($comuneMap[$comuneCode] ?? null) : null;

            if (! $comuneId) {
                // Skip if comune not found
                continue;
            }

            $cap = $item['cap'] ?? null;
            if (! $cap) {
                continue;
            }

            DB::table('comune_cap')->updateOrInsert(
                ['comune_id' => $comuneId, 'cap' => $cap],
                [
                    'sigla_provincia' => $item['sigla_provincia'] ?? null,
                    'code' => $comuneCode, // Changed from codice_istat to 'code'
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ CAP imported: '.count($data));
    }
}
