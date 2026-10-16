<?php

namespace Tests\Feature;

use App\Models\Customers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    public function test_elenco_scheda_stampa_e_isolamento_nei_quattro_ruoli(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $other = $this->structureFor(null);
        $customer = Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'PersonaUnica', 'surname' => 'Sintetica', 'type_housed' => 'Richiesta', 'email' => 'persona@example.test', 'azienda' => 'Azienda completa', 'nota' => str_repeat('Nota lunga ', 100)]);
        foreach (range(1, 9) as $i) \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'customer_id' => $customer->id, 'name' => 'Storico', 'surname' => 'Sintetico', 'scheda' => 'SOGGIORNO-'.$i, 'arrive' => '2026-06-01', 'departure' => '2026-06-03']);
        $foreign = Customers::forceCreate(['struttura_id' => $other->id, 'name' => 'ClienteSegreto', 'surname' => 'Esterno']);
        foreach ([$this->actor('super_admin'), $admin, $this->actor('proprietario', $owner->id), $this->actor('struttura_user', null, $s->id)] as $actor) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $s->id]);
            $this->get('/clienti?q=PersonaUnica')->assertOk()->assertSee('PersonaUnica')->assertDontSee('ClienteSegreto');
            $this->get('/clienti?stato=completo&tipo_cliente=Richiesta')->assertOk()->assertSee('PersonaUnica');
            $this->get('/clienti?stato=bozza')->assertOk()->assertDontSee('PersonaUnica');
            $this->get('/clienti/'.$customer->id.'/modifica')->assertOk()->assertSee('Stampa scheda completa');
            $this->get('/clienti/'.$customer->id.'/stampa')->assertOk()->assertSee('Azienda completa')->assertSee('Nota lunga')->assertSee('Classificazione e anagrafica')->assertSee('Storico soggiorni completo')->assertSee('SOGGIORNO-1')->assertSee('SOGGIORNO-9');
            foreach (['modifica', 'stampa', 'storico'] as $action) $this->get('/clienti/'.$foreign->id.'/'.$action)->assertNotFound();
            $this->put('/clienti/'.$foreign->id, ['name' => 'Violazione'])->assertNotFound();
            $this->assertSame('ClienteSegreto', $foreign->fresh()->name);
        }
    }

    public function test_ricerca_ordinamento_paginazione_e_parametri_non_scalari(): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'))->withSession(['struttura_corrente_id' => $s->id]);
        foreach (range(1, 12) as $i) Customers::forceCreate(['struttura_id' => $s->id, 'name' => sprintf('Persona%02d', $i), 'surname' => 'Paginata', 'type_housed' => 'Richiesta', 'cellphone' => '3331234567']);
        $this->get('/clienti?ordine=nome&q=Paginata')->assertOk()->assertSeeInOrder(['Persona01', 'Persona02', 'Persona10'])->assertDontSee('Persona11')->assertSee('page=2', false);
        $this->get('/clienti?ordine=nome&q=Paginata&page=2')->assertOk()->assertSeeInOrder(['Persona11', 'Persona12'])->assertDontSee('Persona01');
        $this->get('/clienti?q=3331234567')->assertOk()->assertSee('Persona12');
        $this->get('/clienti?q=Inesistente')->assertOk()->assertDontSee('Persona12');
        foreach (['q', 'tipo_cliente', 'stato', 'ordine'] as $field) $this->getJson('/clienti?'.$field.'[]=x')->assertUnprocessable();
        $this->getJson('/clienti?ordine=campo_arbitrario')->assertUnprocessable();
    }

    public function test_menu_senza_diagnostica_e_tassa_riservata(): void
    {
        $s = $this->structureFor(null);
        foreach (['super_admin', 'admin', 'proprietario', 'struttura_user'] as $role) {
            $this->actingAs($this->actor($role, null, $s->id))->withSession(['struttura_corrente_id' => $s->id]);
            $html = view('layouts.sidebar')->render();
            $this->assertStringNotContainsString('/qa', $html);
            $response = $this->get('/superadmin/tassa/configurazione');
            if ($role === 'super_admin') $response->assertOk()->assertSee('Contesto territoriale')->assertSee('Verifiche delle fonti')->assertSee('documentazione-tassa');
            else $response->assertForbidden();
        }
    }
}
