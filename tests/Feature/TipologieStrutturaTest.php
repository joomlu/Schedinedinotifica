<?php

namespace Tests\Feature;

use App\Models\{Classificazione, TipologiaGenerale, TipologiaStruttura};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TipologieStrutturaTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_catalogo_popolato_dopo_le_migrazioni_conserva_i_soli_cav(): void
    {
        // Riproduce la popolazione CAV/Nessuna successiva alle migrazioni.
        // Il launcher esclude i seeder generali: nessun loro caricamento o bypass.
        $extra = TipologiaGenerale::where('nome', 'Extra-alberghiera')->sole();
        $cav = TipologiaStruttura::firstOrCreate(['nome' => 'Case e appartamenti per vacanze (CAV)', 'tipologia_generale_id' => $extra->id]);
        $cav->classificazioni()->syncWithoutDetaching([Classificazione::where('nome', 'Nessuna')->sole()->id]);
        $this->assertEqualsCanonicalizing(['Nessuna', '2 soli', '3 soli', '4 soli'], $cav->classificazioni()->pluck('nome')->all());
    }

    public function test_tipologie_extra_salvate_e_rilette_senza_mutare_le_altre_strutture(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $legacy = $this->structureFor($owner);
        $before = $legacy->getRawOriginal();
        $extra = TipologiaGenerale::where('nome', 'Extra-alberghiera')->sole();
        foreach (['Agriturismo', 'Affittacamere', 'Locazione turistica imprenditoriale', 'Locazione turistica non imprenditoriale', 'Ostello'] as $nome) {
            $tipo = TipologiaStruttura::firstOrCreate(['nome' => $nome, 'tipologia_generale_id' => $extra->id]);
            $classe = Classificazione::firstOrCreate(['nome' => $nome === 'Agriturismo' ? '3 girasoli' : 'Nessuna']);
            $tipo->classificazioni()->syncWithoutDetaching([$classe->id]);
            $this->actingAs($admin)->put('/struttura?sid='.$s->id, ['tipologia_generale_id' => $extra->id, 'tipologia_struttura_id' => $tipo->id, 'classificazione_id' => $classe->id])->assertSessionHasNoErrors()->assertRedirect();
            $saved = $s->fresh();
            $this->assertSame($nome, $saved->tipologia_struttura);
            $this->assertSame($tipo->id, $saved->tipologia_struttura_id);
            $this->assertSame($classe->nome, $saved->classificazione);
            $this->assertNull($saved->questura_profilo);
            $this->assertNull($saved->questura_appartamento_id);
        }
        $this->assertSame($before, $legacy->fresh()->getRawOriginal());
    }

    public function test_migrazione_additiva_idempotente_conserva_tutte_le_righe_precedenti(): void
    {
        $s = $this->structureFor(null);
        $before = $s->getRawOriginal();
        $extra = TipologiaGenerale::where('nome', 'Extra-alberghiera')->sole();
        $cav = TipologiaStruttura::firstOrCreate(['nome' => 'Case e appartamenti per vacanze (CAV)', 'tipologia_generale_id' => $extra->id]);
        $legacy = Classificazione::create(['nome' => 'Spighe storiche sintetiche']);
        $cav->classificazioni()->attach($legacy->id);
        $tables = ['tipologie_generali', 'tipologie_struttura', 'classificazioni', 'classificazione_tipologia'];
        $snapshot = [];
        foreach ($tables as $table) { $snapshot[$table] = DB::table($table)->get()->keyBy('id'); }
        $migration = require database_path('migrations/2026_10_27_120100_complete_tipologie_turistiche.php');
        $migration->up();
        $counts = [];
        foreach ($tables as $table) {
            foreach ($snapshot[$table] as $id => $row) { $this->assertEquals($row, DB::table($table)->find($id)); }
            $counts[$table] = DB::table($table)->count();
        }
        $migration->up();
        foreach ($tables as $table) { $this->assertSame($counts[$table], DB::table($table)->count()); }
        $this->assertSame($before, $s->fresh()->getRawOriginal());
        $this->assertEqualsCanonicalizing(['2 soli', '3 soli', '4 soli', $legacy->nome], $cav->classificazioni()->pluck('nome')->all());
    }
}
