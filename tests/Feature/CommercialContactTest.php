<?php

namespace Tests\Feature;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommercialContactTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(array $changes = []): array
    {
        return array_replace([
            'contact_form' => 'commercial-v2', 'company' => 'Hotel Sintetico',
            'structure_type' => 'hotel', 'name' => 'Referente Sintetico',
            'email' => 'contatto@example.invalid', 'phone' => '+39 0541 123456',
            'city' => 'Bellaria Igea Marina', 'topic' => 'Richiesta demo',
            'demo_mode' => 'online', 'consent' => true, 'website' => '',
        ], $changes);
    }

    public function test_tre_motivi_registrati_nel_crm_e_nelle_attivita_con_messaggio_facoltativo(): void
    {
        foreach (['Richiesta demo', 'Presentazione di gruppo', 'Informazioni e personalizzazioni'] as $i => $topic) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.'.(170 + $i)]);
            $response = $this->postJson('/contatti/richiesta', $this->payload(['topic' => $topic]))->assertOk()->assertJsonPath('ok', true);
            $lead = CrmLead::findOrFail($response->json('lead_id'));
            $this->assertSame($topic, $lead->modalita_contatto);
            $this->assertSame('Bellaria Igea Marina', $lead->localita);
            $this->assertSame('+39 0541 123456', $lead->telefono);
            $this->assertStringContainsString('Tipo di struttura: Hotel', $lead->messaggio);
            $activity = $lead->activities()->sole();
            $this->assertSame($topic, $activity->titolo);
            $this->assertSame($lead->messaggio, $activity->descrizione);
            if ($topic !== 'Richiesta demo') {
                $this->assertStringNotContainsString('Preferenza demo:', $lead->messaggio);
            }
        }
    }

    public function test_preferenze_demo_distinte_e_non_promesse_come_appuntamenti(): void
    {
        foreach (['online' => 'Online', 'visita' => 'Visita in struttura', 'da-concordare' => 'Da concordare'] as $mode => $label) {
            $response = $this->postJson('/contatti/richiesta', $this->payload(['demo_mode' => $mode]))->assertOk();
            $lead = CrmLead::findOrFail($response->json('lead_id'));
            $this->assertStringContainsString('Preferenza demo: '.$label, $lead->messaggio);
            $this->assertStringContainsString('La modalità viene concordata con la struttura.', $lead->messaggio);
            $this->assertNull($lead->prossimo_contatto_at);
        }
        $response = $this->postJson('/contatti/richiesta', $this->payload(['demo_mode' => null]))->assertOk();
        $this->assertStringContainsString('Preferenza demo: Da concordare', CrmLead::findOrFail($response->json('lead_id'))->messaggio);
    }

    public function test_piano_funzione_e_max_full_conservati_con_tariffa_canonica(): void
    {
        $response = $this->postJson('/contatti/richiesta', $this->payload([
            'topic' => 'Presentazione di gruppo', 'plan' => 'duo', 'interest' => 'web-checkin',
            'max_full' => true, 'message' => 'Vorrei conoscere anche altre soluzioni Tanggo.',
        ]))->assertOk();
        $lead = CrmLead::findOrFail($response->json('lead_id'));
        foreach (['100 € + IVA / anno', 'Interesse richiesto: Web Check-in [web-checkin]', 'Servizio aggiuntivo richiesto: Max Full', 'Vorrei conoscere anche altre soluzioni Tanggo.'] as $text) {
            $this->assertStringContainsString($text, $lead->messaggio);
        }
    }

    public function test_campi_principali_e_selezioni_non_validi_non_creano_richieste(): void
    {
        $n = CrmLead::count(); $a = CrmLeadActivity::count();
        $this->postJson('/contatti/richiesta', ['contact_form' => 'commercial-v2'])->assertUnprocessable()
            ->assertJsonValidationErrors(['company', 'name', 'email', 'phone', 'topic', 'consent']);
        foreach ([['structure_type' => 'inventato'], ['demo_mode' => 'garantita-domani'], ['phone' => 'telefono'], ['topic' => 'Evento inventato'], ['message' => str_repeat('a', 5001)]] as $change) {
            $this->postJson('/contatti/richiesta', $this->payload($change))->assertUnprocessable();
        }
        $this->assertSame($n, CrmLead::count()); $this->assertSame($a, CrmLeadActivity::count());
    }

    public function test_iscrizione_gruppo_non_duplica_lead_o_attivita_al_retry(): void
    {
        $payload = $this->payload(['topic' => 'Presentazione di gruppo']);
        $first = $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', false);
        $retry = $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame($first->json('lead_id'), $retry->json('lead_id'));
        $this->assertCount(1, CrmLead::findOrFail($first->json('lead_id'))->activities);
    }

    public function test_ripristino_nativo_preserva_tipo_localita_motivo_e_preferenza(): void
    {
        $payload = $this->payload(['email' => 'errata', 'city' => 'Località "<sintetica>', 'demo_mode' => 'visita', 'structure_type' => 'bnb', 'consent' => '1']);
        $this->from('/contatti')->post('/contatti/richiesta', $payload)->assertRedirect('/contatti')->assertSessionHasErrors('email');
        $html = $this->get('/contatti?motivo=gruppo')->assertOk()->getContent();
        $this->assertStringContainsString('value="visita" selected', $html);
        $this->assertStringContainsString('value="bnb" selected', $html);
        $this->assertStringContainsString('value="Località &quot;&lt;sintetica&gt;"', $html);
        $this->assertStringContainsString('data-contact-restored="true"', $html);
        $this->assertStringContainsString('La richiesta non è stata registrata.', $html);
    }

    public function test_senza_javascript_motivo_da_url_e_conferma_solo_dopo_salvataggio(): void
    {
        foreach (['demo' => 'Richiesta demo', 'gruppo' => 'Presentazione di gruppo', 'informazioni' => 'Informazioni e personalizzazioni'] as $query => $topic) {
            $html = $this->get('/contatti?motivo='.$query)->assertOk()->getContent();
            $this->assertStringContainsString('value="'.$topic.'" selected', $html);
        }
        $this->post('/contatti/richiesta', $this->payload(['topic' => 'Presentazione di gruppo', 'consent' => '1']))
            ->assertStatus(303)->assertRedirect('/contatti')->assertSessionHas('sito-richiesta-registrata', true);
        $this->assertSame('Presentazione di gruppo', CrmLead::where('email', 'contatto@example.invalid')->sole()->modalita_contatto);
        $this->get('/contatti')->assertOk()->assertSee('Richiesta registrata. Grazie per averci contattato.');
    }

    public function test_recapito_email_telefono_o_entrambi_e_facoltativi_assenti_arrivano_al_crm(): void
    {
        foreach ([['email' => 'solo-email@example.invalid'], ['phone' => '+39 0541 123456'], ['email' => 'entrambi@example.invalid', 'phone' => '+39 0541 654321']] as $i => $recapiti) {
            $payload = ['contact_form' => 'commercial-v2', 'company' => 'Struttura minima '.$i, 'name' => 'Persona sintetica', 'topic' => 'Richiesta demo', 'consent' => true] + $recapiti;
            $response = $this->postJson('/contatti/richiesta', $payload)->assertOk();
            $lead = CrmLead::findOrFail($response->json('lead_id'));
            $this->assertSame($recapiti['email'] ?? '', $lead->email);
            $this->assertSame($recapiti['phone'] ?? null, $lead->telefono);
            $this->assertNull($lead->localita);
            $this->assertStringNotContainsString('Tipo di struttura:', $lead->messaggio);
            $this->assertCount(1, $lead->activities);
            $this->postJson('/contatti/richiesta', $payload)->assertOk()->assertJsonPath('duplicate', true)->assertJsonPath('lead_id', $lead->id);
        }
    }

    public function test_recapiti_assenti_o_non_validi_rifiutati_senza_scritture(): void
    {
        $count = CrmLead::count(); $activities = CrmLeadActivity::count();
        foreach ([['email' => null, 'phone' => null], ['email' => 'errata', 'phone' => null], ['email' => null, 'phone' => 'ciao'], ['email' => 'valida@example.invalid', 'phone' => 'errato'], ['email' => 'errata', 'phone' => '+39 0541 123456']] as $change) {
            $this->postJson('/contatti/richiesta', $this->payload($change))->assertUnprocessable();
        }
        $this->assertSame($count, CrmLead::count()); $this->assertSame($activities, CrmLeadActivity::count());
    }

    public function test_telefono_solo_senza_javascript_e_ripristino_piano_dopo_errore(): void
    {
        $payload = $this->payload(['email' => '', 'phone' => '+39 0541 123456', 'plan' => 'multi', 'interest' => 'web-checkin', 'max_full' => '1', 'name' => '', 'consent' => '1']);
        $this->from('/contatti')->post('/contatti/richiesta', $payload)->assertSessionHasErrors('name');
        $html = $this->get('/contatti')->getContent();
        foreach (['value="multi" selected', 'value="web-checkin" selected', 'id="contact-error-name"', 'aria-invalid="true"', 'value="+39 0541 123456"'] as $text) $this->assertStringContainsString($text, $html);
        $this->post('/contatti/richiesta', array_replace($payload, ['name' => 'Nome sintetico']))->assertStatus(303)->assertSessionHas('sito-richiesta-registrata', true);
    }

    public function test_preselezioni_da_url_disponibili_anche_senza_javascript(): void
    {
        $html = $this->get('/contatti?piano=solo&interesse=web-checkin&servizio=max-full')->assertOk()->getContent();
        $this->assertStringContainsString('value="solo" selected', $html);
        $this->assertStringContainsString('value="web-checkin" selected', $html);
        $this->assertMatchesRegularExpression('~<input[^>]*name="max_full"[^>]*checked~', $html);
        $this->get('/contatti?piano[]=solo&interesse[]=web-checkin&motivo[]=demo')->assertOk();
    }

    public function test_unico_modulo_commerciale_e_tour_demo_conservato(): void
    {
        $this->get('/contatti')->assertOk()->assertSee('Parliamo della')->assertSee('name="structure_type"', false);
        $this->get('/demo')->assertOk()->assertDontSee('data-demo-form', false)->assertSee('data-product-tour', false)->assertSee('href="/contatti#demo"', false);
    }
}
