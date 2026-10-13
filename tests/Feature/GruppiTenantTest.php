<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Gruppo;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class GruppiTenantTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    private function context(string $role = 'struttura_user'): array
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $user = match ($role) {
            'admin' => $admin, 'proprietario' => $this->actor($role, $owner->id), default => $this->actor($role, null, $s->id)
        };
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id]);

        return [$s, $user];
    }

    private function root(int $sid, string $name = 'Business'): Gruppo
    {
        return Gruppo::withoutGlobalScopes()->where('struttura_id', $sid)->where('livello', 1)->where('nome', $name)->firstOrFail();
    }

    public function test_due_strutture_stessa_base_rinomina_isolata_seed_non_sovrascrive_e_nuova_struttura_originale(): void
    {
        [$a, $u] = $this->context();
        $b = $this->structureFor(null);
        $rows = fn ($sid) => DB::table('gruppi')->where('struttura_id', $sid)->orderBy('livello')->orderBy('nome')->get(['nome', 'descrizione', 'livello', 'tipo'])->toJson();
        $this->assertSame($rows($a->id), $rows($b->id));
        $base = config('gruppi');
        $g = $this->root($a->id);
        $this->put('/gruppi/'.$g->id, ['nome' => 'Business personale', 'livello' => 1])->assertSessionHasNoErrors();
        $this->get('/gruppi?q=personale')->assertSee('Business personale');
        $this->assertSame('Business', $this->root($b->id)->nome);
        $this->seed(\Database\Seeders\GruppiSeeder::class);
        $this->seed(\Database\Seeders\GruppoSeeder::class);
        $this->assertSame('Business personale', $g->fresh()->nome);
        $c = $this->structureFor(null);
        $this->assertSame('Business', $this->root($c->id)->nome);
        $this->assertSame($base, config('gruppi'));
        $leaf = Gruppo::withoutGlobalScopes()->where('struttura_id', $a->id)->where('nome', 'Board meeting')->firstOrFail();
        $this->delete('/gruppi/'.$leaf->id)->assertSessionHasNoErrors();
        $this->seed(\Database\Seeders\GruppiSeeder::class);
        $this->assertDatabaseMissing('gruppi', ['id' => $leaf->id]);

        $this->post('/gruppi', ['nome' => 'Solo A', 'livello' => 1, 'struttura_id' => $b->id])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gruppi', ['nome' => 'Solo A', 'struttura_id' => $a->id]);
        $this->assertDatabaseMissing('gruppi', ['nome' => 'Solo A', 'struttura_id' => $b->id]);
    }

    public function test_rinomina_preserva_associazioni_clienti_schedine_componenti_e_altri_tenant(): void
    {
        [$a, $u] = $this->context();
        $b = $this->structureFor(null);
        $g = $this->root($a->id);
        $c = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico A', 'group' => 'Business', 'subgroup' => 'Meeting']);
        $cb = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Sintetico B', 'group' => 'Business']);
        $s = Schedina::forceCreate(['struttura_id' => $a->id, 'customer_id' => $c->id, 'name' => 'Capogruppo', 'relationship' => 'CAPO GRUPPO', 'customer_group' => 'Business', 'customer_subgroup' => 'Meeting']);
        $part = \App\Models\Componenti::forceCreate(['struttura_id' => $a->id, 'schedina_id' => $s->id, 'name' => 'Componente']);
        $before = $part->fresh()->getAttributes();
        $this->put('/gruppi/'.$g->id, ['nome' => 'Business personale', 'livello' => 1])->assertSessionHasNoErrors();
        $this->assertSame('Business personale', $c->fresh()->group);
        $this->assertSame('Business personale', $s->fresh()->customer_group);
        $this->assertSame($c->id, $s->fresh()->customer_id);
        $this->assertSame('Business', $cb->fresh()->group);
        $this->assertSame($before, $part->fresh()->getAttributes());
        $child = Gruppo::withoutGlobalScopes()->where('struttura_id', $a->id)->where('nome', 'Meeting')->firstOrFail();
        $this->put('/gruppi/'.$child->id, ['nome' => 'Meeting personale', 'livello' => 2, 'parent_id' => $g->id])->assertSessionHasNoErrors();
        $this->assertSame('Meeting personale', $c->fresh()->subgroup);
        $this->assertSame('Meeting personale', $s->fresh()->customer_subgroup);
        $this->delete('/gruppi/'.$child->id)->assertSessionHasErrors('gruppo');
    }

    public static function roles(): array
    {
        return [['struttura_user'], ['proprietario'], ['admin'], ['super_admin']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_ruoli_nel_contesto_selezionato_e_id_estranei_rifiutati(string $role): void
    {
        [$a, $u] = $this->context($role);
        $b = $this->structureFor($this->ownerFor($this->actor('admin')));
        $other = $this->root($b->id);
        $other->update(['nome' => 'Privato B']);
        $this->get('/gruppi?q=Privato')->assertOk()->assertDontSee('Privato B');
        $this->put('/gruppi/'.$other->id, ['nome' => 'Attacco', 'livello' => 1])->assertNotFound();
        $this->delete('/gruppi/'.$other->id)->assertNotFound();
        $this->post('/gruppi', ['nome' => 'Attacco', 'livello' => 2, 'parent_id' => $other->id])->assertSessionHasErrors('parent_id');
        $this->assertSame('Privato B', $other->fresh()->nome);
        if ($role !== 'super_admin') {
            $this->withSession(['struttura_corrente_id' => $b->id])->get('/gruppi')->assertForbidden();
        }
    }

    public function test_duplicati_vincoli_annullamento_e_ripristino(): void
    {
        [$a, $u] = $this->context();
        $this->post('/gruppi', ['nome' => 'Business', 'livello' => 1])->assertSessionHasErrors('nome');
        $this->post('/gruppi', ['nome' => 'Foglia libera', 'livello' => 1])->assertSessionHasNoErrors();
        $g = $this->root($a->id, 'Foglia libera');
        $this->delete('/gruppi/'.$g->id)->assertSessionHasNoErrors();
        $item = \App\Models\CestinoItem::where('entity_class', Gruppo::class)->where('original_id', $g->id)->firstOrFail();
        $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gruppi', ['id' => $g->id, 'struttura_id' => $a->id]);
    }

    public function test_migrazione_ricostruisce_uso_condiviso_e_conserva_ambigui_originali_e_personalizzazioni(): void
    {
        [$a, $u] = $this->context();
        $b = $this->structureFor(null);
        $legacy = DB::table('gruppi')->insertGetId(['nome' => 'Legacy condiviso', 'livello' => 1, 'tipo' => 'Gruppi I']);
        DB::table('gruppi')->insert(['nome' => 'Ambiguo legacy', 'livello' => 1, 'tipo' => 'Gruppi I']);
        DB::table('gruppi')->insert(['nome' => 'Ambiguo legacy', 'livello' => 1, 'tipo' => 'Gruppi I']);
        $ca = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Cliente A', 'group' => 'Legacy condiviso']);
        $cb = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente B', 'group' => 'Legacy condiviso']);
        $amb = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente ambiguo', 'group' => 'Ambiguo legacy']);
        $before = DB::table('clienti')->orderBy('id')->get()->toJson();
        $originals = DB::table('gruppi')->whereNull('struttura_id')->orderBy('id')->get()->toJson();
        $migration = require base_path('database/migrations/2026_10_13_210000_isolate_gruppi_per_struttura.php');
        $migration->up();
        $this->assertDatabaseHas('gruppi', ['nome' => 'Legacy condiviso', 'struttura_id' => $a->id]);
        $this->assertDatabaseHas('gruppi', ['nome' => 'Legacy condiviso', 'struttura_id' => $b->id]);
        $this->assertDatabaseMissing('gruppi', ['nome' => 'Ambiguo legacy', 'struttura_id' => $b->id]);
        $this->assertSame($before, DB::table('clienti')->orderBy('id')->get()->toJson());
        $this->assertSame($originals, DB::table('gruppi')->whereNull('struttura_id')->orderBy('id')->get()->toJson());
        $root = $this->root($a->id);
        $this->put('/gruppi/'.$root->id, ['nome' => 'Personalizzazione', 'livello' => 1])->assertSessionHasNoErrors();
        $migration->up();
        $this->assertSame('Personalizzazione', $root->fresh()->nome);
        $this->get('/clienti/'.$amb->id.'/modifica')->assertNotFound();
        $this->actingAs($this->actor('struttura_user', null, $b->id))->withSession(['struttura_corrente_id' => $b->id]);
        $this->get('/clienti/'.$amb->id.'/modifica')->assertOk()->assertSee('valore storico conservato');
        $this->put('/clienti/'.$amb->id, ['name' => 'Cliente ambiguo aggiornato', 'surname' => 'Sintetico', 'privacy_consent' => 1, 'type_cliente' => 'Richiesta', 'group' => 'Ambiguo legacy'])->assertSessionHasNoErrors();
        $this->assertSame('Ambiguo legacy', $amb->fresh()->group);
    }

    public function test_gruppi_privati_non_associabili_neanche_con_richieste_dirette_o_bozze(): void
    {
        [$a, $u] = $this->context();
        $b = $this->structureFor(null);
        $other = $this->root($b->id);
        $other->update(['nome' => 'Privato B']);
        $this->post('/clienti', ['name' => 'Cliente A', 'surname' => 'Sintetico', 'privacy_consent' => 1, 'type_cliente' => 'Richiesta', 'group' => 'Privato B'])->assertSessionHasErrors('group');
        $this->post('/clienti', ['name' => 'Bozza A', 'group' => 'Privato B', 'save_mode' => 'draft'])->assertSessionHasErrors('group');
        $this->assertDatabaseMissing('clienti', ['name' => 'Cliente A']);
        $this->assertDatabaseMissing('clienti', ['name' => 'Bozza A']);
    }

    public function test_archivio_rinomina_e_ripristino_non_creano_orfani(): void
    {
        [$a, $u] = $this->context();
        $g = $this->root($a->id);
        $c = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Cliente in archivio', 'group' => 'Business']);
        $item = app(\App\Services\CestinoService::class)->archiveModel($c);
        $c->delete();
        $this->put('/gruppi/'.$g->id, ['nome' => 'Business archivio', 'livello' => 1])->assertSessionHasNoErrors();
        $this->assertSame('Business archivio', $item->fresh()->payload['group']);
        $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasNoErrors();
        $this->assertSame('Business archivio', Customers::withoutGlobalScopes()->findOrFail($c->id)->group);
        $parent = Gruppo::create(['nome' => 'Padre cestino', 'livello' => 1, 'tipo' => 'Gruppi I']);
        $child = Gruppo::create(['nome' => 'Figlio cestino', 'livello' => 2, 'tipo' => 'Gruppi II', 'parent_id' => $parent->id]);
        $this->delete('/gruppi/'.$child->id)->assertSessionHasNoErrors();
        $bin = \App\Models\CestinoItem::where('entity_class', Gruppo::class)->where('original_id', $child->id)->firstOrFail();
        $this->delete('/gruppi/'.$parent->id)->assertSessionHasNoErrors();
        $this->post('/cestino/'.$bin->id.'/ripristina')->assertSessionHasErrors('gruppo');
        $this->assertDatabaseHas('cestino_items', ['id' => $bin->id]);
        $this->assertDatabaseMissing('gruppi', ['id' => $child->id]);
        $parentBin = \App\Models\CestinoItem::where('entity_class', Gruppo::class)->where('original_id', $parent->id)->firstOrFail();
        $this->post('/cestino/'.$parentBin->id.'/ripristina')->assertSessionHasNoErrors();
        $this->post('/cestino/'.$bin->id.'/ripristina')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gruppi', ['id' => $child->id, 'parent_id' => $parent->id, 'struttura_id' => $a->id]);

    }

    public function test_ruolo_legacy_account_inattivo_e_ricerca_malformata(): void
    {
        [$a, $u] = $this->context();
        $this->getJson('/gruppi?q[]=valore')->assertUnprocessable();
        $g = $this->root($a->id);
        $this->put('/gruppi/'.$g->id.'x', ['nome' => 'ID errato', 'livello' => 1])->assertNotFound();
        $this->from('/gruppi')->post('/gruppi', ['_group_form' => 'create', 'nome' => ['malformato'], 'descrizione' => ['malformata'], 'livello' => 1])->assertSessionHasErrors('nome');
        $this->get('/gruppi')->assertOk();
        $this->postJson('/clienti', ['name' => 'Cliente', 'surname' => 'Sintetico', 'privacy_consent' => 1, 'type_cliente' => 'Richiesta', 'group' => ['valore']])->assertUnprocessable()->assertJsonValidationErrors('group');
        $this->actingAs($this->actor('reception', null, $a->id))->get('/gruppi')->assertForbidden();
        $u->update(['attivo' => false]);
        $this->actingAs($u)->get('/gruppi')->assertRedirect('/login');
    }

    public function test_vincolo_database_impedisce_padre_di_altra_struttura(): void
    {
        [$a, $u] = $this->context();
        $b = $this->structureFor(null);
        $root = $this->root($b->id);
        try {
            DB::table('gruppi')->insert(['struttura_id' => $a->id, 'nome' => 'Tentativo cross tenant', 'livello' => 2, 'tipo' => 'Gruppi II', 'parent_id' => $root->id]);
            $this->fail('Il database ha accettato un padre di un’altra struttura.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertSame('23000', $error->errorInfo[0]);
        }
        $this->assertDatabaseMissing('gruppi', ['nome' => 'Tentativo cross tenant']);
    }
}
