<?php

namespace Tests\Feature;

use App\Models\CestinoItem;
use App\Models\Customers;
use App\Models\Schedina;
use App\Models\TipoAlloggiato;
use App\Models\TipoDocumento;
use App\Services\CestinoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CataloghiQuesturaReadOnlyTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([\Database\Seeders\TipoAlloggiatoSeeder::class, \Database\Seeders\TipoDocumentoSeeder::class]);
    }

    private function context(string $role): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $u = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $s->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id]);
    }

    public static function cases(): array
    {
        $out = [];
        foreach (['super_admin', 'admin', 'proprietario', 'struttura_user'] as $role) {
            foreach (['tipo_alloggiato', 'tipo_documento'] as $table) {
                $out[] = [$role, $table];
            }
        }

        return $out;
    }

    #[DataProvider('cases')]
    public function test_tutti_ruoli_leggono_e_non_scrivono(string $role, string $table): void
    {
        $this->context($role);
        $before = DB::table($table)->orderBy('id')->get()->toJson();
        $id = DB::table($table)->value('id');
        $this->get('/'.$table)->assertOk()->assertSee('Catalogo ufficiale Questura (sola lettura)');
        $this->get('/'.$table.'/create')->assertForbidden();
        $this->get('/'.$table.'/'.$id.'/edit')->assertForbidden();
        $this->post('/'.$table, ['codice' => 'SYNTH', 'descrizione' => 'Solo prova isolata', 'locked' => false, 'attivo' => 0, 'struttura_id' => 999])->assertForbidden();
        $this->put('/'.$table.'/'.$id, ['codice' => 'SYNTH', 'descrizione' => 'Solo prova isolata', 'locked' => false])->assertForbidden();
        $this->patch('/'.$table.'/'.$id, ['codice' => 'SYNTH', 'descrizione' => 'Solo prova isolata'])->assertForbidden();
        $this->delete('/'.$table.'/'.$id)->assertForbidden();
        $this->put('/'.$table.'/999999', ['codice' => 'SYNTH', 'descrizione' => 'Prova'])->assertNotFound();
        $this->post('/'.$table, ['_token' => 'token-falso', 'codice' => 'SYNTH', 'descrizione' => 'Prova'])->assertStatus(419);
        $this->assertSame($before, DB::table($table)->orderBy('id')->get()->toJson());
    }

    #[DataProvider('cases')]
    public function test_richieste_invalide_rifiutate_prima_della_validazione(string $role, string $table): void
    {
        $this->context($role);
        $this->postJson('/'.$table, ['codice' => ['forgiato'], 'descrizione' => null])->assertForbidden();
    }

    public function test_dettaglio_documento_non_produce_errore_server(): void
    {
        $this->context('super_admin');
        $this->get('/tipo_documento/'.TipoDocumento::value('id'))->assertForbidden();
    }

    public function test_ripristino_catalogo_vietato_e_snapshot_preservato(): void
    {
        $this->context('super_admin');
        $before = TipoDocumento::orderBy('id')->get()->toJson();
        $item = CestinoItem::create(['entity_class' => TipoDocumento::class, 'entity_type' => 'Tipo Documento', 'original_id' => 999991, 'title' => 'Prova sintetica', 'payload' => ['id' => 999991, 'codice' => 'SYNTH', 'descrizione' => 'Prova sintetica', 'locked' => false], 'deleted_at' => now()]);
        $this->post('/cestino/'.$item->id.'/ripristina')->assertForbidden();
        $this->assertDatabaseHas('cestino_items', ['id' => $item->id]);
        $this->assertSame($before, TipoDocumento::orderBy('id')->get()->toJson());
    }

    public function test_seed_idempotenti_cataloghi_ufficiali_id_clienti_relazioni_extra_preservati(): void
    {
        $s = $this->structureFor(null);
        $c = Customers::create(['struttura_id' => $s->id, 'name' => 'Persona', 'surname' => 'Sintetica', 'type_doc_reg' => 'IDENT']);
        $sheet = Schedina::create(['struttura_id' => $s->id, 'customer_id' => $c->id, 'relationship' => '16', 'or_doctype' => 'IDENT']);
        $before = [$c->fresh()->getRawOriginal(), $sheet->fresh()->getRawOriginal()];
        DB::table('tipo_documento')->insert(['codice' => 'EXTRA', 'descrizione' => 'Storico sintetico', 'locked' => false]);
        $snap = [TipoAlloggiato::orderBy('id')->get()->toJson(), TipoDocumento::orderBy('id')->get()->toJson()];
        $this->travel(2)->seconds();
        $this->seed([\Database\Seeders\TipoAlloggiatoSeeder::class, \Database\Seeders\TipoDocumentoSeeder::class]);
        $this->assertSame($snap, [TipoAlloggiato::orderBy('id')->get()->toJson(), TipoDocumento::orderBy('id')->get()->toJson()]);
        $this->assertSame($before, [$c->fresh()->getRawOriginal(), $sheet->fresh()->getRawOriginal()]);
        foreach (['tipo_alloggiato' => 'tipi.csv', 'tipo_documento' => 'documenti.csv'] as $table => $file) {
            $h = fopen(base_path('reference/questura/'.$file), 'r');
            fgetcsv($h);
            $official = [];
            while (($row = fgetcsv($h)) !== false) {
                $official[$row[0]] = $row[1];
            }fclose($h);
            $actual = DB::table($table)->whereIn('codice', array_keys($official))->pluck('descrizione', 'codice')->all();
            ksort($official);
            ksort($actual);
            $this->assertSame($official, $actual);
            $this->assertSame(count($official), DB::table($table)->whereIn('codice', array_keys($official))->count());
        }
    }

    public function test_cestino_service_vieta_entrambi_cataloghi_indipendentemente_dal_locked(): void
    {
        foreach ([TipoAlloggiato::first(), TipoDocumento::first()] as $model) {
            $model->locked = false;
            try {
                app(CestinoService::class)->archiveModel($model);
                $this->fail('Archiviazione consentita');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
            $item = CestinoItem::create(['entity_class' => get_class($model), 'entity_type' => 'Catalogo Questura', 'original_id' => $model->id, 'payload' => ['codice' => 'SYNTH', 'descrizione' => 'Prova'], 'deleted_at' => now()]);
            try {
                app(CestinoService::class)->restoreItem($item);
                $this->fail('Ripristino consentito');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
            $this->assertDatabaseHas('cestino_items', ['id' => $item->id]);
        }
    }

    public function test_ricerca_pagine_stato_vuoto_catalogo_comune_due_strutture(): void
    {
        $this->context('struttura_user');
        $before = [TipoDocumento::orderBy('id')->get()->toJson(), TipoAlloggiato::orderBy('id')->get()->toJson()];
        $this->get('/tipo_documento?q=PASOR')->assertOk()->assertViewHas('tipoDocumenti', fn ($p) => $p->total() === 1 && $p->first()->codice === 'PASOR');
        $this->get('/tipo_documento?page=10')->assertOk()->assertViewHas('tipoDocumenti', fn ($p) => $p->total() === 95 && $p->count() === 5 && $p->perPage() === 10);
        $this->get('/tipo_alloggiato?q=16')->assertOk()->assertViewHas('tipiAlloggiato', fn ($p) => $p->total() === 1 && $p->first()->codice === '16');
        foreach (['tipo_alloggiato', 'tipo_documento'] as $t) {
            $this->get('/'.$t.'?q=inesistente&page=2')->assertOk()->assertSee('Nessun risultato.');
        }
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        $this->get('/tipo_documento')->assertOk()->assertViewHas('tipoDocumenti', fn ($p) => $p->total() === 95);
        $this->get('/tipo_alloggiato')->assertOk()->assertViewHas('tipiAlloggiato', fn ($p) => $p->total() === 5);
        $this->assertSame($before, [TipoDocumento::orderBy('id')->get()->toJson(), TipoAlloggiato::orderBy('id')->get()->toJson()]);
    }
}
