<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['rilasciato_da' => 'name', 'tipo_via' => 'abbr'] as $table => $field) {
            if (Schema::hasColumn($table, 'struttura_id')) {
                continue;
            }
            foreach (Schema::getIndexes($table) as $index) {
                if ($index['unique'] && ! $index['primary'] && in_array($index['columns'], [[$field], ['nome']], true)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropUnique($index['name']));
                }
            }
            Schema::table($table, function (Blueprint $t) use ($table, $field) {
                $t->foreignId('struttura_id')->nullable()->constrained('struttura')->restrictOnDelete();
                $t->unique(['struttura_id', $field], $table.'_struttura_unique');
            });
        }
        if (! Schema::hasColumn('struttura', 'indirizzi_inizializzati_at')) {
            Schema::table('struttura', fn (Blueprint $t) => $t->timestamp('indirizzi_inizializzati_at')->nullable());
        }
        $service = app(\App\Services\CataloghiIndirizziStrutturaService::class);
        foreach (DB::table('struttura')->orderBy('id')->pluck('id') as $sid) {
            $service->initialize((int) $sid);
        }
        $service->recoverLegacyReferences();
    }

    public function down(): void
    {
        throw new RuntimeException('Usare il checkpoint coordinato: nessuna perdita automatica dei cataloghi privati.');
    }
};
