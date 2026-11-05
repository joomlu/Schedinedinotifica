<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupComponentiOrfaniCommand extends Command
{
    protected $signature = 'schedina:cleanup-componenti {--apply : Applica la pulizia eliminando i record orfani}';

    protected $description = 'Rileva e pulisce record orfani o incoerenti nella tabella componenti rispetto a schedina/clienti';

    public function handle(): int
    {
        if (!Schema::hasTable('componenti')) {
            $this->warn('Tabella componenti non presente: nessuna pulizia da eseguire.');
            return self::SUCCESS;
        }

        $orphansNoSchedina = DB::table('componenti as c')
            ->leftJoin('schedina as s', 's.id', '=', 'c.schedina_id')
            ->whereNull('s.id')
            ->select('c.id')
            ->pluck('c.id')
            ->all();

        $orphansSchedinaMismatch = DB::table('componenti as c')
            ->join('schedina as s', 's.id', '=', 'c.schedina_id')
            ->whereColumn('c.struttura_id', '!=', 's.struttura_id')
            ->select('c.id')
            ->pluck('c.id')
            ->all();

        $orphansCustomerMissing = DB::table('componenti as c')
            ->leftJoin('clienti as cl', 'cl.id', '=', 'c.customer_id')
            ->whereNotNull('c.customer_id')
            ->whereNull('cl.id')
            ->select('c.id')
            ->pluck('c.id')
            ->all();

        $orphansCustomerMismatch = DB::table('componenti as c')
            ->join('clienti as cl', 'cl.id', '=', 'c.customer_id')
            ->whereColumn('c.struttura_id', '!=', 'cl.struttura_id')
            ->select('c.id')
            ->pluck('c.id')
            ->all();

        $ids = collect(array_merge(
            $orphansNoSchedina,
            $orphansSchedinaMismatch,
            $orphansCustomerMissing,
            $orphansCustomerMismatch
        ))->unique()->values();

        $this->info('Analisi componenti completata');
        $this->line(' - Orfani senza schedina: ' . count($orphansNoSchedina));
        $this->line(' - Incoerenti struttura vs schedina: ' . count($orphansSchedinaMismatch));
        $this->line(' - Customer mancante: ' . count($orphansCustomerMissing));
        $this->line(' - Incoerenti struttura vs customer: ' . count($orphansCustomerMismatch));
        $this->line(' - Totale ID da pulire: ' . $ids->count());

        if ($ids->isEmpty()) {
            $this->info('Nessun record orfano trovato.');
            return self::SUCCESS;
        }

        if (!$this->option('apply')) {
            $this->warn('Dry-run: nessuna eliminazione eseguita. Usa --apply per applicare.');
            return self::SUCCESS;
        }

        $deleted = DB::table('componenti')->whereIn('id', $ids->all())->delete();
        $this->info('Pulizia completata. Record eliminati: ' . $deleted);

        return self::SUCCESS;
    }
}
