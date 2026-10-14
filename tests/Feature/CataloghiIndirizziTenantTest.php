<?php

namespace Tests\Feature;

use App\Models\CestinoItem;
use App\Models\RilasciatoDa;
use App\Models\TipoVia;
use App\Services\CataloghiIndirizziStrutturaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CataloghiIndirizziTenantTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private function context(string $role = 'struttura_user'): array
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $u = match ($role) {
            'admin' => $admin, 'proprietario' => $this->actor($role, $owner->id), default => $this->actor($role, null, $a->id)
        };
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $a->id]);

        return [$a, $b, $u];
    }

    public static function roles(): array
    {
        return [['struttura_user'], ['proprietario'], ['admin'], ['super_admin']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_crud_id_estranei_duplicati_cestino_e_csrf(string $role): void
    {
        [$a, $b] = $this->context($role);
        foreach ([['rilasciato_da', 'name', '/rilasciato', '/rilasciato/', '/rilasciato/', RilasciatoDa::class], ['tipo_via', 'abbr', '/tipovia_store', '/tipovia_update/', '/tipovia/', TipoVia::class]] as [$table, $key, $store, $update, $delete, $class]) {
            $foreign = DB::table($table)->where('struttura_id', $b->id)->first();
            $this->put($update.$foreign->id, [$key => 'Attacco'])->assertNotFound();
            $this->delete($delete.$foreign->id)->assertNotFound();
            $this->post($store, [$key => 'Privata A', 'attivo' => 1, 'struttura_id' => $b->id])->assertSessionHasNoErrors();
            $id = DB::table($table)->where('struttura_id', $a->id)->where($key, 'Privata A')->value('id');
            $this->assertNotNull($id);
            $this->post($store, [$key => ' privata a '])->assertSessionHasErrors($key);
            $this->put($update.$id, [$key => 'Modificata A', 'attivo' => 0])->assertSessionHasNoErrors();
            $this->assertDatabaseHas($table, ['id' => $id, $key => 'Modificata A']);
            if ($key === 'abbr') {
                $this->assertDatabaseHas($table, ['id' => $id, 'attivo' => 0]);
            }
            $this->put($update.$id, [$key => 'CSRF', '_token' => 'errato'])->assertStatus(419);
            $this->delete($delete.$id)->assertSessionHasNoErrors();
            $item = CestinoItem::where('entity_class', $class)->where('original_id', $id)->sole();
            $this->assertSame($a->id, $item->struttura_id);
            $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasNoErrors();
            $this->assertDatabaseHas($table, ['id' => $id, 'struttura_id' => $a->id]);
            $this->delete($delete.$id)->assertSessionHasNoErrors();
            $item = CestinoItem::where('entity_class', $class)->where('original_id', $id)->sole();
            $this->post($store, [$key => 'Modificata A'])->assertSessionHasNoErrors();
            $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasErrors('catalogo');
            $this->assertDatabaseHas('cestino_items', ['id' => $item->id]);
        }
    }

    public function test_inizializzazione_idempotente_personalizzazioni_e_nuova_struttura(): void
    {
        [$a, $b] = $this->context();
        $original = DB::table('rilasciato_da')->insertGetId(['name' => 'Extra globale', 'attivo' => 0]);
        $snapshot = DB::table('rilasciato_da')->where('id', $original)->first();
        foreach (['rilasciato_da' => 'name', 'tipo_via' => 'abbr'] as $table => $key) {
            $initial = DB::table($table)->where('struttura_id', $a->id)->orderBy('id')->get();
            $first = $initial->first();
            DB::table($table)->where('id', $first->id)->update([$key => 'Personale', 'attivo' => 0]);
            $removed = $initial->last()->id;
            DB::table($table)->where('id', $removed)->delete();
            $service = app(CataloghiIndirizziStrutturaService::class);
            $service->initialize($a->id);
            $service->initialize($a->id);
            $this->seed(\Database\Seeders\TipoViaSeeder::class);
            $this->seed(\Database\Seeders\RilasciatoDaSeeder::class);
            $this->assertDatabaseHas($table, ['id' => $first->id, $key => 'Personale', 'attivo' => 0]);
            $this->assertDatabaseMissing($table, ['id' => $removed]);
            $c = $this->structureFor(null);
            $this->assertSame($initial->count(), DB::table($table)->where('struttura_id', $c->id)->count());
            $this->assertSame($first->$key, DB::table($table)->where('struttura_id', $b->id)->orderBy('id')->value($key));
        }
        $this->assertEquals($snapshot, DB::table('rilasciato_da')->where('id', $original)->first());
    }

    public function test_riferimenti_utilizzati_conservati_e_ripristino_legacy_non_attribuito(): void
    {
        [$a] = $this->context('super_admin');
        $r = RilasciatoDa::where('name', 'Comune')->sole();
        $via = TipoVia::where('abbr', 'Via')->sole();
        $customer = \App\Models\Customers::create(['struttura_id' => $a->id, 'name' => 'Sintetico', 'surname' => 'Audit', 'rilasciato_reg' => 'Comune', 'typeaway' => 'Via']);
        $this->delete('/rilasciato/'.$r->id)->assertSessionHasErrors('name');
        $this->put('/rilasciato/'.$r->id, ['name' => 'Rinominata'])->assertSessionHasErrors('name');
        $this->delete('/tipovia/'.$via->id)->assertSessionHasErrors('abbr');
        $this->put('/tipovia_update/'.$via->id, ['abbr' => 'Rinominata'])->assertSessionHasErrors('abbr');
        $this->assertSame('Comune', $customer->fresh()->rilasciato_reg);
        foreach ([RilasciatoDa::class => 'name', TipoVia::class => 'abbr'] as $class => $field) {
            $item = CestinoItem::create(['entity_class' => $class, 'entity_type' => 'Configurazioni', 'original_id' => 99999, 'payload' => [$field => 'Storico globale'], 'deleted_at' => now()]);
            $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasErrors('catalogo');
            $this->assertDatabaseHas('cestino_items', ['id' => $item->id]);
        }
    }

    public function test_validazioni_autorizzazioni_e_cestino_estraneo(): void
    {
        $this->get('/rilasciato')->assertRedirect('/login');
        $this->get('/tipovia')->assertRedirect('/login');
        [$a, $b, $u] = $this->context();
        $this->post('/rilasciato', [])->assertSessionHasErrors('name');
        $this->post('/rilasciato', ['name' => str_repeat('x', 192)])->assertSessionHasErrors('name');
        $this->post('/tipovia_store', ['abbr' => str_repeat('x', 21), 'attivo' => 7])->assertSessionHasErrors(['abbr', 'attivo']);
        $this->post('/tipovia_store', ['abbr' => 'Valida', 'descrizione' => str_repeat('x', 192)])->assertSessionHasErrors('descrizione');
        $this->get('/rilasciato?q=!!!')->assertViewHas('rilasciati', fn ($p) => $p->total() === 0);
        $this->get('/tipovia?q=!!!')->assertViewHas('tipovia', fn ($p) => $p->total() === 0);
        $other = CestinoItem::create(['entity_type' => 'Tipo Via', 'struttura_id' => $b->id, 'entity_class' => TipoVia::class, 'original_id' => 99999, 'payload' => ['struttura_id' => $b->id, 'abbr' => 'Estranea'], 'deleted_at' => now()]);
        $this->post('/cestino/'.$other->id.'/ripristina')->assertNotFound();
        $this->delete('/cestino/'.$other->id)->assertNotFound();
        $this->get('/rilasciato?sid='.$b->id)->assertForbidden();
        $this->withSession(['struttura_corrente_id' => $b->id])->post('/tipovia_store', ['abbr' => 'Attacco'])->assertForbidden();
        $this->actingAs($this->actor('unknown'))->withSession(['struttura_corrente_id' => $a->id]);
        $this->post('/rilasciato', ['name' => 'Attacco'])->assertForbidden();
        $this->post('/tipovia_store', ['abbr' => 'Attacco'])->assertForbidden();
    }

    public function test_valori_testuali_legacy_rimangono_selezionati_nei_consumatori(): void
    {
        [$a] = $this->context('super_admin');
        $customer = \App\Models\Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'surname' => 'Legacy', 'typeaway' => 'Via storica fuori catalogo', 'rilasciato_reg' => 'Autorita storica libera']);
        $this->get('/clienti/'.$customer->id.'/modifica')->assertOk()->assertSee('value="Via storica fuori catalogo" selected', false)->assertSee('value="Autorita storica libera" selected', false);
        $sheet = \App\Models\Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'or_typeaway' => 'Strada storica libera']);
        $this->get('/schedine/'.$sheet->id.'/modifica')->assertOk()->assertSee('value="Strada storica libera" selected', false);
        $this->assertSame('Via storica fuori catalogo', $customer->fresh()->typeaway);
    }

    public function test_recupero_riferimenti_json_import_e_archivi_conserva_payload_e_scope(): void
    {
        [$a, $b, $user] = $this->context('super_admin');
        DB::table('rilasciato_da')->insert(['name' => 'Autorita import storica', 'attivo' => 0]);
        DB::table('tipo_via')->insert(['abbr' => 'Import', 'nome' => 'Via import storica', 'descrizione' => 'Storica', 'attivo' => 1]);
        $batch = DB::table('customer_import_batches')->insertGetId(['struttura_id' => $a->id, 'user_id' => $user->id, 'original_name' => 'fixture.csv', 'stored_path' => 'fixture.csv']);
        $raw = json_encode(['dato' => 'Originale sintetico']);
        $normalized = json_encode(['rilasciato_da' => 'Autorita import storica', 'tipo_via_strada' => 'Via import storica']);
        $id = DB::table('customer_import_rows')->insertGetId(['batch_id' => $batch, 'row_number' => 1, 'raw_payload' => $raw, 'normalized_payload' => $normalized]);
        DB::table('rilasciato_da')->insert(['name' => 'Autorita archivio storica', 'attivo' => 1]);
        $item = CestinoItem::create(['entity_type' => 'Web Check-in', 'entity_class' => \App\Models\WebCheckinRichiesta::class, 'struttura_id' => $a->id, 'original_id' => 99999, 'payload' => ['schedina' => ['or_published' => 'Autorita archivio storica']], 'deleted_at' => now()]);
        $before = DB::table('customer_import_rows')->where('id', $id)->first();
        app(CataloghiIndirizziStrutturaService::class)->recoverLegacyReferences();
        app(CataloghiIndirizziStrutturaService::class)->recoverLegacyReferences();
        $this->assertDatabaseHas('rilasciato_da', ['struttura_id' => $a->id, 'name' => 'Autorita import storica', 'attivo' => 0]);
        $this->assertDatabaseHas('rilasciato_da', ['struttura_id' => $a->id, 'name' => 'Autorita archivio storica']);
        $this->assertDatabaseMissing('rilasciato_da', ['struttura_id' => $b->id, 'name' => 'Autorita import storica']);
        $this->assertDatabaseHas('tipo_via', ['struttura_id' => $a->id, 'nome' => 'Via import storica']);
        $this->assertEquals($before, DB::table('customer_import_rows')->where('id', $id)->first());
        $this->assertSame('Autorita archivio storica', $item->fresh()->payload['schedina']['or_published']);
    }
}
