<?php

namespace Tests\Feature;

use App\Models\{TipoCliente, Customers, CestinoItem};
use App\Services\{TipoClienteSistemaService, CestinoService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TipoClienteSistemaTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private function context(string $role = 'struttura_user'): array
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin); $s = $this->structureFor($owner);
        $u = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $s->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id]);
        return [$s, $u];
    }

    public static function roles(): array { return [['super_admin'], ['admin'], ['proprietario'], ['struttura_user']]; }

    #[DataProvider('roles')]
    public function test_consultazione_e_scritture_vietate_a_tutti_i_ruoli(string $role): void
    {
        $this->context($role);
        $before = DB::table('tipo_cliente')->orderBy('id')->get()->toJson();
        $this->get('/tipo_cliente')->assertOk()->assertSee('Classificazioni predefinite del sistema, non modificabili.')
            ->assertDontSee('Nuovo Tipo Cliente')->assertDontSee('modalTipoCliente')->assertDontSee('Azioni');
        $this->post('/tipo_cliente', ['codice' => 'EXTRA', 'descrizione' => 'Extra', 'struttura_id' => 999])->assertForbidden();
        foreach (TipoCliente::pluck('id') as $id) {
            $this->put('/tipo_cliente/'.$id, ['attivo' => 0, 'codice' => 'ALTERATO'])->assertForbidden();
            $this->patch('/tipo_cliente/'.$id, ['descrizione' => 'Alterato'])->assertForbidden();
            $this->delete('/tipo_cliente/'.$id)->assertForbidden();
        }
        $this->put('/tipo_cliente/999999', ['attivo' => 0])->assertNotFound();
        // La rotta numerica esclude 1x: PUT non è un metodo ammesso per tale percorso.
        $this->put('/tipo_cliente/1x', ['attivo' => 0])->assertStatus(405);
        $this->assertSame($before, DB::table('tipo_cliente')->orderBy('id')->get()->toJson());
    }

    public function test_ricerca_pagine_e_catalogo_comune_due_strutture(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null);
        $this->get('/tipo_cliente?q=ospite')->assertOk()->assertViewHas('tipiClienti', fn ($p) => $p->total() === 1 && $p->first()->codice === 'OSPITE');
        $this->get('/tipo_cliente?q=inesistente&page=2')->assertOk()->assertViewHas('tipiClienti', fn ($p) => $p->total() === 0);
        $this->actingAs($this->actor('struttura_user', null, $b->id))->withSession(['struttura_corrente_id' => $b->id]);
        $this->get('/tipo_cliente')->assertOk()->assertViewHas('tipiClienti', fn ($p) => $p->total() === 3);
        $this->assertSame(3, DB::table('tipo_cliente')->count());
        $this->withSession(['struttura_corrente_id' => $a->id])->get('/tipo_cliente')->assertOk()->assertViewHas('tipiClienti', fn ($p) => $p->total() === 3);
    }

    public function test_inizializzazione_seed_idempotenza_id_extra_e_relazioni_preservati(): void
    {
        [$s] = $this->context();
        $customer = Customers::create(['struttura_id' => $s->id, 'type_housed' => 'Ospite', 'name' => 'Persona', 'surname' => 'Sintetica']);
        $before = $customer->fresh()->getRawOriginal(); $ids = TipoCliente::pluck('id', 'codice')->all();
        DB::table('tipo_cliente')->insert(['codice' => 'STORICO', 'descrizione' => 'Da conservare', 'attivo' => false]);
        $extra = DB::table('tipo_cliente')->where('codice', 'STORICO')->first();
        DB::table('tipo_cliente')->where('codice', 'OSPITE')->update(['attivo' => false, 'descrizione' => 'Modificata']);
        $this->seed(\Database\Seeders\TipoClienteSeeder::class); $this->seed(\Database\Seeders\TipoClienteSeeder::class);
        $this->assertSame($ids, TipoCliente::whereIn('codice', array_keys($ids))->pluck('id', 'codice')->all());
        $this->assertSame($before, $customer->fresh()->getRawOriginal());
        $this->assertEquals($extra, DB::table('tipo_cliente')->where('codice', 'STORICO')->first());
        $this->get('/tipo_cliente')->assertOk()->assertViewHas('tipiClienti', fn ($p) => $p->total() === 3)->assertDontSee('Da conservare');
        $this->get('/clienti/nuovo')->assertOk()->assertDontSee('Da conservare');
        $this->assertDatabaseHas('tipo_cliente', ['id' => $ids['OSPITE'], 'descrizione' => 'Ospite', 'attivo' => true]);
        $stable = DB::table('tipo_cliente')->orderBy('id')->get()->toJson();
        app(TipoClienteSistemaService::class)->initialize(); $this->assertSame($stable, DB::table('tipo_cliente')->orderBy('id')->get()->toJson());
        DB::table('tipo_cliente')->where('codice', 'RICHIESTA')->delete();
        app(TipoClienteSistemaService::class)->initialize(); $this->assertSame(1, TipoCliente::where('codice', 'RICHIESTA')->count());
    }

    public static function classificazioni(): array { return [['Ospite'], ['Componente'], ['Richiesta']]; }

    #[DataProvider('classificazioni')]
    public function test_creazione_modifica_classificazione_e_obbligatori_esistenti(string $type): void
    {
        [$s] = $this->context();
        \App\Models\GeoNazione::firstOrCreate(['nome' => 'Francia'], ['codice_iso2' => 'FR', 'cittadinanza' => 'Francese', 'is_italia' => false]);
        \App\Models\TipoDocumento::firstOrCreate(['codice' => 'PASOR'], ['descrizione' => 'PASSAPORTO']);
        \App\Models\TipoVia::firstOrCreate(['struttura_id' => $s->id, 'nome' => 'Via'], ['abbr' => 'VIA', 'attivo' => true]);
        \App\Models\RilasciatoDa::firstOrCreate(['struttura_id' => $s->id, 'name' => 'Autorità'], ['attivo' => true]);
        $data = ['type_cliente' => $type, 'name' => 'Persona', 'surname' => 'Sintetica', 'privacy_consent' => 1,
            'email' => 'persona@example.test', 'sex' => 'M', 'country' => 'Francia', 'region' => 'Regione', 'province' => 'Provincia', 'city' => 'Parigi',
            'typeaway' => 'Via', 'address' => 'Via Sintetica', 'number' => '1',
            'country_reg' => 'Francia', 'region_reg' => 'Regione', 'prov_reg' => 'Provincia', 'city_reg' => 'Parigi',
            'ciudadania_reg' => 'Francese', 'nac_reg' => '1980-01-01', 'type_doc_reg' => 'PASSAPORTO',
            'num_doc_reg' => 'QA123', 'date_pub_reg' => '2025-01-01', 'expire_reg' => '2030-01-01',
            'rilasciato_reg' => 'Autorità', 'country_doc_reg' => 'Francia'];
        $this->post('/clienti', $data)->assertSessionHasNoErrors()->assertRedirect();
        $c = Customers::where('struttura_id', $s->id)->sole();
        $this->assertSame($type, $c->type_housed);
        $data['name'] = 'Aggiornata';
        $this->put('/clienti/'.$c->id, $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Aggiornata', $c->fresh()->name);
        $this->assertSame($type, $c->fresh()->type_housed);
        unset($data['sex'], $data['num_doc_reg']);
        if ($type !== 'Richiesta') {
            $this->post('/clienti', $data)->assertSessionHasErrors($type === 'Ospite' ? ['sex', 'num_doc_reg'] : ['sex']);
            $this->put('/clienti/'.$c->id, $data)->assertSessionHasErrors($type === 'Ospite' ? ['sex', 'num_doc_reg'] : ['sex']);
        } else {
            $minimal = ['type_cliente' => 'Richiesta', 'name' => 'Richiesta', 'surname' => 'Incompleta', 'privacy_consent' => 1, 'email' => 'persona@example.test'];
            $this->put('/clienti/'.$c->id, $minimal)->assertSessionHasNoErrors();
            $this->put('/clienti/'.$c->id, $minimal + ['save_mode' => 'to_schedina'])->assertSessionHasErrors('type_cliente');
            $this->assertSame('Richiesta', $c->fresh()->type_housed);
        }
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        $this->get('/clienti/'.$c->id.'/modifica')->assertNotFound();
        $this->put('/clienti/'.$c->id, ['type_cliente' => 'Richiesta', 'name' => 'Estranea', 'surname' => 'Sintetica', 'privacy_consent' => 1])->assertNotFound();
        $this->assertNotSame('Estranea', $c->fresh()->name);
    }

    public function test_modello_e_cestino_non_consentono_scritture(): void
    {
        $this->context(); $t = TipoCliente::firstOrFail();
        $before = DB::table('tipo_cliente')->orderBy('id')->get()->toJson();
        foreach ([fn () => $t->update(['attivo' => false]), fn () => $t->delete(), fn () => app(CestinoService::class)->archiveModel($t)] as $write) {
            try { $write(); $this->fail('Scrittura non bloccata'); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
        }
        $item = CestinoItem::create(['entity_class' => TipoCliente::class, 'entity_type' => 'Tipo Cliente', 'original_id' => $t->id, 'payload' => ['codice' => 'ALTERATO'], 'deleted_at' => now()]);
        try { app(CestinoService::class)->restoreItem($item); $this->fail('Ripristino non bloccato'); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->assertDatabaseHas('cestino_items', ['id' => $item->id]);
        $this->assertSame($before, DB::table('tipo_cliente')->orderBy('id')->get()->toJson());
    }
}
