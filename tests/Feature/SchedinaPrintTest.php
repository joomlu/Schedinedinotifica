<?php

namespace Tests\Feature;

use App\Models\{Schedina, Componenti};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedinaPrintTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    public function test_codice_modifica_salvataggio_e_stampa_completa_isolati(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $this->cataloghiSchedina($a->id);
        $payload = $this->validSchedinaPayload() + ['relationship' => 'OSPITE SINGOLO'];
        $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors();
        $schedina = Schedina::sole();
        $this->get(route('schedina'))->assertOk()
            ->assertSee('aria-label="Modifica schedina '.$schedina->scheda.'"', false)
            ->assertSee(route('schedina.print', $schedina->id), false)
            ->assertSee(route('schedina.edit', $schedina->id), false);
        $this->get(route('schedina.edit', $schedina->id))->assertOk()->assertSee('QATEST');
        $this->put(route('schedina.update', $schedina->id), array_replace($payload, ['observation' => 'Nota modificata sintetica']))->assertSessionHasNoErrors();
        $this->assertSame('Nota modificata sintetica', $schedina->fresh()->observation);
        Componenti::forceCreate(['struttura_id' => $a->id, 'schedina_id' => $schedina->id, 'name' => 'ComponenteSintetico', 'surname' => 'Famiglia', 'comune_nac' => 'Roma', 'city_nac' => 'ITALIANA']);
        $foreign = Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'SegretoAltroHotel', 'circuito' => 'bozza']);
        Componenti::forceCreate(['struttura_id' => $b->id, 'schedina_id' => $schedina->id, 'name' => 'ComponenteEstraneo']);
        $this->get(route('schedina.print', $schedina->id))->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertSeeInOrder(['Ospite titolare', 'QATEST', 'Componenti (1)', 'ComponenteSintetico'])
            ->assertSee('Nota modificata sintetica')->assertSee('A4 portrait')
            ->assertDontSee('ComponenteEstraneo')->assertDontSee('SegretoAltroHotel')->assertDontSee('Stampa ricevuta');
        $this->get(route('schedina.print', $foreign->id))->assertNotFound();
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get(route('schedina.print', $schedina->id))->assertNotFound();
        $this->get(route('schedina.edit', $schedina->id))->assertNotFound();
        $this->put(route('schedina.update', $schedina->id), $payload)->assertNotFound();
    }

    public function test_stampa_bozza_senza_componenti_e_escaping(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $schedina = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => '<script>alert(1)</script>', 'circuito' => 'bozza']);
        $this->get(route('schedina.print', $schedina->id))->assertOk()->assertSee('Nessun componente registrato.')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        auth()->logout();
        $this->get(route('schedina.print', $schedina->id))->assertRedirect(route('login'));
    }
}
