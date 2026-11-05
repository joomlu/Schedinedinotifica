<?php

use App\Services\GruppiStrutturaService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('gruppi', 'struttura_id')) {
            Schema::table('gruppi', function (Blueprint $t) {
                $t->foreignId('struttura_id')->nullable()->constrained('struttura')->restrictOnDelete();
                $t->index(['struttura_id', 'livello', 'nome']);
                $t->unique(['struttura_id', 'id'], 'gruppi_struttura_identita');
                $t->foreign(['struttura_id', 'parent_id'], 'gruppi_padre_struttura_fk')
                    ->references(['struttura_id', 'id'])->on('gruppi')->restrictOnDelete();
            });
        }
        if (! Schema::hasColumn('struttura', 'gruppi_inizializzati_at')) {
            Schema::table('struttura', fn (Blueprint $t) => $t->timestamp('gruppi_inizializzati_at')->nullable());
        }
        // Originali globali intatti. Solo catene ricostruibili in modo univoco
        // dalle classificazioni effettivamente presenti vengono ricopiate.
        $legacy = DB::table('gruppi')->whereNull('struttura_id')->get()->keyBy('id');
        $service = app(GruppiStrutturaService::class);
        foreach (DB::table('struttura')->orderBy('id')->pluck('id') as $sid) {
            $service->initialize((int) $sid);
            $paths = DB::table('clienti')->where('struttura_id', $sid)->select('group', 'subgroup', 'subgroup1')->get()
                ->merge(DB::table('schedina')->where('struttura_id', $sid)->selectRaw('customer_group AS `group`, customer_subgroup AS subgroup, customer_subgroup1 AS subgroup1')->get());
            foreach ($paths as $path) {
                $parentSource = null;
                $parentTarget = null;
                foreach ([1 => 'group', 2 => 'subgroup', 3 => 'subgroup1'] as $level => $field) {
                    $name = $path->$field;
                    if (! $name) {
                        break;
                    }
                    $matches = $legacy->filter(fn ($g) => $g->nome === $name && (int) $g->livello === $level && $g->parent_id == $parentSource);
                    if ($matches->count() !== 1) {
                        break;
                    } // Nessuna scelta arbitraria.
                    $source = $matches->first();
                    $target = DB::table('gruppi')->where('struttura_id', $sid)->where('livello', $level)->where('nome', $name)->where('parent_id', $parentTarget)->first();
                    $parentTarget = $target?->id ?? DB::table('gruppi')->insertGetId([
                        'struttura_id' => $sid, 'nome' => $source->nome, 'descrizione' => $source->descrizione,
                        'tipo' => $source->tipo, 'livello' => $level, 'parent_id' => $parentTarget,
                        'created_at' => $source->created_at, 'updated_at' => $source->updated_at,
                    ]);
                    $parentSource = $source->id;
                }
            }
        }
    }

    public function down(): void
    {
        // Il recupero richiede il checkpoint coordinato; nessuna perdita silenziosa delle personalizzazioni.
        throw new RuntimeException('Migrazione Gruppi non reversibile automaticamente: usare il checkpoint coordinato.');
    }
};
