<?php

namespace Tests\Feature;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Support\PublicSiteCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicSiteRenewalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pagine_pubbliche_renderizzano_catalogo_e_marchio_senza_offerte_superate(): void
    {
        foreach (['/', '/moduli', '/ampliamenti', '/demo', '/prezzi', '/contatti'] as $url) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();
            $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
            $this->assertStringContainsString('Tango Soft Pro', $html);
            $this->assertStringContainsString('Schedine di Notifica', $html);
            $this->assertStringContainsString('href="/login"', $html);
            $this->assertStringContainsString('aria-current="page"', $html);
            $this->assertStringNotContainsString('{{catalog.', $html);
            foreach (['7 giorni', 'prova gratuita', '400 €', '60 camere', 'Strutture Plus', 'tangoschedine.com', 'fonts.googleapis.com'] as $obsolete) {
                $this->assertStringNotContainsString($obsolete, $html);
            }
        }
        $home = preg_replace('/\s+/u', ' ', strip_tags(preg_replace('/<br\s*\/?\s*>/i', ' ', $this->get('/')->getContent())));
        $this->assertStringContainsString('Meno burocrazia. Più tempo per i tuoi ospiti.', $home);
        $this->assertStringContainsString('Schedine di Notifica è il software per reception, gestori di appartamenti e strutture ricettive: organizza gli arrivi, raccogli i dati degli ospiti e segui gli adempimenti nel contesto della tua struttura.', $home);
        $this->assertStringContainsString('Richiedi una demo', $home);
        $this->assertStringContainsString('Scopri le funzioni', $home);
    }

    public function test_url_storiche_e_accessi_privati_restano_preservati(): void
    {
        foreach (['/index.html' => '/', '/moduli.html' => '/moduli', '/ampliamenti.html' => '/ampliamenti', '/demo.html' => '/demo', '/prezzi.html' => '/prezzi', '/contatti.html' => '/contatti', '/accesso' => '/login', '/accesso.html' => '/login'] as $old => $new) {
            $this->get($old)->assertRedirect($new);
        }
        $this->get('/login')->assertOk()->assertSee('name="password"', false);
        $this->get('/register')->assertOk()->assertSee('name="email"', false);
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/schedine')->assertRedirect('/login');
        $users = \App\Models\User::count();
        $this->postJson('/register', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'avatar']);
        $this->assertSame($users, \App\Models\User::count());
    }

    public function test_asset_e_catalogo_pubblico_non_escono_dal_perimetro(): void
    {
        foreach (['styles.css', 'app.js'] as $asset) {
            $this->assertSame(hash_file('sha256', base_path('web/assets/'.$asset)), hash_file('sha256', public_path('site-assets/'.$asset)));
        }
        foreach (['styles.css', 'app.js', 'img/prodotto-arrivi.webp', 'catalogo-commerciale.json'] as $asset) {
            $this->get('/site-assets/'.$asset)->assertOk();
        }
        $this->assertStringContainsString('no-cache', $this->get('/site-assets/catalogo-commerciale.json')->headers->get('Cache-Control'));
        $this->get('/site-assets/../index.html')->assertNotFound();
        $this->get('/site-assets/mancante.png')->assertNotFound();
    }

    public function test_tariffe_ufficiali_centralizzate_distinguono_appartamenti_e_strutture(): void
    {
        $catalog = app(PublicSiteCatalog::class);
        $expected = ['solo' => ['1 appartamento', '50 € + IVA / anno'], 'duo' => ['2 appartamenti', '100 € + IVA / anno'], 'multi' => ['Da 3 a 7 appartamenti', '200 € + IVA / anno'], 'oltre7' => ['Oltre 7 appartamenti', 'Preventivo personalizzato'], 'strutture' => ['1 o 2 strutture ricettive', '320 € + IVA / anno complessivi'], 'oltre2' => ['Più di 2 strutture ricettive', 'Preventivo personalizzato']];
        $this->assertSame(array_keys($expected), array_keys($catalog->plans()));
        foreach ($expected as $code => [$scope, $price]) {
            $this->assertSame($scope, $catalog->plans()[$code]['scope']);
            $this->assertSame($price, $catalog->plans()[$code]['price']);
            $rendered = $catalog->render('{{catalog.'.$code.'.scope}} | {{catalog.'.$code.'.price}}');
            $this->assertSame(htmlspecialchars($scope, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').' | '.htmlspecialchars($price, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $rendered);
        }
        foreach (['/', '/prezzi'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('50 € + IVA / anno', $html);
            $this->assertStringContainsString('320 € + IVA / anno complessivi', $html);
            $this->assertStringContainsString($catalog->data()['annual_note'], $html);
            $this->assertStringContainsString($catalog->data()['extras_note'], $html);
        }
    }

    public function test_riferimento_sconosciuto_al_catalogo_non_viene_pubblicato_come_segnaposto(): void
    {
        $this->expectException(\LogicException::class);
        app(PublicSiteCatalog::class)->render('{{catalog.piano_inesistente.price}}');
    }

    public function test_richiesta_generica_compatibile_senza_alterare_messaggio_o_creare_account(): void
    {
        $users = \App\Models\User::count();
        $structures = \App\Models\Struttura::withoutGlobalScopes()->count();
        $this->postJson('/contatti/richiesta', $this->payload())->assertOk()->assertJsonPath('ok', true);
        $lead = CrmLead::where('email', 'sito@example.invalid')->sole();
        $this->assertSame('Richiesta demo', $lead->modalita_contatto);
        $this->assertSame('Messaggio sintetico per il collaudo.', $lead->messaggio);
        $this->assertSame($users, \App\Models\User::count());
        $this->assertSame($structures, \App\Models\Struttura::withoutGlobalScopes()->count());
    }

    public function test_piano_e_max_full_sono_conservati_nel_CRM_con_tariffa_server_e_nessun_acquisto(): void
    {
        $users = \App\Models\User::count();
        $structures = \App\Models\Struttura::withoutGlobalScopes()->count();
        $this->postJson('/contatti/richiesta', $this->payload() + ['plan' => 'strutture', 'max_full' => true, 'price' => '1 €', 'amount' => 1])->assertOk();
        $lead = CrmLead::where('email', 'sito@example.invalid')->sole();
        $this->assertSame('Richiesta demo', $lead->modalita_contatto);
        foreach (['Piano richiesto: Strutture [strutture]', '1 o 2 strutture ricettive', '320 € + IVA / anno complessivi', 'Servizio aggiuntivo richiesto: Max Full', 'Prezzo personalizzato', 'Attività, frequenza delle verifiche, canali e tempi di assistenza vengono definiti nel preventivo in base alle esigenze della struttura.', 'Messaggio del richiedente:'] as $part) {
            $this->assertStringContainsString($part, $lead->messaggio);
        }
        $this->assertStringNotContainsString('1 €', $lead->messaggio);
        $activity = CrmLeadActivity::where('crm_lead_id', $lead->id)->sole();
        $this->assertSame($lead->messaggio, $activity->descrizione);
        $this->assertSame('Richiesta demo', $activity->titolo);
        $this->assertSame($users, \App\Models\User::count());
        $this->assertSame($structures, \App\Models\Struttura::withoutGlobalScopes()->count());
    }

    public function test_preventivi_fuori_soglia_e_servizio_senza_piano_sono_richieste_distinte(): void
    {
        foreach ([['plan' => 'oltre7'], ['plan' => 'oltre2'], ['max_full' => true]] as $index => $selection) {
            $payload = $this->payload();
            $payload['email'] = 'preventivo-'.$index.'@example.invalid';
            $this->postJson('/contatti/richiesta', $payload + $selection)->assertOk();
            $message = CrmLead::where('email', $payload['email'])->sole()->messaggio;
            $this->assertStringContainsString($index === 2 ? 'Prezzo personalizzato' : 'Preventivo personalizzato', $message);
            $this->assertStringNotContainsString('320 €', $message);
        }
    }

    public function test_piani_inesistenti_e_valori_non_booleani_non_scrivono_nel_CRM(): void
    {
        $before = CrmLead::count();
        $activities = CrmLeadActivity::count();
        foreach (['strutture-plus', 'prova7', '<script>'] as $bad) {
            $this->postJson('/contatti/richiesta', $this->payload() + ['plan' => $bad])->assertUnprocessable()->assertJsonValidationErrors('plan');
        }
        $this->postJson('/contatti/richiesta', $this->payload() + ['max_full' => 'attiva_ora'])->assertUnprocessable()->assertJsonValidationErrors('max_full');
        $this->assertSame($before, CrmLead::count());
        $this->assertSame($activities, CrmLeadActivity::count());
    }

    public function test_campi_richiesti_e_honeypot_senza_scritture(): void
    {
        $before = CrmLead::count();
        $this->postJson('/contatti/richiesta', [])->assertUnprocessable()->assertJsonValidationErrors(['company', 'name', 'email', 'topic', 'message']);
        $payload = $this->payload();
        $payload['website'] = 'bot';
        $this->postJson('/contatti/richiesta', $payload)->assertUnprocessable()->assertJsonValidationErrors('website');
        $this->assertSame($before, CrmLead::count());
    }

    public function test_limite_contatti_ha_contatore_dedicato_e_non_blocca_altri_visitatori(): void
    {
        $before = CrmLead::count();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.77']);
        for ($i = 0; $i < 6; $i++) {
            $payload = $this->payload();
            $payload['email'] = 'limite-'.$i.'@example.invalid';
            $this->postJson('/contatti/richiesta', $payload)->assertOk();
        }
        $this->postJson('/contatti/richiesta', $this->payload())->assertStatus(429)->assertHeader('Retry-After');
        $this->assertSame($before + 6, CrmLead::count());
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.78']);
        $this->postJson('/contatti/richiesta', $this->payload())->assertOk();
        $this->assertSame($before + 7, CrmLead::count());
    }

    public function test_SEO_canonico_e_sitemap_coerenti_sul_dominio_ufficiale(): void
    {
        $titles = $descriptions = [];
        foreach (['index', 'moduli', 'ampliamenti', 'demo', 'prezzi', 'contatti', 'note-legali', 'privacy', 'cookie'] as $page) {
            $html = $this->get($page === 'index' ? '/' : '/'.$page)->getContent();
            preg_match('/<title>\s*(.*?)\s*<\/title>/s', $html, $title);
            preg_match('/<meta name="description" content="([^"]+)">/', $html, $description);
            $this->assertNotEmpty($title[1] ?? null);
            $this->assertNotEmpty($description[1] ?? null);
            $titles[] = trim($title[1]);
            $descriptions[] = $description[1];
            $canonical = 'https://schedinedinotifica.tanggo.software'.($page === 'index' ? '/' : '/'.$page);
            $this->assertStringContainsString('rel="canonical" href="'.$canonical.'"', $html);
            $this->assertStringContainsString('property="og:url" content="'.$canonical.'"', $html);
        }
        $this->assertCount(9, array_unique($titles));
        $this->assertCount(9, array_unique($descriptions));
        $xml = simplexml_load_file(public_path('sitemap.xml'));
        $this->assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->assertCount(9, $xml->xpath('//s:loc'));
        $this->assertStringContainsString('https://schedinedinotifica.tanggo.software/sitemap.xml', file_get_contents(public_path('robots.txt')));
    }

    private function payload(): array
    {
        return ['company' => 'Hotel Sintetico', 'name' => 'Referente Demo', 'email' => 'sito@example.invalid', 'topic' => 'Richiesta demo', 'message' => 'Messaggio sintetico per il collaudo.', 'website' => '', 'consent' => true];
    }
}
