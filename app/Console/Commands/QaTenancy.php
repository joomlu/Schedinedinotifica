<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QaTenancy extends Command
{
    protected $signature = 'qa:tenancy';

    protected $description = 'Verifica rapida integrita struttura_id e distribuzione dati per struttura';

    public function handle(): int
    {
        $this->info('=== QA TENANCY ===');

        $warnings = [];
        $fails = [];

        if (!Schema::hasTable('struttura')) {
            $this->error('Tabella struttura mancante.');
            return 2;
        }

        $validIds = DB::table('struttura')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->line('- Strutture presenti: ' . implode(', ', $validIds));

        $tables = [
            'schedina',
            'clienti',
            'componenti',
            'schedina_camere',
            'web_checkin_richieste',
            'questura_exports',
            'questura_transmissions',
            'istat_exports',
            'istat_transmissions',
            'tassa_di_soggiorno',
            'tassa_esenzioni',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'struttura_id')) {
                continue;
            }

            $total = DB::table($table)->count();
            $nullCount = DB::table($table)->whereNull('struttura_id')->count();
            $orphans = DB::table($table)->whereNotNull('struttura_id')->whereNotIn('struttura_id', $validIds ?: [-1])->count();

            $this->line("  [$table] tot=$total null=$nullCount orfani=$orphans");

            $rows = DB::table($table)
                ->select('struttura_id', DB::raw('count(*) as total'))
                ->groupBy('struttura_id')
                ->orderBy('struttura_id')
                ->get();

            foreach ($rows as $row) {
                $this->line('    - struttura_id ' . ($row->struttura_id ?? 'NULL') . ': ' . $row->total);
            }

            if ($orphans > 0) {
                $fails[] = "Tabella $table ha record con struttura_id orfano.";
            }
        }

        if ($fails) {
            $this->error('FAILS:');
            foreach ($fails as $fail) {
                $this->error(' - ' . $fail);
            }
        }

        if ($warnings) {
            $this->warn('WARNINGS:');
            foreach ($warnings as $warning) {
                $this->warn(' - ' . $warning);
            }
        }

        if ($fails) {
            return 2;
        }

        if ($warnings) {
            return 1;
        }

        $this->info('QA tenancy OK');
        return 0;
    }
}
