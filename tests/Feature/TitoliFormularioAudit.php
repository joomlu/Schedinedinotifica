<?php

namespace Tests\Feature;

use App\Models\Titolo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Diagnostica esplicita: soltanto infrastruttura effimera attestata. */
class TitoliFormularioAudit extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private function contesto(string $ruolo = 'super_admin'): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor($ruolo, null, $s->id))
            ->withSession(['struttura_corrente_id' => $s->id]);
    }

    public function test_ricerca_punti_descrizioni_id_e_paginazione(): void
    {
        $this->contesto();
        foreach ([['Sig.', 'Signore'], ['Sig.ra', 'Signora'], ['Sigg.', 'Signori'], ['Dott.', 'Dottore'], ['Dott.ssa', 'Dottoressa'], ['Ing.', 'Ingegnere']] as [$n, $d]) {
            Titolo::create(['nome' => $n, 'descrizione' => $d, 'attivo' => true]);
        }
        foreach (['Sig.', 'sig', 'SIG'] as $q) {
            $this->get('/titolo?q='.urlencode($q))->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 3);
        }
        $this->get('/titolo?q=Ingegnere')->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 1);
        $this->get('/titolo?q=Dott.ssa')->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 2);
        $this->get('/titolo?q=inesistente')->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 0);
        for ($i = 0; $i < 12; $i++) Titolo::create(['nome' => 'Pagina '.$i, 'descrizione' => 'Sintetica', 'attivo' => $i !== 0]);
        $this->get('/titolo?q=Pagina&page=2')->assertOk()->assertViewHas('titoli', fn ($p) => $p->total() === 12 && $p->count() === 2 && str_contains($p->url(1), 'q=Pagina'));
        $this->get('/titolo?q=Pagina&page=1')->assertSee('Disattivo');
    }

    public function test_crud_validazioni_csrf_e_cestino(): void
    {
        $this->get('/titolo')->assertRedirect('/login');
        $this->contesto();
        $this->post('/titolo', [])->assertSessionHasErrors('nome');
        $this->post('/titolo', ['nome' => str_repeat('x', 51), 'descrizione' => str_repeat('d', 151)])->assertSessionHasErrors(['nome', 'descrizione']);
        $this->post('/titolo', ['nome' => 'Titolo diagnostico', 'attivo' => 'errato'])->assertSessionHasErrors('attivo');
        $this->post('/titolo', ['nome' => 'Titolo diagnostico', '_token' => 'errato'])->assertStatus(419);
        $this->post('/titolo', ['nome' => 'Titolo diagnostico', 'descrizione' => 'Descrizione', 'attivo' => '0'])->assertSessionHasNoErrors();
        $t = Titolo::where('nome', 'Titolo diagnostico')->sole();
        $this->assertFalse($t->attivo);
        $this->put('/titolo/'.$t->id, ['nome' => 'Titolo modificato', 'descrizione' => 'Nuova', 'attivo' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($t->fresh()->attivo);
        $this->delete('/titolo/'.$t->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('titolo', ['id' => $t->id]);
        $this->assertDatabaseHas('cestino_items', ['entity_class' => Titolo::class, 'original_id' => $t->id]);
        $this->put('/titolo/999999', ['nome' => 'Assente'])->assertNotFound();
    }

    public function test_switch_deselezionato_deve_disattivare(): void
    {
        $this->contesto();
        $t = Titolo::create(['nome' => 'Stato sintetico', 'attivo' => true]);
        $this->put('/titolo/'.$t->id, ['nome' => $t->nome])->assertSessionHasNoErrors();
        $this->assertFalse($t->fresh()->attivo, 'Il formulario omette attivo quando lo switch è deselezionato.');
    }

    public function test_record_creato_in_a_non_deve_essere_letto_o_modificato_in_b(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id))->withSession(['struttura_corrente_id' => $a->id]);
        $this->post('/titolo', ['nome' => 'Privato A diagnostico', 'attivo' => '1'])->assertSessionHasNoErrors();
        $t = Titolo::where('nome', 'Privato A diagnostico')->sole();
        $this->actingAs($this->actor('struttura_user', null, $b->id))->withSession(['struttura_corrente_id' => $b->id]);
        $r = $this->get('/titolo?q=Privato&page=1');
        $r->assertOk();
        $visible = $r->viewData('titoli')->contains('id', $t->id);
        $write = $this->put('/titolo/'.$t->id, ['nome' => 'Alterato da B', 'attivo' => '1']);
        $this->assertFalse($visible || ($write->isRedirection() && $t->fresh()->nome === 'Alterato da B'), 'La struttura B legge o modifica il titolo creato da A.');
    }
}
