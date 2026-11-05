<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class QaRoles extends Command
{
    protected $signature = 'qa:roles';

    protected $description = 'Verifica rapida ruoli, accessi minimi e catena admin -> proprietario -> struttura';

    public function handle(): int
    {
        $this->info('=== QA ROLES ===');

        $warnings = [];
        $fails = [];

        if (!Schema::hasTable('users')) {
            $this->error('Tabella users mancante.');
            return 2;
        }

        $roles = DB::table('users')
            ->select('ruolo', DB::raw('count(*) as total'))
            ->where('attivo', true)
            ->groupBy('ruolo')
            ->pluck('total', 'ruolo');

        $this->line('- Ruoli attivi');
        foreach (['super_admin', 'admin', 'proprietario', 'struttura_user'] as $role) {
            $count = (int) ($roles[$role] ?? 0);
            $this->line("  $role: $count");
        }

        if ((int) ($roles['super_admin'] ?? 0) < 1) {
            $fails[] = 'Manca almeno un super_admin attivo.';
        }
        if ((int) ($roles['admin'] ?? 0) < 1) {
            $fails[] = 'Manca almeno un admin attivo.';
        }
        if ((int) ($roles['proprietario'] ?? 0) < 1) {
            $fails[] = 'Manca almeno un proprietario attivo.';
        }
        if ((int) ($roles['struttura_user'] ?? 0) < 1) {
            $fails[] = 'Manca almeno uno struttura_user attivo.';
        }

        $this->line('- Rotte ruolo');
        $expectedRoutes = [
            'superadmin.amministratori.index',
            'superadmin.proprietari.index',
            'superadmin.strutture.index',
            'admin.proprietari.index',
            'admin.strutture.index',
            'proprietario.strutture.index',
            'strutture.seleziona.index',
        ];

        foreach ($expectedRoutes as $route) {
            $exists = Route::has($route);
            $this->line('  ' . $route . ': ' . ($exists ? 'OK' : 'WARN'));
            if (!$exists) {
                $warnings[] = 'Route mancante: ' . $route;
            }
        }

        if (Schema::hasTable('proprietari')) {
            $this->line('- Catena amministrativa');

            $owners = DB::table('proprietari')->get(['id', 'nome', 'admin_id', 'attivo']);
            foreach ($owners as $owner) {
                $structures = Schema::hasTable('struttura')
                    ? DB::table('struttura')->where('proprietario_id', $owner->id)->count()
                    : 0;

                $this->line(sprintf(
                    '  Proprietario %s (id=%d): admin_id=%s, attivo=%s, strutture=%d',
                    $owner->nome,
                    $owner->id,
                    $owner->admin_id ?? 'NULL',
                    $owner->attivo ? '1' : '0',
                    $structures
                ));

                if ($owner->attivo && empty($owner->admin_id)) {
                    $warnings[] = 'Proprietario attivo senza admin_id: ' . $owner->nome;
                }
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

        $this->info('QA roles OK');
        return 0;
    }
}
