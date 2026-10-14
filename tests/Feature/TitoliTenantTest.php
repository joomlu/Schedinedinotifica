<?php

namespace Tests\Feature;

use App\Models\{Titolo, Customers, Schedina, CestinoItem};
use App\Services\{TitoliStrutturaService, CestinoService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TitoliTenantTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private function context(string $role = 'struttura_user'): array
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin); $s = $this->structureFor($owner);
        $user = match ($role) { 'admin' => $admin, 'proprietario' => $this->actor($role, $owner->id), default => $this->actor($role, null, $s->id) };
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id]);
        return [$s, $user];
    }

    private function title(int $sid, string $name = 'Sig.'): Titolo
    {
        return Titolo::withoutGlobalScopes()->where('struttura_id', $sid)->where('nome', $name)->sole();
    }

    public function test_azioni_relative_non_confondono_dominio_e_percorso_schedine(): void
    {
        $this->context();
        $html = $this->get('/titolo?q=Sig.')->assertOk()->getContent();
        preg_match_all('/<form[^>]+action="([^"]+)"/', $html, $matches);
        $actions = array_filter($matches[1], fn ($action) => str_contains($action, '/titolo'));
        $this->assertNotEmpty($actions);
        foreach ($actions as $action) {
            $this->assertStringStartsWith('/titolo', $action);
            $this->assertStringNotContainsString('/schedine', $action);
        }
    }

    public function test_base_80_protetta_seed_ripetuti_e_nuove_strutture(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null);
        $this->assertCount(80, config('titoli'));
        $this->assertSame(80, DB::table('titolo')->where('struttura_id', $a->id)->count());
        DB::table('titolo')->insert(['nome' => 'Sig.s', 'descrizione' => 'Signore', 'attivo' => true]);
        DB::table('titolo')->insert(['nome' => 'Sigg.', 'descrizione' => 'Signori', 'attivo' => true]);
        $originals = DB::table('titolo')->whereNull('struttura_id')->orderBy('id')->get()->toJson();
        $sig = $this->title($a->id);
        $this->put('/titolo/'.$sig->id, ['nome' => 'Personale A', 'descrizione' => 'Mia', 'attivo' => 0])->assertSessionHasNoErrors();
        $ing = $this->title($a->id, 'Ing.'); $this->delete('/titolo/'.$ing->id)->assertSessionHasNoErrors();
        $this->seed(\Database\Seeders\TitoloSeeder::class); $this->seed(\Database\Seeders\TitoloSeeder::class);
        $this->assertDatabaseMissing('titolo', ['id' => $ing->id]);
        $this->assertSame('Mia', $sig->fresh()->descrizione); $this->assertFalse($sig->fresh()->attivo);
        $this->assertSame('Sig.', $this->title($b->id)->nome);
        $c = $this->structureFor(null); $this->assertSame(80, DB::table('titolo')->where('struttura_id', $c->id)->count());
        $this->assertSame($originals, DB::table('titolo')->whereNull('struttura_id')->orderBy('id')->get()->toJson());
        $this->assertDatabaseMissing('titolo', ['struttura_id' => $c->id, 'nome' => 'Sig.s']);
    }

    public static function roles(): array { return [['struttura_user'], ['proprietario'], ['admin'], ['super_admin']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_ruoli_id_query_e_sessioni_estranei(string $role): void
    {
        [$a, $u] = $this->context($role); $b = $this->structureFor($this->ownerFor($this->actor('admin')));
        $other = $this->title($b->id); $other->update(['nome' => 'Estraneo B']);
        $this->get('/titolo?q=Estraneo&page=1')->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 0);
        $this->put('/titolo/'.$other->id, ['nome' => 'Attacco', 'attivo' => 1])->assertNotFound();
        $this->delete('/titolo/'.$other->id)->assertNotFound();
        $this->put('/titolo/'.$other->id.'x', ['nome' => 'Attacco'])->assertNotFound();
        $this->post('/titolo', ['nome' => 'Solo A', 'attivo' => 1, 'struttura_id' => $b->id, 'base_nome' => 'Sig.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('titolo', ['nome' => 'Solo A', 'struttura_id' => $a->id, 'base_nome' => null]);
        $this->assertDatabaseMissing('titolo', ['nome' => 'Solo A', 'struttura_id' => $b->id]);
        $this->assertSame('Estraneo B', $other->fresh()->nome);
        if ($role !== 'super_admin') {
            $this->get('/titolo?sid='.$b->id)->assertForbidden();
            $this->withSession(['struttura_corrente_id' => $b->id])->post('/titolo/recupera-base', ['conferma' => 1])->assertForbidden();
        }
    }

    public function test_stato_creazione_modifica_assente_e_riattivazione(): void
    {
        [$a] = $this->context();
        foreach ([0, 1] as $state) {
            $this->post('/titolo', ['nome' => 'Stato '.$state, 'attivo' => (string) $state])->assertSessionHasNoErrors();
            $t = $this->title($a->id, 'Stato '.$state); $this->assertSame((bool) $state, $t->attivo);
            $this->put('/titolo/'.$t->id, ['nome' => $t->nome, 'attivo' => (string) (1-$state)])->assertSessionHasNoErrors();
            $this->assertSame((bool) (1-$state), $t->fresh()->attivo);
            $this->put('/titolo/'.$t->id, ['nome' => $t->nome])->assertSessionHasNoErrors(); $this->assertFalse($t->fresh()->attivo);
        }
        $this->post('/titolo', ['nome' => 'Assente'])->assertSessionHasNoErrors(); $this->assertFalse($this->title($a->id, 'Assente')->attivo);
        $this->get('/titolo?q=Assente')->assertSee('Disattivo');
    }

    public function test_duplicati_validazioni_errori_e_csrf(): void
    {
        $this->get('/titolo')->assertRedirect('/login'); [$a, $u] = $this->context();
        foreach ([['nome' => 'Sig.'], ['nome' => 'sig.'], ['nome' => ' Sig. ']] as $data) $this->post('/titolo', $data)->assertSessionHasErrors('nome');
        $this->post('/titolo', [])->assertSessionHasErrors('nome');
        $this->post('/titolo', ['nome' => str_repeat('x', 51), 'descrizione' => str_repeat('d', 151)])->assertSessionHasErrors(['nome', 'descrizione']);
        $this->post('/titolo', ['nome' => 'Errato', 'attivo' => 'errato'])->assertSessionHasErrors('attivo');
        $this->from('/titolo')->post('/titolo', ['nome' => ['x'], 'descrizione' => ['y'], '_titolo_form' => 'create'])->assertSessionHasErrors(['nome', 'descrizione']);
        $this->get('/titolo')->assertOk();
        $this->getJson('/titolo?q[]=x')->assertUnprocessable();
        $this->getJson('/titolo?q='.str_repeat('x', 192))->assertUnprocessable();
        $this->post('/titolo', ['nome' => 'CSRF', '_token' => 'errato'])->assertStatus(419);
        $this->post('/titolo/recupera-base', ['conferma' => 1, '_token' => 'errato'])->assertStatus(419);
        $this->actingAs($this->actor('reception', null, $a->id))->get('/titolo')->assertForbidden();
        $u->update(['attivo' => false]); $this->actingAs($u)->get('/titolo')->assertRedirect('/login');
    }

    public function test_ricerca_punti_forme_composte_descrizione_id_e_paginazione(): void
    {
        [$a] = $this->context();
        foreach (['Dott.ssa', 'dottssa', 'DOTT.SSA'] as $q) $this->get('/titolo?q='.urlencode($q))->assertOk()->assertViewHas('titoli', fn ($p) => $p->count() === 1 && $p->first()->nome === 'Dott.ssa');
        foreach (['Ing.', 'Ingegnere'] as $q) $this->get('/titolo?q='.urlencode($q))->assertViewHas('titoli', fn ($p) => $p->count() === 1 && $p->first()->nome === 'Ing.');
        $this->get('/titolo?q=Grand%27Uff.')->assertViewHas('titoli', fn ($p) => $p->first()->nome === "Grand'Uff.");
        $this->get('/titolo?q=%%%')->assertViewHas('titoli', fn ($p) => $p->total() === 0);
        $id = $this->title($a->id)->id; $this->get('/titolo?q='.$id)->assertViewHas('titoli', fn ($p) => $p->contains('id', $id));
        for ($i=0; $i<13; $i++) Titolo::create(['nome' => 'Pagina '.$i, 'attivo' => true]);
        $this->get('/titolo?q=Pagina&page=2')->assertViewHas('titoli', fn ($p) => $p->total() === 13 && $p->count() === 3 && str_contains($p->url(1), 'q=Pagina'));
        $this->get('/titolo?q=nessunrisultato')->assertViewHas('titoli', fn ($p) => $p->total() === 0);
    }

    public function test_recupero_volontario_conserva_personalizzazioni_relazioni_e_conflitti(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null); $sig = $this->title($a->id); $ing = $this->title($a->id, 'Ing.');
        $client = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'type' => 'Sig.']);
        $this->put('/titolo/'.$sig->id, ['nome' => 'Personale', 'descrizione' => 'Mia', 'attivo' => 0])->assertSessionHasNoErrors();
        $this->delete('/titolo/'.$ing->id)->assertSessionHasNoErrors();
        $this->post('/titolo', ['nome' => 'Ing.', 'descrizione' => 'Personale', 'attivo' => 0])->assertSessionHasNoErrors();
        $beforeB = DB::table('titolo')->where('struttura_id', $b->id)->get()->toJson();
        $this->post('/titolo/recupera-base', [])->assertSessionHasErrors('conferma');
        $this->post('/titolo/recupera-base', ['conferma' => 0])->assertSessionHasErrors('conferma');
        $this->post('/titolo/recupera-base', ['conferma' => 1])->assertSessionHas('warning')->assertSessionHas('success');
        $this->assertSame('Mia', $sig->fresh()->descrizione); $this->assertFalse($sig->fresh()->attivo);
        $this->assertSame('Personale', $client->fresh()->type); $this->assertSame('Sig.', $this->title($a->id)->nome);
        $this->assertSame('Personale', $this->title($a->id, 'Ing.')->descrizione);
        $this->assertFalse($this->title($a->id, 'Ing.')->attivo);
        $count = DB::table('titolo')->where('struttura_id', $a->id)->count();
        $this->post('/titolo/recupera-base', ['conferma' => 1])->assertSessionHasNoErrors();
        $this->assertSame($count, DB::table('titolo')->where('struttura_id', $a->id)->count());
        $this->assertSame($beforeB, DB::table('titolo')->where('struttura_id', $b->id)->get()->toJson());
    }

    public function test_rinomina_relazioni_live_archivi_annidati_e_divieto_eliminazione(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null); $t = $this->title($a->id);
        $client = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'A', 'type' => 'Sig.']);
        $sheet = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'A', 'customer_id' => $client->id, 'type' => 'Sig.']);
        $other = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'B', 'type' => 'Sig.']);
        $archive = CestinoItem::forceCreate(['struttura_id' => $a->id, 'entity_type' => 'Web Check-in', 'entity_class' => \App\Models\WebCheckinRichiesta::class, 'original_id' => 999, 'deleted_at' => now(), 'payload' => ['schedina' => ['type' => 'Sig.', 'name' => 'Storico']]]);
        $this->delete('/titolo/'.$t->id)->assertSessionHasErrors('titolo');
        $this->put('/titolo/'.$t->id, ['nome' => 'Signor Personale', 'attivo' => 0])->assertSessionHasNoErrors();
        $this->assertSame('Signor Personale', $client->fresh()->type); $this->assertSame('Signor Personale', $sheet->fresh()->type);
        $this->assertSame($client->id, $sheet->fresh()->customer_id); $this->assertSame('Sig.', $other->fresh()->type);
        $this->assertSame(['type' => 'Signor Personale', 'name' => 'Storico'], $archive->fresh()->payload['schedina']);
        $this->get('/clienti/'.$client->id.'/modifica')->assertOk()->assertSee('valore storico conservato');
        $this->post('/clienti', ['name' => 'Nuovo', 'surname' => 'Test', 'type' => 'Signor Personale', 'type_cliente' => 'Richiesta', 'privacy_consent' => 1])->assertSessionHasErrors('type');
    }

    public function test_selezioni_clienti_bozze_e_schedine_bloccano_titoli_estranei(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null); $t = $this->title($b->id); $t->update(['nome' => 'Solo B']);
        foreach ([['name' => 'Bozza', 'save_mode' => 'draft'], ['name' => 'Cliente', 'surname' => 'Sintetico', 'privacy_consent' => 1, 'type_cliente' => 'Richiesta']] as $data) {
            $this->post('/clienti', $data+['type' => 'Solo B'])->assertSessionHasErrors('type');
        }
        $this->postJson('/schedine', ['save_mode' => 'draft', 'type' => 'Solo B'])->assertUnprocessable()->assertJsonValidationErrors('type');
        $this->get('/clienti/nuovo')->assertDontSee('Solo B');
        $this->get('/schedine/nuova')->assertDontSee('Solo B');
    }

    public function test_cestino_privato_ripristino_e_originali_globali_protetti(): void
    {
        [$a] = $this->context(); $t = $this->title($a->id, 'Ing.');
        $this->delete('/titolo/'.$t->id)->assertSessionHasNoErrors();
        $item = CestinoItem::withoutGlobalScopes()->where('original_id', $t->id)->where('entity_class', Titolo::class)->sole();
        $this->assertSame($a->id, $item->struttura_id);
        $this->post('/cestino/'.$item->id.'/ripristina')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('titolo', ['id' => $t->id, 'struttura_id' => $a->id]);
        $global = DB::table('titolo')->insertGetId(['nome' => 'Originale intoccabile', 'attivo' => true]);
        $this->put('/titolo/'.$global, ['nome' => 'Attacco'])->assertNotFound(); $this->delete('/titolo/'.$global)->assertNotFound();
        $this->get('/titolo?q=Originale')->assertDontSee('Originale intoccabile');
        $b = $this->structureFor(null); $this->actingAs($this->actor('struttura_user', null, $b->id))->withSession(['struttura_corrente_id' => $b->id]);
        $this->post('/cestino/'.$item->id.'/ripristina')->assertNotFound();
    }

    public function test_recupero_legacy_univoco_senza_attribuzione_arbitraria(): void
    {
        [$a] = $this->context(); $b = $this->structureFor(null);
        DB::table('titolo')->insert(['nome' => 'Sig.s', 'descrizione' => 'Signore', 'attivo' => 1]);
        DB::table('titolo')->insert([['nome' => 'Ambiguo', 'attivo' => 1], ['nome' => 'Ambiguo', 'attivo' => 1]]);
        Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Storico', 'type' => 'Sig.s']);
        Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Storico B', 'type' => 'Ambiguo']);
        $before = DB::table('clienti')->orderBy('id')->get()->toJson();
        app(TitoliStrutturaService::class)->recoverLegacyReferences();
        $this->assertDatabaseHas('titolo', ['struttura_id' => $a->id, 'nome' => 'Sig.s', 'base_nome' => null]);
        $this->assertDatabaseMissing('titolo', ['struttura_id' => $b->id, 'nome' => 'Sig.s']);
        $this->assertDatabaseMissing('titolo', ['struttura_id' => $b->id, 'nome' => 'Ambiguo']);
        $this->assertSame($before, DB::table('clienti')->orderBy('id')->get()->toJson());
    }
}
