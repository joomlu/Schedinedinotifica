<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $generale = $this->id('tipologie_generali', ['nome' => 'Extra-alberghiera']);
            $tipo = $this->id('tipologie_struttura', ['tipologia_generale_id' => $generale, 'nome' => 'Locazione turistica non imprenditoriale']);
            $nessuna = $this->id('classificazioni', ['nome' => 'Nessuna']);
            $this->id('classificazione_tipologia', ['tipologia_struttura_id' => $tipo, 'classificazione_id' => $nessuna]);
            // Opzioni ER additive; i valori e le relazioni legacy restano invariati.
            foreach (['Case e appartamenti per vacanze (CAV)' => [2, 3, 4], 'Locazione turistica non imprenditoriale' => [2, 3]] as $nome => $soli) {
                // Le migrazioni precedono i seeder anche nelle installazioni nuove.
                $tipoId = $this->id('tipologie_struttura', ['nome' => $nome, 'tipologia_generale_id' => $generale]);
                foreach ($soli as $numero) {
                    $classe = $this->id('classificazioni', ['nome' => $numero.' soli']);
                    $this->id('classificazione_tipologia', ['tipologia_struttura_id' => $tipoId, 'classificazione_id' => $classe]);
                }
            }
        });
    }

    private function id(string $table, array $key): int
    {
        $ids = DB::table($table)->where($key)->pluck('id');
        if ($ids->count() > 1) { throw new RuntimeException('Catalogo ambiguo: nessuna deduplicazione automatica.'); }

        return $ids->first() ?? DB::table($table)->insertGetId($key + ['created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Nessuna cancellazione: le nuove opzioni potrebbero già essere referenziate.
    }
};
