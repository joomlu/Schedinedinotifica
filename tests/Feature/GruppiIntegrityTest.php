<?php

namespace Tests\Feature;

use App\Models\Gruppo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GruppiIntegrityTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private $strutturaGruppi;

    private $actorGruppi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strutturaGruppi = $this->structureFor(null);
        $this->actorGruppi = $this->actor('super_admin', null, $this->strutturaGruppi->id);
        $this->actingAs($this->actorGruppi)->withSession(['struttura_corrente_id' => $this->strutturaGruppi->id]);
    }

    private function gruppo(int $level = 1, ?Gruppo $parent = null, string $name = 'Classificazione sintetica'): Gruppo
    {
        return Gruppo::create(['nome' => $name, 'livello' => $level, 'tipo' => Gruppo::tipoFromLivello($level), 'parent_id' => $parent?->id]);
    }

    public function test_non_si_puo_eliminare_un_padre_lasciando_figli_senza_gerarchia(): void
    {
        $this->actingAs($this->actorGruppi);
        $parent = $this->gruppo();
        $child = $this->gruppo(2, $parent, 'Figlio sintetico');
        $this->delete('/gruppi/'.$parent->id)->assertSessionHasErrors('gruppo');
        $this->assertDatabaseHas('gruppi', ['id' => $parent->id]);
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertDatabaseMissing('cestino_items', ['entity_class' => Gruppo::class, 'original_id' => $parent->id]);
    }

    public function test_non_si_puo_cambiare_livello_invalidando_i_figli(): void
    {
        $this->actingAs($this->actorGruppi);
        $parent = $this->gruppo();
        $child = $this->gruppo(2, $parent, 'Figlio sintetico');
        $other = $this->gruppo(1, null, 'Altro padre');
        $this->put('/gruppi/'.$parent->id, ['nome' => $parent->nome, 'livello' => 2, 'parent_id' => $other->id])->assertSessionHasErrors('livello');
        $this->assertSame(1, $parent->fresh()->livello);
        $this->assertSame($parent->id, $child->fresh()->parent_id);
    }

    public function test_non_si_puo_creare_un_ciclo_tramite_un_discendente(): void
    {
        $this->actingAs($this->actorGruppi);
        $parent = $this->gruppo();
        $child = $this->gruppo(2, $parent, 'Figlio sintetico');
        $this->put('/gruppi/'.$parent->id, ['nome' => $parent->nome, 'livello' => 3, 'parent_id' => $child->id])->assertSessionHasErrors('parent_id');
        $this->assertNull($parent->fresh()->parent_id);
    }

    public function test_crud_validazioni_ricerca_paginazione_e_cestino(): void
    {
        $this->actingAs($this->actorGruppi);
        $this->from('/gruppi')->post('/gruppi', [])->assertSessionHasErrors(['nome', 'livello']);
        $this->post('/gruppi', ['nome' => str_repeat('x', 101), 'livello' => 4])->assertSessionHasErrors(['nome', 'livello']);
        $this->post('/gruppi', ['nome' => 'Radice CRUD', 'livello' => 1])->assertSessionHasNoErrors();
        $root = Gruppo::where('nome', 'Radice CRUD')->firstOrFail();
        $this->post('/gruppi', ['nome' => 'Figlio CRUD', 'livello' => 2])->assertSessionHasErrors('parent_id');
        $this->post('/gruppi', ['nome' => 'Figlio CRUD', 'livello' => 2, 'parent_id' => $root->id])->assertSessionHasNoErrors();
        $child = Gruppo::where('nome', 'Figlio CRUD')->firstOrFail();
        $this->post('/gruppi', ['nome' => 'Errato', 'livello' => 3, 'parent_id' => $root->id])->assertSessionHasErrors('parent_id');
        $this->post('/gruppi', ['nome' => 'Foglia CRUD', 'livello' => 3, 'parent_id' => $child->id])->assertSessionHasNoErrors();
        $leaf = Gruppo::where('nome', 'Foglia CRUD')->firstOrFail();
        $this->put('/gruppi/'.$leaf->id, ['nome' => 'Foglia aggiornata', 'livello' => 3, 'parent_id' => $child->id, 'descrizione' => 'Descrizione sintetica'])->assertSessionHasNoErrors();
        $this->get('/gruppi?q=aggiornata')->assertOk()->assertSee('Foglia aggiornata')->assertDontSee('Radice CRUD</td>', false);
        $this->get('/gruppi?q=inesistente')->assertOk()->assertDontSee('Foglia aggiornata');
        for ($i = 0; $i < 12; $i++) {
            $this->gruppo(1, null, 'Pagina sintetica '.$i);
        }
        $this->get('/gruppi?q=Pagina&page=2')->assertOk()->assertViewHas('gruppi', fn ($p) => $p->total() === 12 && $p->count() === 2);
        $this->delete('/gruppi/'.$leaf->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('gruppi', ['id' => $leaf->id]);
        $this->assertDatabaseHas('cestino_items', ['entity_class' => Gruppo::class, 'original_id' => $leaf->id]);
        $this->delete('/gruppi/'.$child->id)->assertSessionHasNoErrors();
        $this->delete('/gruppi/'.$root->id)->assertSessionHasNoErrors();
        $this->put('/gruppi/999999', ['nome' => 'Assente', 'livello' => 1])->assertNotFound();
    }

    public function test_anonimo_e_csrf_non_possono_scrivere(): void
    {
        $root = $this->gruppo();
        auth()->logout();
        $this->get('/gruppi')->assertRedirect('/login');
        $this->post('/gruppi', ['nome' => 'Anonimo', 'livello' => 1])->assertRedirect('/login');
        $this->actingAs($this->actorGruppi);
        $this->delete('/gruppi/'.$root->id, ['_token' => 'token-errato'])->assertStatus(419);
        $this->assertDatabaseHas('gruppi', ['id' => $root->id]);
    }

    public function test_cliente_con_omonimi_sotto_padri_diversi_associa_rimuove_e_rifiuta_catena_errata(): void
    {
        $a = $this->gruppo(1, null, 'Radice A');
        $b = $this->gruppo(1, null, 'Radice B');
        $aa = $this->gruppo(2, $a, 'Nome condiviso');
        $bb = $this->gruppo(2, $b, 'Nome condiviso');
        $aaa = $this->gruppo(3, $aa, 'Foglia A');
        $bbb = $this->gruppo(3, $bb, 'Foglia B');
        $structure = $this->strutturaGruppi;
        $actor = $this->actor('struttura_user', null, $structure->id);
        $this->actingAs($actor);
        $payload = ['name' => 'Cliente sintetico', 'surname' => 'Gruppi', 'type_cliente' => 'Richiesta', 'privacy_consent' => 1,
            'group' => 'Radice B', 'subgroup' => 'Nome condiviso', 'subgroup1' => 'Foglia B'];
        $this->post('/clienti', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $customer = \App\Models\Customers::query()->where('name', 'Cliente sintetico')->firstOrFail();
        $this->assertSame('Foglia B', $customer->subgroup1);
        $this->put('/clienti/'.$customer->id, array_merge($payload, ['subgroup1' => 'Foglia A']))->assertSessionHasErrors('subgroup1');
        $this->assertSame('Foglia B', $customer->fresh()->subgroup1);
        $this->put('/clienti/'.$customer->id, array_merge($payload, ['group' => null, 'subgroup' => null, 'subgroup1' => null]))->assertSessionHasNoErrors();
        $this->assertNull($customer->fresh()->group);
        $this->assertNull($customer->fresh()->subgroup);
        $this->assertNull($customer->fresh()->subgroup1);
        $other = $this->structureFor(null);
        $foreign = \App\Models\Customers::forceCreate(['struttura_id' => $other->id, 'name' => 'Riservato altra struttura', 'group' => 'Radice A']);
        $this->get('/clienti/'.$foreign->id.'/modifica')->assertNotFound();
        $this->put('/clienti/'.$foreign->id, $payload)->assertNotFound();
        $this->assertSame('Radice A', \Illuminate\Support\Facades\DB::table('clienti')->where('id', $foreign->id)->value('group'));
    }
}
