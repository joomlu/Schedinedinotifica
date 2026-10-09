<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class StrutturaSelezioneTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_empty_owner_can_open_selector_without_foreign_structures(): void
    {
        $admin = $this->actor('admin');
        $foreign = $this->structureFor($this->ownerFor($admin));
        $owner = $this->actor('proprietario', $this->ownerFor($admin)->id);
        $this->actingAs($owner)->get('/strutture/seleziona')->assertOk()
            ->assertSee('Nessuna struttura disponibile.')->assertDontSee($foreign->nome_struttura);
    }

    public function test_owner_can_select_and_switch_only_owned_structures(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $first = $this->structureFor($owner);
        $second = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($admin));
        $this->actingAs($this->actor('proprietario', $owner->id));
        $this->get('/strutture/seleziona')->assertOk()->assertSee($first->nome_struttura)
            ->assertSee($second->nome_struttura)->assertDontSee($foreign->nome_struttura);
        foreach ([$second, $first] as $structure) {
            $this->post('/strutture/'.$structure->id.'/seleziona', ['selection_context' => 'topbar_switch'])
                ->assertRedirect(route('root'))->assertSessionHas('struttura_corrente_id', $structure->id);
            $this->get('/strutture/seleziona')->assertOk()->assertViewHas('currentId', $structure->id);
        }
        $this->post('/strutture/'.$foreign->id.'/seleziona')->assertNotFound();
        $this->assertSame($first->id, session('struttura_corrente_id'));
    }

    public function test_single_structure_owner_and_limited_staff_keep_their_access(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $foreign = $this->structureFor(null);
        $this->actingAs($this->actor('proprietario', $owner->id))
            ->get('/strutture/seleziona')->assertOk()->assertSee($mine->nome_struttura)
            ->assertDontSee($foreign->nome_struttura);
        $staff = $this->actor('struttura_user', null, $mine->id);
        $staff->update(['ruolo_operativo' => 'reception']);
        $this->actingAs($staff)->get('/strutture/seleziona')->assertRedirect(route('home'));
        $this->post('/strutture/'.$foreign->id.'/seleziona')->assertNotFound();
        $this->assertSame($mine->id, session('struttura_corrente_id'));
    }
}
