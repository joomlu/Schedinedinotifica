<?php

namespace Tests\Feature;

use App\Http\Controllers\WebCheckinController;
use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinShortLinkTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function richiesta(?int $tenant = null, ?int $parentTenant = null, string $stato = 'convertito'): WebCheckinRichiesta
    {
        $tenant ??= $this->structureFor(null)->id;
        $parent = Schedina::forceCreate(['struttura_id' => $parentTenant ?? $tenant, 'circuito' => 'web', 'name' => 'SINTETICO-TENANT', 'surname' => 'Link', 'arrive' => '2026-06-01', 'departure' => '2026-06-02', 'cant_people' => 1]);

        return WebCheckinRichiesta::create(['struttura_id' => $tenant, 'schedina_id' => $parent->id, 'codice' => 'WC'.($parent->id), 'numero_prenotazione' => 'SINTETICA', 'email' => 'synthetic@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-01', 'partenza' => '2026-06-02', 'quantita_persone' => 1, 'token' => bin2hex(random_bytes(32)), 'stato' => $stato]);
    }

    private function link(WebCheckinRichiesta $r): string
    {
        return (new \ReflectionMethod(WebCheckinController::class, 'publicUrl'))->invoke(app(WebCheckinController::class), $r);
    }

    private function stato(): array
    {
        $result = [];
        foreach (['web_checkin_richieste', 'schedina', 'componenti', 'schedina_camere', 'clienti'] as $table) {
            $result[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        return $result;
    }

    public function test_link_generato_invito_e_token_completo_preservato(): void
    {
        $r = $this->richiesta();
        $before = $this->stato();
        $this->get($this->link($r))->assertOk()->assertSee('Apri il tuo Web Check-in')->assertSee('/checkin/'.$r->token);
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('SINTETICO-TENANT');
        $this->get('/w/'.$r->token)->assertOk()->assertSee('SINTETICO-TENANT');
        $this->assertSame($before, $this->stato());
    }

    public function test_salvataggio_breve_autorizzato_e_csrf_preservato(): void
    {
        $r = $this->richiesta(stato: 'da_inviare');
        $before = $this->stato();
        $this->post($this->link($r), ['_token' => 'ERRATO'])->assertStatus(419);
        $this->assertSame($before, $this->stato());
        $data = ['name' => 'Sintetico aggiornato', 'surname' => 'Link', 'arrive' => '2026-06-01', 'departure' => '2026-06-02', 'cant_people' => 1, 'customer_email' => 'synthetic@example.invalid'];
        $this->post($this->link($r), $data)->assertOk()->assertSee('Sintetico aggiornato');
        $this->assertSame('Sintetico aggiornato', Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->name);
        $this->assertSame($r->struttura_id, (int) Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->struttura_id);
        $this->assertSame('in_compilazione', $r->fresh()->stato);
        $this->get($this->link($r).'/completato')->assertOk();
        $data['name'] = 'Sintetico completo';
        $this->post('/checkin/'.$r->token, $data)->assertOk()->assertSee('Sintetico completo');
    }

    public function test_token_completo_legacy_con_formato_diverso_restato_compatibile(): void
    {
        $r = $this->richiesta();
        foreach (['Legacy'.str_repeat('x', 75), 'legacy-token-con-separatori', 'WC1-aaaaaaaa'] as $token) {
            $r->update(['token' => $token]);
            $this->get('/checkin/'.$token)->assertOk()->assertSee('SINTETICO-TENANT');
            $this->get('/w/'.$token)->assertOk()->assertSee('SINTETICO-TENANT');
            $this->post('/w/'.$token)->assertOk();
            $this->get('/w/'.$token.'/completato')->assertOk();
        }
    }

    public static function operazioni(): array
    {
        return [['GET', ''], ['POST', ''], ['GET', '/completato']];
    }

    #[DataProvider('operazioni')]
    public function test_convertito_restato_leggibile_e_non_modificabile(string $method, string $suffix): void
    {
        $r = $this->richiesta();
        $before = $this->stato();
        $this->call($method, $this->link($r).$suffix, ['name' => 'MODIFICA-NON-CONSENTITA'])->assertOk();
        $this->assertSame($before, $this->stato());
    }

    #[DataProvider('operazioni')]
    public function test_parent_estero_bloccato_senza_esposizione_o_scritture(string $method, string $suffix): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $r = $this->richiesta($a->id, $b->id);
        $before = $this->stato();
        $this->call($method, $this->link($r).$suffix, ['name' => 'NON-SCRIVERE'])->assertNotFound()->assertDontSee('SINTETICO-TENANT');
        $this->assertSame($before, $this->stato());
    }

    public static function invalidi(): array
    {
        return [['inesistente', 'MISSING-aaaaaaaa'], ['prefisso corto', 'WC1-a'], ['prefisso lungo', 'WC1-aaaaaaaaa'], ['wildcard percento', 'WC1-%'], ['wildcard underscore', 'WC1-________'], ['separatore extra', 'WC1-aaaaaaaa-x'], ['token inesistente', str_repeat('z', 64)], ['solo codice', 'WC1']];
    }

    #[DataProvider('invalidi')]
    public function test_accesso_non_valido_fallisce_chiuso(string $label, string $access): void
    {
        $r = $this->richiesta();
        $r->update(['codice' => 'WC1', 'token' => str_repeat('a', 64)]);
        $before = $this->stato();
        foreach (['GET', 'POST'] as $method) {
            $this->call($method, '/w/'.rawurlencode($access))->assertNotFound()->assertDontSee('SINTETICO-TENANT');
        }
        $this->assertSame($before, $this->stato());
    }

    public function test_revoca_rotazione_e_cancellazione_invalidano_vecchi_link(): void
    {
        $r = $this->richiesta();
        $old = $this->link($r);
        $oldFull = $r->token;
        $r->update(['token' => str_repeat('Z', 64)]);
        $this->get($old)->assertNotFound();
        $this->post($old)->assertNotFound();
        $this->get('/checkin/'.$oldFull)->assertNotFound();
        $new = $this->link($r);
        $this->get($new)->assertOk();
        $r->delete();
        $this->get($new)->assertNotFound();
        $this->post($new)->assertNotFound();
        $this->get('/checkin/'.str_repeat('Z', 64))->assertNotFound();
    }

    public function test_prefisso_case_sensitive_e_token_corto_non_ammettono_invito(): void
    {
        $r = $this->richiesta();
        $r->update(['token' => str_repeat('A', 64)]);
        $this->get('/w/'.$r->codice.'-aaaaaaaa')->assertNotFound();
        $r->update(['token' => 'AAAAAAAA']);
        $this->get($this->link($r))->assertNotFound();
    }

    public function test_due_tenant_non_si_scambiano_codice_e_prefisso(): void
    {
        $a = $this->richiesta();
        $b = $this->richiesta();
        $a->update(['token' => str_repeat('a', 64)]);
        $b->update(['token' => str_repeat('b', 64)]);
        $this->get('/w/'.$a->codice.'-bbbbbbbb')->assertNotFound();
        $this->get($this->link($b))->assertOk()->assertSee('/checkin/'.$b->token)->assertDontSee('/checkin/'.$a->token);
        $this->actingAs($this->actor('struttura_user', null, $a->struttura_id))->get('/web-checkin/'.$b->id.'/modifica')->assertNotFound();
    }

    public function test_date_soggiorno_non_costituiscono_scadenza_implicita_del_token(): void
    {
        $this->travelTo(now()->setDate(2030, 1, 1));
        $r = $this->richiesta();
        $this->get($this->link($r))->assertOk();
        $this->get('/checkin/'.$r->token)->assertOk();
        $this->travelBack();
    }

    public function test_parent_inesistente_non_viene_ricreato(): void
    {
        $r = $this->richiesta();
        $r->update(['schedina_id' => 999999]);
        $before = $this->stato();
        $this->get($this->link($r))->assertNotFound();
        $this->post($this->link($r))->assertNotFound();
        $this->assertSame($before, $this->stato());
    }
}
