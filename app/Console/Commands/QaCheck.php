<?php

namespace App\Console\Commands;

use App\Http\Kernel as HttpKernel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class QaCheck extends Command
{
    protected $signature = 'qa:check';

    protected $description = 'Verifica rapida QA (tenancy, middleware, rotte)';

    public function handle(): int
    {
        $this->info('=== QA CHECK ===');
        $this->line('Env: ' . app()->environment());
        $this->line('APP_URL: ' . config('app.url'));

        $warnings = [];
        $fails = [];

        $this->checkMiddleware($warnings, $fails);
        $this->checkRoutes($warnings, $fails);
        $this->checkTables($warnings, $fails);
        $this->checkDemoIntegrity($warnings, $fails);

        if ($fails) {
            $this->error('FAILS:');
            foreach ($fails as $fail) {
                $this->error(' - ' . $fail);
            }
        }

        if ($warnings) {
            $this->warn('WARNINGS:');
            foreach ($warnings as $warn) {
                $this->warn(' - ' . $warn);
            }
        }

        if ($fails) {
            return 2;
        }

        if ($warnings) {
            return 1;
        }

        $this->info('QA check OK');
        return 0;
    }

    protected function checkRoutes(array &$warnings, array &$fails): void
    {
        $this->info('- Rotte principali');
        $routes = [
            'qa.demo-map',
            'strutture.seleziona.index',
            'admin.proprietari.index',
            'proprietario.strutture.index',
            'schedina',
        ];

        foreach ($routes as $name) {
            $exists = Route::has($name);
            $status = $exists ? 'OK' : 'WARN';
            $this->line(sprintf('  [%s] %s', $status, $name));
            if (!$exists) {
                $warnings[] = 'Route mancante: ' . $name;
            }
        }
    }

    protected function checkMiddleware(array &$warnings, array &$fails): void
    {
        $this->info('- Middleware gruppo web');
        /** @var HttpKernel $kernel */
        $kernel = app(HttpKernel::class);
        $web = $kernel->getMiddlewareGroups()['web'] ?? [];
        $checks = [
            'ImpostaStrutturaCorrente' => \App\Http\Middleware\ImpostaStrutturaCorrente::class,
            'VerificaServizioStruttura' => \App\Http\Middleware\VerificaServizioStruttura::class,
        ];

        foreach ($checks as $label => $class) {
            $ok = $this->containsMiddleware($web, $class);
            $this->line('  ' . $label . ': ' . ($ok ? 'OK' : 'WARN'));
            if (!$ok) {
                $warnings[] = 'Middleware mancante nel gruppo web: ' . $label;
            }
        }
    }

    protected function checkTables(array &$warnings, array &$fails): void
    {
        $this->info('- Conteggi per tabella');
        $tables = ['struttura', 'proprietari', 'users', 'schedina', 'clienti', 'componenti', 'schedina_camere'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->line("  [$table] WARN tabella non presente");
                $warnings[] = 'Tabella mancante: ' . $table;
                continue;
            }

            $hasStruttura = Schema::hasColumn($table, 'struttura_id');
            $total = DB::table($table)->count();
            $nullCount = $hasStruttura ? DB::table($table)->whereNull('struttura_id')->count() : null;
            $this->line("  [$table] tot=$total" . ($hasStruttura ? " null_struttura=$nullCount" : ' (struttura_id assente)'));

            if ($hasStruttura) {
                $byStruttura = DB::table($table)
                    ->select('struttura_id', DB::raw('count(*) as total'))
                    ->groupBy('struttura_id')
                    ->orderBy('struttura_id')
                    ->get();
                foreach ($byStruttura as $row) {
                    $this->line('    - struttura_id ' . ($row->struttura_id ?? 'NULL') . ': ' . $row->total);
                }
            }
        }
    }

    protected function containsMiddleware(array $stack, string $needle): bool
    {
        foreach ($stack as $item) {
            if ($item === $needle || (is_array($item) && in_array($needle, $item, true))) {
                return true;
            }
        }
        return false;
    }

    protected function checkDemoIntegrity(array &$warnings, array &$fails): void
    {
        $this->info('- Integrità base');

        if (Schema::hasTable('struttura')) {
            $structures = DB::table('struttura')->count();
            $this->line('  Strutture: ' . $structures);
            if ($structures < 1) {
                $fails[] = 'Nessuna struttura presente.';
            }

            if (Schema::hasColumn('struttura', 'proprietario_id')) {
                $ownersPerStructure = DB::table('struttura')->whereNull('proprietario_id')->count();
                if ($ownersPerStructure > 0) {
                    $warnings[] = 'Strutture senza proprietario_id: ' . $ownersPerStructure;
                }
            }
        }

        if (Schema::hasTable('proprietari')) {
            $owners = DB::table('proprietari')->count();
            $this->line('  Proprietari: ' . $owners);
            if ($owners < 1) {
                $fails[] = 'Nessun proprietario presente.';
            }

            if (Schema::hasTable('struttura') && Schema::hasColumn('struttura', 'proprietario_id')) {
                $counts = DB::table('struttura')->select('proprietario_id', DB::raw('count(*) as total'))->groupBy('proprietario_id')->get();
                foreach ($counts as $row) {
                    if ($row->proprietario_id === null) {
                        continue;
                    }

                    if ($row->total < 1) {
                        $warnings[] = 'Proprietario_id ' . $row->proprietario_id . ' non ha strutture associate.';
                    }
                }
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'ruolo')) {
            $roles = DB::table('users')
                ->select('ruolo', DB::raw('count(*) as total'))
                ->groupBy('ruolo')
                ->pluck('total', 'ruolo');

            $expect = [
                'super_admin' => 1,
                'admin' => 1,
                'proprietario' => 1,
                'struttura_user' => 1,
            ];

            foreach ($expect as $role => $expectedCount) {
                $found = (int) ($roles[$role] ?? 0);
                $this->line('  Ruolo ' . $role . ': ' . $found);
                if ($found < $expectedCount) {
                    $fails[] = "Attesi almeno $expectedCount utenti ruolo $role, trovati $found";
                }
            }
        }
    }
}
