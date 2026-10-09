<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalWebConversionAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_conversione_operativa_blocca_link_pubblico_e_conserva_prima_data(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'CONVERSIONE-SINTETICA', 'surname' => 'Audit', 'circuito' => 'web']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'WCAUDIT', 'numero_prenotazione' => 'EFFIMERA', 'nome_referente' => 'Sintetico', 'email' => 'converti@example.invalid', 'arrivo' => now()->toDateString(), 'partenza' => now()->addDay()->toDateString(), 'quantita_persone' => 1, 'token' => str_repeat('v', 64), 'stato' => 'in_compilazione']);
        $payload = ['save_mode' => 'to_arrivi', 'name' => 'CONVERSIONE-SINTETICA', 'surname' => 'Audit', 'arrive' => now()->toDateString(), 'departure' => now()->addDay()->toDateString(), 'cant_people' => 1, 'room' => 1, 'beds' => 1];
        $this->actingAs($u)->put('/schedine/'.$p->id, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('arrivi', $p->fresh()->circuito);
        $this->assertSame('convertito', $r->fresh()->stato);
        $this->assertNotNull($r->fresh()->convertito_at);
        $date = $r->fresh()->getRawOriginal('convertito_at');
        $this->travel(1)->hours();
        $this->put('/schedine/'.$p->id, $payload)->assertRedirect();
        $this->assertSame($date, $r->fresh()->getRawOriginal('convertito_at'));
        $this->get('/logout');
        $this->get('/checkin/'.$r->token)->assertOk()->assertDontSee('id="schedina-form"', false);
        $this->post('/checkin/'.$r->token, ['name' => 'NON-MODIFICARE', 'save_mode' => 'web'])->assertOk();
        $this->assertSame('CONVERSIONE-SINTETICA', $p->fresh()->name);
        $this->assertSame('arrivi', $p->fresh()->circuito);
        $this->assertSame($date, $r->fresh()->getRawOriginal('convertito_at'));
    }
}
