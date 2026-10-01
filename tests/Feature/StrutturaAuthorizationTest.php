<?php

namespace Tests\Feature;

use App\Models\Struttura;
use App\Support\StrutturaAccess;
use App\Support\StrutturaCorrente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

/** Locked by tests/bootstrap.php until disposable infrastructure is accepted. */
class StrutturaAuthorizationTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_owner_without_structures_cannot_read_update_or_get_suggestions(): void
    {
        $admin = $this->actor('admin');
        $other = $this->structureFor($this->ownerFor($admin));
        $owner = $this->actor('proprietario', $this->ownerFor($admin)->id);
        $before = $other->getRawOriginal();
        $this->actingAs($owner)->get('/struttura')->assertForbidden();
        $this->get('/struttura/zone-suggestions')->assertForbidden();
        $this->put('/struttura', $this->validStructurePayload($other))->assertForbidden();
        $this->assertSame($before, $other->fresh()->getRawOriginal());
    }

    public function test_owner_without_owner_id_cannot_receive_legacy_structures(): void
    {
        $legacy = $this->structureFor(null);
        $owner = $this->actor('proprietario');
        $this->actingAs($owner)->get('/struttura')->assertForbidden();
        $this->post('/strutture/'.$legacy->id.'/seleziona')->assertNotFound();
        $this->assertSame(0, StrutturaAccess::query($owner)->count());
    }

    public function test_owner_cannot_target_another_owner_using_either_query_parameter(): void
    {
        $admin = $this->actor('admin');
        $mineOwner = $this->ownerFor($admin);
        $mine = $this->structureFor($mineOwner);
        $other = $this->structureFor($this->ownerFor($admin));
        $beforeMine = $mine->getRawOriginal();
        $beforeOther = $other->getRawOriginal();
        $this->actingAs($this->actor('proprietario', $mineOwner->id));
        foreach (['sid', 'struttura_id'] as $key) {
            $suffix = '?'.$key.'='.$other->id;
            $this->get('/struttura'.$suffix)->assertForbidden();
            $this->get('/struttura/zone-suggestions'.$suffix)->assertForbidden();
            $this->put('/struttura'.$suffix, $this->validStructurePayload($mine))->assertForbidden();
        }
        $this->assertSame($beforeMine, $mine->fresh()->getRawOriginal());
        $this->assertSame($beforeOther, $other->fresh()->getRawOriginal());
    }

    public function test_forged_session_does_not_silently_update_own_structure(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $mine = $this->structureFor($owner);
        $other = $this->structureFor($this->ownerFor($admin));
        $before = $mine->getRawOriginal();
        $this->actingAs($this->actor('proprietario', $owner->id))
            ->withSession(['struttura_corrente_id' => $other->id])
            ->put('/struttura', $this->validStructurePayload($mine))->assertForbidden();
        $this->assertSame($before, $mine->fresh()->getRawOriginal());
    }

    public function test_legitimate_owner_can_read_and_update(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $this->actingAs($this->actor('proprietario', $owner->id));
        $this->get('/struttura')->assertOk();
        $this->get('/struttura/zone-suggestions')->assertOk();
        $this->put('/struttura', $this->validStructurePayload($mine))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Hotel Aggiornato', $mine->fresh()->nome_struttura);
    }

    public function test_admin_operational_legacy_exception_does_not_expand_selector_or_crud(): void
    {
        $admin = $this->actor('admin');
        $mine = $this->structureFor($this->ownerFor($admin));
        $other = $this->structureFor($this->ownerFor($this->actor('admin')));
        $legacy = $this->structureFor(null);
        $this->actingAs($admin);
        $this->get('/struttura?sid='.$mine->id)->assertOk();
        $this->get('/struttura?sid='.$other->id)->assertForbidden();
        $this->put('/struttura?sid='.$other->id, $this->validStructurePayload($other))->assertForbidden();
        $this->get('/struttura?sid='.$legacy->id)->assertOk();
        $this->post('/strutture/'.$legacy->id.'/seleziona')->assertNotFound();
        $this->get('/admin/strutture/'.$legacy->id.'/edit')->assertNotFound();
        $this->get('/admin/strutture/'.$other->id.'/edit')->assertNotFound();
    }

    public function test_admin_gestisci_flow_redirects_to_dashboard_and_sets_current_structure(): void
    {
        $admin = $this->actor('admin');
        $mine = $this->structureFor($this->ownerFor($admin));

        $this->actingAs($admin)
            ->post('/strutture/'.$mine->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertRedirect(route('root'));

        $this->assertSame($mine->id, session('struttura_corrente_id'));
    }

    public function test_admin_gestisci_flow_cannot_select_other_admin_structure(): void
    {
        $adminA = $this->actor('admin');
        $adminB = $this->actor('admin');
        $foreign = $this->structureFor($this->ownerFor($adminB));

        $this->actingAs($adminA)
            ->post('/strutture/'.$foreign->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertNotFound();

        $this->assertNull(session('struttura_corrente_id'));
    }

    public function test_admin_topbar_switch_flow_redirects_to_dashboard_and_sets_current_structure(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $first = $this->structureFor($owner);
        $second = $this->structureFor($owner);

        $this->actingAs($admin)
            ->post('/strutture/'.$second->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
            ])
            ->assertRedirect(route('root'));

        $this->assertSame($second->id, session('struttura_corrente_id'));
        $this->assertNotSame($first->id, session('struttura_corrente_id'));
    }

    public function test_topbar_switch_does_not_allow_arbitrary_redirect_targets(): void
    {
        $admin = $this->actor('admin');
        $structure = $this->structureFor($this->ownerFor($admin));

        $this->actingAs($admin)
            ->post('/strutture/'.$structure->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
                'return_url' => 'https://evil.example/path',
                'next' => '/admin/strutture',
                'destination' => '/qualunque',
            ])
            ->assertRedirect(route('root'));
    }

    public function test_selection_context_manipulation_does_not_expand_authorization(): void
    {
        $adminA = $this->actor('admin');
        $adminB = $this->actor('admin');
        $foreign = $this->structureFor($this->ownerFor($adminB));

        $this->actingAs($adminA)
            ->post('/strutture/'.$foreign->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
            ])
            ->assertNotFound();

        $this->actingAs($adminA)
            ->post('/strutture/'.$foreign->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertNotFound();
    }

    public function test_super_admin_retains_global_access(): void
    {
        $structure = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($this->actor('super_admin'));
        $this->get('/struttura?sid='.$structure->id)->assertOk();
        $this->put('/struttura?sid='.$structure->id, $this->validStructurePayload($structure))
            ->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_structure_user_only_reaches_its_structure(): void
    {
        $mine = $this->structureFor(null);
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $mine->id));
        $this->get('/struttura')->assertOk();
        $this->get('/struttura?sid='.$other->id)->assertForbidden();
        $this->put('/struttura?sid='.$other->id, $this->validStructurePayload($other))->assertForbidden();
    }

    public function test_non_admin_roles_keep_existing_selection_redirect_behavior(): void
    {
        $admin = $this->actor('admin');

        $owner = $this->ownerFor($admin);
        $ownerStructure = $this->structureFor($owner);
        $this->actingAs($this->actor('proprietario', $owner->id))
            ->from('/strutture/seleziona')
            ->post('/strutture/'.$ownerStructure->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertRedirect('/strutture/seleziona');

        $superStructure = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($this->actor('super_admin'))
            ->from('/strutture/seleziona')
            ->post('/strutture/'.$superStructure->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertRedirect('/strutture/seleziona');

        $userStructure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $userStructure->id))
            ->from('/strutture/seleziona')
            ->post('/strutture/'.$userStructure->id.'/seleziona', [
                'selection_context' => 'admin_strutture_gestisci',
            ])
            ->assertRedirect('/strutture/seleziona');
    }

    public function test_topbar_shows_only_authorized_structures_for_admin(): void
    {
        $adminA = $this->actor('admin');
        $adminBOwner = $this->ownerFor($this->actor('admin'));
        $ownerA = $this->ownerFor($adminA);
        $mine = $this->structureFor($ownerA);
        $mineSecond = $this->structureFor($ownerA);
        $foreign = $this->structureFor($adminBOwner);

        $response = $this->actingAs($adminA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($mine->nome_struttura);
        $response->assertSee($mineSecond->nome_struttura);
        $response->assertDontSee($foreign->nome_struttura);
    }

    public function test_topbar_shows_single_structure_without_extra_options(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $single = $this->structureFor($owner);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Struttura: '.$single->nome_struttura);
        $response->assertDontSee('selection_context" value="topbar_switch"', false);
    }

    public function test_topbar_shows_safe_state_when_no_structure_is_selectable(): void
    {
        $admin = $this->actor('admin');

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Nessuna struttura autorizzata');
    }

    public function test_topbar_does_not_expand_allowed_list_for_legacy_current_structure(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $mine = $this->structureFor($owner);
        $legacy = $this->structureFor(null);

        $response = $this->actingAs($admin)
            ->withSession(['struttura_corrente_id' => $legacy->id])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Struttura: '.$legacy->nome_struttura);
        $response->assertSee($mine->nome_struttura);
        $response->assertDontSee('/strutture/'.$legacy->id.'/seleziona');
    }

    public function test_topbar_switch_keeps_existing_behavior_for_proprietario_struttura_user_and_super_admin(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $ownerStructure = $this->structureFor($owner);

        $this->actingAs($this->actor('proprietario', $owner->id))
            ->post('/strutture/'.$ownerStructure->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
            ])
            ->assertRedirect(route('root'));

        $superStructure = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($this->actor('super_admin'))
            ->post('/strutture/'.$superStructure->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
            ])
            ->assertRedirect(route('root'));

        $userStructure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $userStructure->id))
            ->post('/strutture/'.$userStructure->id.'/seleziona', [
                'selection_context' => 'topbar_switch',
            ])
            ->assertRedirect(route('root'));
    }

    public function test_invalid_or_conflicting_selection_is_rejected(): void
    {
        $mine = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'));
        foreach (['sid[]=1', 'sid=0', 'sid=-1', 'sid=abc', 'sid=999999999999999999999999',
                  'sid='.$mine->id.'&struttura_id='.($mine->id + 1)] as $query) {
            $this->get('/struttura?'.$query)->assertForbidden();
        }
    }

    public function test_unknown_role_is_denied(): void
    {
        $this->structureFor(null);
        $this->actingAs($this->actor('unknown'))->get('/struttura')->assertForbidden();
    }

    public function test_memory_does_not_cross_user_contexts(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $emptyOwner = $this->ownerFor($this->actor('admin'));
        $this->actingAs($this->actor('proprietario', $owner->id))->get('/struttura')->assertOk();
        $this->assertNull(StrutturaCorrente::getId());
        // Simulate leaked static state: middleware must clear it before the next request.
        StrutturaCorrente::setId($mine->id);
        $this->actingAs($this->actor('proprietario', $emptyOwner->id))->get('/struttura')->assertForbidden();
        $this->assertNull(StrutturaCorrente::getId());
    }
}
