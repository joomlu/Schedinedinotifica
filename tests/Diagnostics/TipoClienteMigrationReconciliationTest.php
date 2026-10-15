<?php

namespace Tests\Diagnostics;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TipoClienteMigrationReconciliationTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    public function test_migrazioni_su_base_normalizzata_preservano_dati_vincoli_e_cliente_vuoto(): void
    {
        $structure = $this->structureFor(null);
        $customer = \App\Models\Customers::create([
            'struttura_id' => $structure->id, 'name' => 'Persona',
            'surname' => 'Sintetica', 'type_housed' => '',
        ]);
        DB::table('tipo_cliente')->insert(['codice' => 'EXTRA', 'descrizione' => 'Personalizzata', 'attivo' => false]);
        $tables = ['tipo_cliente', 'clienti', 'classificazioni', 'classificazione_tipologia', 'struttura'];
        $snapshot = fn () => collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->orderBy('id')->get()->toJson()])->all();
        $schema = fn () => collect($tables)->mapWithKeys(fn ($t) => [$t => DB::select('SHOW CREATE TABLE `'.$t.'`')])->all();
        $before = $snapshot(); $constraints = $schema();
        $prepare = require database_path('migrations/2026_02_28_000000_prepare_classificazioni_index_for_normalize.php');
        $initialize = require database_path('migrations/2026_10_14_030000_initialize_tipo_cliente_sistema.php');
        $prepare->up(); $initialize->up();
        $this->assertSame($before, $snapshot());
        $this->assertEquals($constraints, $schema());
        $prepare->up(); $initialize->up();
        $this->assertSame($before, $snapshot());
        $this->assertSame('', $customer->fresh()->type_housed);
    }

    public function test_migrazione_inizializza_solo_voci_mancanti_e_conserva_extra(): void
    {
        $stable = DB::table('tipo_cliente')->where('codice', 'OSPITE')->first();
        DB::table('tipo_cliente')->where('codice', 'RICHIESTA')->delete();
        DB::table('tipo_cliente')->insert(['codice' => 'LEGACY', 'descrizione' => 'Personalizzata', 'attivo' => false]);
        $extra = DB::table('tipo_cliente')->where('codice', 'LEGACY')->first();
        $migration = require database_path('migrations/2026_10_14_030000_initialize_tipo_cliente_sistema.php');
        $migration->up();
        $this->assertEquals($stable, DB::table('tipo_cliente')->where('codice', 'OSPITE')->first());
        $this->assertEquals($extra, DB::table('tipo_cliente')->where('codice', 'LEGACY')->first());
        $this->assertDatabaseHas('tipo_cliente', ['codice' => 'RICHIESTA', 'descrizione' => 'Richiesta', 'attivo' => true]);
        $before = DB::table('tipo_cliente')->orderBy('id')->get()->toJson();
        $migration->up();
        $this->assertSame($before, DB::table('tipo_cliente')->orderBy('id')->get()->toJson());
    }
}
