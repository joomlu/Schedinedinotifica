<?php

namespace Tests\Feature;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Support\PublicSiteCatalog;
use App\Support\PublicSiteRenderer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class PublicSiteCompletionTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    public function test_sei_pagine_condividono_versione_link_nominati_e_asset_autenticati(): void
    {
        $version = app(PublicSiteRenderer::class)->version();
        foreach (PublicSiteRenderer::PAGES as $page => $name) {
            $path = route($name, [], false);
            $response = $this->get($path)->assertOk()->assertHeader('X-Sito-Versione', $version);
            $html = $response->getContent();
            $this->assertStringContainsString('name="sito-versione" content="'.$version.'"', $html);
            $this->assertStringContainsString('href="'.route('site.ampliamenti', [], false).'"', $html);
            foreach (['app.js', 'styles.css'] as $asset) {
                [$stem, $extension] = explode('.', $asset);
                $hash = substr(hash_file('sha256', base_path('web/assets/'.$asset)), 0, 16);
                $versioned = '/site-assets/'.$stem.'.'.$hash.'.'.$extension;
                $this->assertStringContainsString($versioned, $html);
                $this->assertStringNotContainsString('/site-assets/'.$asset.'"', $html);
                $assetResponse = $this->get($versioned)->assertOk();
                $this->assertStringContainsString('immutable', $assetResponse->headers->get('Cache-Control'));
                $this->assertSame([], $assetResponse->headers->getCookies());
            }
        }
        $this->get('/moduli#stato-funzionalita')->assertOk()->assertSee('id="stato-funzionalita"', false);
        $this->get('/ampliamenti')->assertOk()->assertSee('Web Check-in')->assertSee('Max Full');
        $this->get('/site-assets/app.0000000000000000.js')->assertNotFound();
        $this->get('/site-assets/styles.0000000000000000.css')->assertNotFound();
        $this->get('/site-assets/app.0000000000000000.css')->assertNotFound();
        $this->actingAs($this->actor('struttura_user'))->get($versioned)->assertOk();
    }

    public function test_interessi_canonici_salvati_nel_lead_e_nell_attivita(): void
    {
        $catalog = app(PublicSiteCatalog::class);
        $visitor = 100;
        foreach ($catalog->interests() as $code => $label) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.'.(++$visitor)]);
            $payload = $this->payload(['interest' => $code, 'email' => $code.'@example.invalid']);
            $response = $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', false);
            $lead = CrmLead::findOrFail($response->json('lead_id'));
            $this->assertStringContainsString('Interesse richiesto: '.$label.' ['.$code.']', $lead->messaggio);
            $this->assertSame($lead->messaggio, $lead->activities()->sole()->descrizione);
        }
    }

    public function test_consenso_e_interesse_non_validi_non_scrivono_nel_CRM(): void
    {
        $n = CrmLead::count();
        $a = CrmLeadActivity::count();
        foreach ([['consent' => false], ['consent' => null], ['interest' => 'servizio-inventato']] as $change) {
            $this->postJson('/contatti/richiesta', $this->payload($change))->assertUnprocessable();
        }
        $this->assertSame($n, CrmLead::count());
        $this->assertSame($a, CrmLeadActivity::count());
    }

    public function test_retry_identico_non_duplica_richieste_o_attivita_ma_nuovo_messaggio_si(): void
    {
        $payload = $this->payload(['plan' => 'strutture', 'max_full' => true, 'interest' => 'istat-ross1000']);
        $n = CrmLead::count();
        $a = CrmLeadActivity::count();
        $first = $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', false);
        $retry = $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame($first->json('lead_id'), $retry->json('lead_id'));
        $this->assertSame($n + 1, CrmLead::count());
        $this->assertSame($a + 1, CrmLeadActivity::count());
        $this->postJson('/contatti/richiesta', $this->payload(['message' => 'Nuova esigenza distinta.']))->assertOk()->assertJsonPath('duplicate', false);
        $this->assertSame($n + 2, CrmLead::count());
    }

    public function test_richiesta_identica_dopo_la_finestra_puo_essere_rinnovata(): void
    {
        $first = $this->postJson('/contatti/richiesta', $this->payload())->assertOk();
        CrmLead::findOrFail($first->json('lead_id'))->forceFill(['created_at' => now()->subMinutes(11)])->save();
        $second = $this->postJson('/contatti/richiesta', $this->payload())->assertOk()->assertJsonPath('duplicate', false);
        $this->assertNotSame($first->json('lead_id'), $second->json('lead_id'));
    }

    public function test_errore_attivita_annulla_anche_il_lead_senza_conferma(): void
    {
        $n = CrmLead::count();
        $a = CrmLeadActivity::count();
        $dispatcher = CrmLeadActivity::getEventDispatcher();
        $temporary = clone $dispatcher;
        CrmLeadActivity::setEventDispatcher($temporary);
        $temporary->listen('eloquent.creating: '.CrmLeadActivity::class, fn () => throw new \RuntimeException('Errore sintetico attivita'));
        $this->withoutExceptionHandling();
        try {
            try {
                $this->postJson('/contatti/richiesta', $this->payload());
                $this->fail('Errore atteso non sollevato');
            } catch (\RuntimeException $e) {
                $this->assertSame('Errore sintetico attivita', $e->getMessage());
            }
        } finally {
            CrmLeadActivity::setEventDispatcher($dispatcher);
        }
        $this->assertSame($n, CrmLead::count());
        $this->assertSame($a, CrmLeadActivity::count());
    }

    public function test_backoffice_mostra_interesse_al_superadmin_e_nega_accesso_pubblico_o_admin(): void
    {
        $id = $this->postJson('/contatti/richiesta', $this->payload(['interest' => 'tassa-soggiorno']))->assertOk()->json('lead_id');
        $url = route('superadmin.crm.show', ['id' => $id], false);
        $this->get($url)->assertRedirect('/login');
        $this->actingAs($this->actor('super_admin'))->get($url)->assertOk()->assertSee('Tassa di soggiorno');
        $this->actingAs($this->actor('admin'))->get($url)->assertForbidden();
    }

    public function test_form_nativo_usa_POST_e_conferma_solo_dopo_persistenza(): void
    {
        $html = $this->get('/contatti')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<form[^>]*action="\/contatti\/richiesta"[^>]*method="post"/i', $html);
        $this->assertStringNotContainsString('{{contact.feedback}}', $html);
        $payload = $this->payload(['consent' => '1', 'plan' => 'strutture', 'max_full' => '1', 'interest' => 'web-checkin']);
        $this->post('/contatti/richiesta', $payload)->assertRedirect('/contatti')->assertStatus(303)->assertSessionHas('sito-richiesta-registrata', true);
        $lead = CrmLead::where('email', $payload['email'])->sole();
        $this->assertStringContainsString('Servizio aggiuntivo richiesto: Max Full', $lead->messaggio);
        $this->assertStringContainsString('Interesse richiesto: Web Check-in [web-checkin]', $lead->messaggio);
        $this->get('/contatti')->assertOk()->assertSee('Richiesta registrata. Grazie per averci contattato.');
    }

    public function test_errori_nativi_conservano_dati_e_selezioni_senza_iniettare_HTML(): void
    {
        $payload = $this->payload(['email' => 'non-email', 'company' => 'Hotel "<Sintetico>', 'plan' => 'strutture', 'max_full' => '1', 'interest' => 'web-checkin', 'message' => '</textarea><script>iniezione_sintetica</script>']);
        $n = CrmLead::count();
        $this->from('/contatti')->post('/contatti/richiesta', $payload)->assertRedirect('/contatti')->assertSessionHasErrors('email');
        $html = $this->get('/contatti')->assertOk()->getContent();
        $this->assertSame($n, CrmLead::count());
        $this->assertStringContainsString('data-contact-restored="true"', $html);
        $this->assertStringContainsString('value="Hotel &quot;&lt;Sintetico&gt;"', $html);
        $this->assertStringContainsString('value="strutture" selected', $html);
        $this->assertStringContainsString('value="web-checkin" selected', $html);
        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;iniezione_sintetica&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>iniezione_sintetica</script>', $html);
        $this->assertStringContainsString('La richiesta non è stata registrata.', $html);
        $this->assertStringNotContainsString('Richiesta registrata. Grazie', $html);
    }

    private function payload(array $change = []): array
    {
        return array_replace(['company' => 'Hotel Collaudo', 'name' => 'Referente Sintetico', 'email' => 'completamento@example.invalid', 'topic' => 'Richiesta demo', 'message' => 'Richiesta sintetica isolata.', 'website' => '', 'consent' => true], $change);
    }
}
