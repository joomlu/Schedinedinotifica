<?php

use App\Services\TitoliStrutturaService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('titolo', 'struttura_id')) {
            foreach (Schema::getIndexes('titolo') as $index) {
                if ($index['unique'] && $index['columns'] === ['nome']) {
                    Schema::table('titolo', fn (Blueprint $t) => $t->dropUnique($index['name']));
                }
            }
            Schema::table('titolo', function (Blueprint $t) {
                $t->foreignId('struttura_id')->nullable()->constrained('struttura')->restrictOnDelete();
                $t->string('base_nome', 50)->nullable();
                $t->unique(['struttura_id', 'nome'], 'titolo_nome_struttura_unique');
                $t->unique(['struttura_id', 'base_nome'], 'titolo_base_struttura_unique');
            });
        }
        if (! Schema::hasColumn('struttura', 'titoli_inizializzati_at')) {
            Schema::table('struttura', fn (Blueprint $t) => $t->timestamp('titoli_inizializzati_at')->nullable());
        }
        $service = app(TitoliStrutturaService::class);
        foreach (DB::table('struttura')->orderBy('id')->pluck('id') as $sid) {
            $service->initialize((int) $sid);
        }
        // Nessun originale viene attribuito o riscritto. Copie aggiuntive soltanto
        // quando un riferimento storico della struttura identifica una fonte univoca.
        $service->recoverLegacyReferences();
    }

    public function down(): void
    {
        throw new RuntimeException('Usare il checkpoint coordinato: nessuna perdita automatica dei titoli privati.');
    }
};
