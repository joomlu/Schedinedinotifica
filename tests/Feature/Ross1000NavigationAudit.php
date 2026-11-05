<?php
namespace Tests\Feature;

use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class Ross1000NavigationAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_configurazione_e_tabella_per_operatore_sintetico_con_dati_incompleti(): void
    {
        Http::preventStrayRequests();
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $s->update(['nome_struttura' => 'tanggo equivalente sintetica', 'regione' => 'Emilia-Romagna']);
        $user = $this->actor('struttura_user', $owner->id, $s->id);
        $user->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($user);
        $this->get('/struttura')->assertOk()->assertSee('Configurazione Ross1000')->assertSee(route('istat.tabella_a.index'));
        $this->get('/istat-tabella-a')->assertOk()->assertSee('Codice struttura Ross1000');
        $s->update(['camere_disponibili' => 10, 'letti_disponibili' => 20, 'istat_codice_struttura' => 'SINTETICA']);
        $this->get('/istat-tabella-a')->assertOk();
        Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'schedina', 'is_arrive' => false,
            'name' => 'Persona sintetica', 'arrive' => now()->startOfMonth()->toDateString(), 'departure' => now()->endOfMonth()->toDateString()]);
        $this->get('/istat-tabella-a')->assertOk()->assertSee('non valido');
        Http::assertNothingSent();
    }
}
