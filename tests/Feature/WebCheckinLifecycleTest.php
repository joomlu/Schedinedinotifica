<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use App\Services\WebCheckinLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinLifecycleTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('file')->flush();
        $this->travelTo(Carbon::parse('2026-10-09 12:00:00', 'Europe/Rome'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function richiesta(?int $tenant = null): WebCheckinRichiesta
    {
        $tenant ??= $this->structureFor(null)->id;
        $p = Schedina::forceCreate(['struttura_id' => $tenant, 'circuito' => 'web', 'name' => 'PERSONALE-SINTETICO', 'surname' => 'Ospite', 'arrive' => '2026-10-09', 'departure' => '2026-10-12', 'cant_people' => 1]);

        return WebCheckinRichiesta::create(['struttura_id' => $tenant, 'schedina_id' => $p->id, 'codice' => 'WC'.$p->id, 'numero_prenotazione' => 'SINTETICA', 'email' => 'ospite@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-10-09', 'partenza' => '2026-10-12', 'quantita_persone' => 1, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);
    }

    private function issue(WebCheckinRichiesta $r): WebCheckinRichiesta
    {
        app(WebCheckinLink::class)->issue($r);

        return $r->fresh();
    }

    private function payload(WebCheckinRichiesta $r): array
    {
        return ['numero_prenotazione' => $r->numero_prenotazione, 'email' => $r->email, 'nome_referente' => $r->nome_referente, 'arrivo' => $r->arrivo->toDateString(), 'partenza' => $r->partenza->toDateString(), 'quantita_persone' => 1];
    }

    private function operator(WebCheckinRichiesta $r): void
    {
        $this->actingAs($this->actor('struttura_user', null, $r->struttura_id));
    }

    public static function dates(): array
    {
        return [['2026-03-28', '2026-03-30', 47], ['2026-10-24', '2026-10-26', 49], ['2026-12-31', '2027-01-02', 48], ['2028-02-28', '2028-03-01', 48]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dates')]
    public function test_scadenza_calendario_dst_confine_esclusivo(string $arrival, string $end, int $hours): void
    {
        $this->travelTo(Carbon::parse($arrival.' 00:00', 'Europe/Rome'));
        $r = $this->richiesta();
        $r->update(['arrivo' => $arrival, 'partenza' => $end]);
        $r = $this->issue($r);
        $this->assertSame($end.' 00:00:00', $r->link_expires_at->format('Y-m-d H:i:s'));
        $this->assertSame($hours, (int) (($r->link_expires_at->timestamp - now()->timestamp) / 3600));
        $this->travelTo($r->link_expires_at->copy()->subSecond());
        $this->get('/w/'.$r->short_token)->assertOk();
        $this->travelTo($r->link_expires_at);
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->post('/w/'.$r->short_token)->assertNotFound();
    }

    public function test_emissione_anticipata_senza_cap_e_formato_indipendente(): void
    {
        $r = $this->richiesta();
        $r->update(['arrivo' => '2027-05-01', 'partenza' => '2027-05-05']);
        $r = $this->issue($r);
        $this->assertSame(64, strlen($r->token));
        $this->assertSame(32, strlen($r->short_token));
        $this->assertNotSame(substr($r->token, 0, 32), $r->short_token);
        $this->travelTo(now()->addMonths(3));
        $this->get('/w/'.$r->short_token)->assertOk();
        $this->get('/w/'.$r->codice.'-'.substr($r->token, 0, 8))->assertNotFound();
    }

    public function test_ospite_salva_corregge_senza_estendere_data_operatore(): void
    {
        $r = $this->issue($this->richiesta());
        $expiry = $r->link_expires_at->toDateTimeString();
        foreach (['Prima', 'Correzione'] as $name) {
            $this->post('/checkin/'.$r->token, ['name' => $name, 'surname' => 'Sintetico', 'arrive' => '2027-04-01', 'departure' => '2027-04-02', 'cant_people' => 1])->assertOk();
        }
        $this->assertSame('2026-10-12', $r->fresh()->partenza->toDateString());
        $this->assertSame('2026-10-09', $r->fresh()->arrivo->toDateString());
        $this->assertSame($expiry, $r->fresh()->link_expires_at->toDateTimeString());
        $this->assertSame('Correzione', Schedina::withoutGlobalScopes()->find($r->schedina_id)->name);
    }

    public function test_riprogrammazione_attiva_e_scaduta_senza_riattivazione(): void
    {
        $r = $this->issue($this->richiesta());
        $this->operator($r);
        $data = $this->payload($r);
        $data['arrivo'] = '2026-10-12';
        $data['partenza'] = '2026-10-15';
        $this->put('/web-checkin/'.$r->id, $data)->assertRedirect();
        $this->assertSame('2026-10-14', $r->fresh()->link_expires_at->toDateString());
        $this->travelTo(Carbon::parse('2026-10-14', 'Europe/Rome'));
        $data['arrivo'] = '2026-10-20';
        $data['partenza'] = '2026-10-23';
        $this->put('/web-checkin/'.$r->id, $data)->assertRedirect();
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertRedirect();
        $this->get('/checkin/'.$r->fresh()->token)->assertOk();
        $this->get('/checkin/'.$r->token)->assertNotFound();
    }

    public function test_revoca_rigenerazione_destinatario_tenant_e_csrf(): void
    {
        $r = $this->issue($this->richiesta());
        $this->operator($r);
        $this->post('/web-checkin/'.$r->id.'/revoca', ['_token' => 'errato'])->assertStatus(419);
        $this->get('/w/'.$r->short_token)->assertOk();
        $this->post('/web-checkin/'.$r->id.'/revoca')->assertRedirect();
        $this->get('/w/'.$r->short_token)->assertNotFound();
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertRedirect();
        $new = $r->fresh();
        $this->get('/w/'.$new->short_token)->assertOk();
        $this->get('/w/'.$r->short_token)->assertNotFound();
        $data = $this->payload($new);
        $data['email'] = 'altro@example.invalid';
        $this->put('/web-checkin/'.$r->id, $data)->assertRedirect();
        $this->get('/w/'.$new->short_token)->assertNotFound();
        $b = $this->richiesta();
        $this->post('/web-checkin/'.$b->id.'/rigenera')->assertNotFound();
        $this->post('/web-checkin/'.$b->id.'/revoca')->assertNotFound();
    }

    public function test_cestino_non_riattiva_e_nuova_emissione64(): void
    {
        $r = $this->issue($this->richiesta());
        $old = $r->token;
        $item = app(CestinoService::class)->archiveModel($r);
        $r->delete();
        $restored = app(CestinoService::class)->restoreItem($item);
        $this->assertNotNull($restored->link_revoked_at);
        $this->assertSame(64, strlen($restored->token));
        $this->get('/checkin/'.$old)->assertNotFound();
        $this->get('/checkin/'.$restored->token)->assertNotFound();
        $this->operator($restored);
        $this->post('/web-checkin/'.$restored->id.'/rigenera')->assertRedirect();
        $this->get('/w/'.$restored->fresh()->short_token)->assertOk();
    }

    public function test_convertito_solo_messaggio_tutti_alias_senza_dati(): void
    {
        $r = $this->issue($this->richiesta());
        $r->update(['stato' => 'convertito', 'convertito_at' => now()]);
        $before = DB::table('schedina')->where('id', $r->schedina_id)->first();
        foreach (['/checkin/'.$r->token, '/w/'.$r->short_token, '/w/'.$r->token] as $path) {
            foreach (['GET', 'POST'] as $method) {
                $response = $this->call($method, $path, ['name' => 'ATTACCO']);
                $response->assertOk();
                $this->assertSame('Check-in recibido', $response->getContent());
            } $this->get($path.'/completato')->assertContent('Check-in recibido');
        }
        $this->assertEquals($before, DB::table('schedina')->where('id', $r->schedina_id)->first());
    }

    public function test_legacy64_80_e_short_fino_scadenza_senza_grazia7(): void
    {
        $r = $this->richiesta();
        $r->update(['arrivo' => '2026-11-01', 'partenza' => '2026-11-03']);
        foreach ([64, 80] as $length) {
            $r->update(['token' => str_repeat('L', $length)]);
            $this->travelTo(Carbon::parse('2026-10-25', 'Europe/Rome'));
            $this->get('/checkin/'.$r->token)->assertOk();
            $this->get('/w/'.$r->token)->assertOk();
            if ($length === 64) {
                $this->get('/w/'.$r->codice.'-LLLLLLLL')->assertOk();
            }
        }
        $this->travelTo(Carbon::parse('2026-11-03', 'Europe/Rome'));
        $this->get('/checkin/'.$r->token)->assertNotFound();
    }

    public function test_date_mancanti_incoerenti_non_abilitano_link(): void
    {
        $r = $this->richiesta();
        $r->update(['partenza' => '2026-10-08']);
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->operator($r);
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertStatus(422);
        $data = $this->payload($r);
        $data['arrivo'] = '2026-10-10';
        $data['partenza'] = '2026-10-12';
        $this->put('/web-checkin/'.$r->id, $data)->assertRedirect();
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertRedirect();
        $this->get('/checkin/'.$r->fresh()->token)->assertOk();
    }

    public function test_letture_alias_soglia_finestra_e_wifi_isolamento(): void
    {
        $a = $this->issue($this->richiesta());
        $b = $this->issue($this->richiesta($a->struttura_id));
        $c = $this->issue($this->richiesta());
        foreach ([$a, $b, $c] as $r) {
            $r->update(['stato' => 'convertito']);
        }
        for ($i = 0; $i < 60; $i++) {
            $this->get($i % 2 ? '/w/'.$a->short_token : '/checkin/'.$a->token)->assertOk();
        }
        $this->get('/w/'.$a->short_token)->assertStatus(429)->assertHeader('Retry-After', '60');
        $this->get('/w/'.$b->short_token)->assertOk();
        $this->get('/w/'.$c->short_token)->assertOk();
        $this->travelTo(now()->addSeconds(59));
        $this->get('/w/'.$a->short_token)->assertStatus(429)->assertHeader('Retry-After', '1');
        $this->travelTo(now()->addSecond());
        $this->get('/checkin/'.$a->token)->assertOk();
    }

    public function test_post20_dieci_minuti_alias_senza_scritture_e_get_indipendente(): void
    {
        $r = $this->issue($this->richiesta());
        $r->update(['stato' => 'convertito']);
        for ($i = 0; $i < 20; $i++) {
            $this->post($i % 2 ? '/w/'.$r->short_token : '/checkin/'.$r->token)->assertOk();
        }
        $this->post('/checkin/'.$r->token, ['name' => 'NON-SCRIVERE'])->assertStatus(429)->assertHeader('Retry-After', '600');
        $this->get('/w/'.$r->short_token)->assertOk();
        $this->travelTo(now()->addSeconds(599));
        $this->post('/w/'.$r->short_token)->assertStatus(429)->assertHeader('Retry-After', '1');
        $this->travelTo(now()->addSecond());
        $this->post('/w/'.$r->short_token)->assertOk();
        $this->assertSame('PERSONALE-SINTETICO', Schedina::withoutGlobalScopes()->find($r->schedina_id)->name);
    }

    public function test_ip_generale_prima_lookup300_wifi_condiviso(): void
    {
        $a = $this->issue($this->richiesta());
        $b = $this->issue($this->richiesta());
        $this->get('/w/'.$a->short_token)->assertOk();
        $this->get('/w/'.$b->short_token)->assertOk();
        for ($i = 0; $i < 298; $i++) {
            $this->get('/w/inesistente-'.$i)->assertNotFound();
        }
        $lookups = 0;
        DB::listen(function ($query) use (&$lookups) {
            if (str_contains($query->sql, 'web_checkin_richieste')) {
                $lookups++;
            }
        });
        $this->get('/w/'.$b->short_token)->assertStatus(429)->assertHeader('Retry-After', '60');
        $this->assertSame(0, $lookups);
        $this->travelTo(now()->addMinute());
        $this->get('/w/'.$b->short_token)->assertOk();
    }

    public function test_date_assenti_e_non_calendario_rifiutate_senza_invenzioni(): void
    {
        $r = $this->richiesta();
        foreach ([null, '0000-00-00', '2026-02-30'] as $date) {
            $r->setRawAttributes(array_replace($r->getAttributes(), ['arrivo' => $date]));
            $this->assertNull(app(WebCheckinLink::class)->deadline($r));
            $this->assertFalse(app(WebCheckinLink::class)->valid($r));
        }
    }

    public function test_conversione_operativa_con_cambio_destinatario_revoca_senza_cambiare_arrivo_previsto(): void
    {
        $r = $this->issue($this->richiesta());
        $this->operator($r);
        $payload = ['save_mode' => 'to_arrivi', 'name' => 'Destinatario diverso', 'surname' => 'Ospite', 'arrive' => '2026-10-12', 'departure' => '2026-10-15', 'cant_people' => 1, 'room' => 1, 'beds' => 1];
        $this->put('/schedine/'.$r->schedina_id, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('2026-10-09', $r->fresh()->arrivo->toDateString());
        $this->assertSame('convertito', $r->fresh()->stato);
        $this->get('/checkin/'.$r->token)->assertNotFound();
    }

    public function test_cancellazione_parent_e_restore_non_riattivano_inviti(): void
    {
        $r = $this->issue($this->richiesta());
        $this->operator($r);
        $this->delete('/schedine/'.$r->schedina_id)->assertRedirect();
        $this->get('/w/'.$r->short_token)->assertNotFound();
        $this->assertNotNull($r->fresh()->link_revoked_at);
        $item = \App\Models\CestinoItem::where('entity_class', Schedina::class)->firstOrFail();
        app(CestinoService::class)->restoreItem($item);
        $this->get('/checkin/'.$r->token)->assertNotFound();
    }

    public function test_emissione_passata_non_riattiva_scadenza_e_ruolo_sconosciuto_negato(): void
    {
        $r = $this->richiesta();
        $this->operator($r);
        $data = $this->payload($r);
        $data['arrivo'] = '2026-09-01';
        $data['partenza'] = '2026-09-03';
        $before = DB::table('schedina')->count();
        $this->post('/web-checkin', $data)->assertRedirect();
        $this->assertSame($before + 1, DB::table('schedina')->count());
        $past = WebCheckinRichiesta::orderByDesc('id')->firstOrFail();
        $this->get('/checkin/'.$past->token)->assertNotFound();
        $this->actingAs($this->actor('ruolo_sconosciuto', null, $r->struttura_id));
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertForbidden();
        $this->post('/web-checkin/'.$r->id.'/revoca')->assertForbidden();
    }

    public function test_head_csrf_invalido_e_ip_distinto_rispettano_contatori(): void
    {
        $r = $this->issue($this->richiesta());
        $r->update(['stato' => 'convertito']);
        for ($i = 0; $i < 60; $i++) {
            $this->call('HEAD', '/checkin/'.$r->token)->assertOk();
        }$this->get('/w/'.$r->short_token)->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.55'])->get('/w/'.$r->short_token)->assertOk();
        for ($i = 0; $i < 20; $i++) {
            $this->post('/checkin/'.$r->token, ['_token' => 'errato'])->assertStatus(419);
        }$this->post('/w/'.$r->short_token)->assertStatus(429);
    }

    public function test_cancellazione_contatto_gestionale_revoca_anche_valore_null(): void
    {
        $r = $this->issue($this->richiesta());
        Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->update(['customer_email' => 'precedente@example.invalid']);
        $this->operator($r);
        $this->put('/schedine/'.$r->schedina_id, ['save_mode' => 'to_arrivi', 'name' => 'PERSONALE-SINTETICO', 'surname' => 'Ospite', 'arrive' => '2026-10-09', 'departure' => '2026-10-12', 'cant_people' => 1, 'room' => 1, 'beds' => 1, 'customer_email' => null])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull($r->fresh()->link_revoked_at);
        $this->get('/w/'.$r->short_token)->assertNotFound();
    }

    public function test_rigenerazione_legacy_invalida_breve_anche_con_stesso_prefisso_completo(): void
    {
        $r = $this->richiesta();
        $oldFull = $r->token;
        $oldShort = $r->codice.'-'.substr($oldFull, 0, 8);
        $this->get('/w/'.$oldShort)->assertOk();
        $this->operator($r);
        $this->post('/web-checkin/'.$r->id.'/rigenera')->assertRedirect();
        $r->refresh();
        $r->forceFill(['token' => substr($oldFull, 0, 8).str_repeat('X', 56)])->save();
        $this->get('/w/'.$oldShort)->assertNotFound();
        $this->get('/checkin/'.$oldFull)->assertNotFound();
        $this->get('/w/'.$r->short_token)->assertOk();
        $this->get('/checkin/'.$r->token)->assertOk();
    }
}
