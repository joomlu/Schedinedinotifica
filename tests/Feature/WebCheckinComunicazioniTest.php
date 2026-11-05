<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinComunicazione;
use App\Models\WebCheckinModello;
use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinLink;
use App\Services\WebCheckinMessaggio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinComunicazioniTest extends TestCase
{
    use \Tests\Support\WebCheckinPublicInput;
    use RefreshDatabase, StrutturaFixtures;
    use \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    private function scenario(): WebCheckinRichiesta
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'arrive' => now()->addDays(10)->toDateString(), 'departure' => now()->addDays(12)->toDateString()]);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'SINTETICO'.$p->id, 'numero_prenotazione' => 'PRENOTAZIONE-SINTETICA', 'email' => 'ospite@example.invalid', 'nome_referente' => 'Referente sintetico', 'arrivo' => $p->arrive, 'partenza' => $p->departure, 'quantita_persone' => 3, 'note' => 'SEGRETO-INTERNO', 'token' => Str::random(64)]);
        app(WebCheckinLink::class)->issue($r);

        return $r;
    }

    public function test_inizializzazione_idempotente_preserva_personalizzazioni_e_isola_strutture(): void
    {
        $r = $this->scenario();
        $url = route('web_checkin.comunicazioni', $r->id);
        $this->get($url)->assertOk()->assertSee('Invito Web Check-in');
        $this->assertSame(3, WebCheckinModello::count());
        $m = WebCheckinModello::first();
        $m->update(['nome' => 'Modello personalizzato']);
        $this->get($url)->assertOk()->assertSee('Modello personalizzato');
        $this->assertSame(3, WebCheckinModello::count());
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $other->id));
        $this->withSession(['struttura_corrente_id' => $other->id])->get($url)->assertNotFound();
    }

    public function test_anteprima_cifrata_retry_idempotente_senza_modificare_modello_o_inviare(): void
    {
        $r = $this->scenario();
        $this->get(route('web_checkin.comunicazioni', $r->id))->assertOk();
        $m = WebCheckinModello::first();
        $data = ['modello_id' => $m->id, 'oggetto' => 'Messaggio per {{referente}}', 'corpo' => 'Personalizzato {{link}} scade {{scadenza}}', 'canale' => 'testo', 'idempotenza' => (string) Str::uuid()];
        $url = route('web_checkin.prepara', $r->id);
        $this->postWeb($url, $data)->assertRedirect();
        $c = WebCheckinComunicazione::firstOrFail();
        $this->postWeb($url, $data)->assertRedirect(route('web_checkin.comunicazione', [$r->id, $c->id]));
        $this->assertSame(1, WebCheckinComunicazione::count());
        $this->assertSame('preparato', $c->stato);
        $this->assertSame($m->corpo, $m->fresh()->corpo);
        $raw = DB::table('web_checkin_comunicazioni')->value('contenuto');
        $this->assertStringNotContainsString($r->short_token, $raw);
        $this->assertStringNotContainsString('Referente sintetico', $raw);
        $this->get(route('web_checkin.comunicazione', [$r->id, $c->id]))->assertOk()->assertSee('Referente sintetico')->assertDontSee('SEGRETO-INTERNO');
    }

    public function test_variabili_sconosciute_codice_e_dati_mancanti_rifiutati_senza_storico(): void
    {
        $r = $this->scenario();
        $r->struttura->update(['email' => '']);
        foreach (['{{sconosciuta}} {{link}}', '@php echo 1; {{link}}', '{{email_struttura}} {{link}}', '{{link + 1}}'] as $body) {
            $this->postJsonWeb(route('web_checkin.prepara', $r->id), ['oggetto' => 'Invito', 'corpo' => $body, 'canale' => 'testo', 'idempotenza' => (string) Str::uuid()])->assertUnprocessable();
        }
        $this->assertSame(0, WebCheckinComunicazione::count());
    }

    public function test_tre_modelli_non_assegnano_classificazioni_e_note_non_diventano_variabili(): void
    {
        $r = $this->scenario();
        foreach (WebCheckinMessaggio::iniziali() as $m) {
            $rendered = app(WebCheckinMessaggio::class)->render($m['oggetto'], $m['corpo'], $r);
            $this->assertStringContainsString($r->short_token, $rendered['corpo']);
            $this->assertStringNotContainsString('SEGRETO-INTERNO', $rendered['corpo']);
        }
        $this->assertNull($r->schedina->relationship);
    }

    public function test_creazione_duplicazione_aggiornamento_esplicito_e_versione_obsoleta(): void
    {
        $r = $this->scenario();
        $this->get(route('web_checkin.comunicazioni', $r->id))->assertOk();
        $m = WebCheckinModello::first();
        $url = route('web_checkin.modello', $r->id);
        $data = ['nome' => 'Modello nuovo', 'oggetto' => 'Invito {{referente}}', 'corpo' => 'Testo {{link}}', 'azione' => 'nuovo'];
        $this->postWeb($url, $data)->assertRedirect();
        $this->assertSame(4, WebCheckinModello::count());
        $data['azione'] = 'duplica'; $data['modello_id'] = $m->id;
        $this->postWeb($url, $data)->assertRedirect();
        $this->assertSame(5, WebCheckinModello::count());
        $this->assertSame($m->corpo, $m->fresh()->corpo);
        $data['azione'] = 'aggiorna'; $data['versione'] = 1;
        $this->postWeb($url, $data)->assertRedirect();
        $this->assertSame(2, $m->fresh()->versione);
        $data['corpo'] = 'Vecchia scheda {{link}}';
        $this->postWeb($url, $data)->assertStatus(409);
        $this->assertSame('Testo {{link}}', $m->fresh()->corpo);
    }

    public function test_parziale_con_componente_continua_senza_perdere_note_contatti_e_blocca_scheda_vecchia(): void
    {
        $r = $this->scenario();
        $this->app['auth']->forgetGuards();
        $this->get('/w/'.$r->short_token)->assertOk();
        $r->refresh();
        $version = app(\App\Services\WebCheckinVersione::class)->of($r);
        $data = ['save_mode' => 'web', 'web_versione' => $version, 'name' => 'Nome parziale', 'componenti' => [['name' => 'Componente parziale']]];
        $this->postWeb('/checkin/'.$r->token, $data)->assertOk();
        $r->refresh();
        $this->assertSame('SEGRETO-INTERNO', $r->note);
        $this->assertSame('Referente sintetico', $r->nome_referente);
        $this->assertSame('ospite@example.invalid', $r->email);
        $this->assertSame(1, \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $r->schedina_id)->count());
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('Nome parziale')->assertSee('Componente parziale')->assertDontSee('SEGRETO-INTERNO');
        $this->postWeb('/checkin/'.$r->token, $data)->assertStatus(409);
        $this->assertSame(1, \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $r->schedina_id)->count());
        $this->assertSame('web', Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->circuito);
    }

    public function test_parziale_rifiuta_valori_invalidi_e_finale_incompleto_non_promuove(): void
    {
        $r = $this->scenario();
        $this->postJsonWeb('/checkin/'.$r->token, ['name' => 'Parziale', 'customer_email' => 'non-email'])->assertUnprocessable();
        $this->postJsonWeb('/checkin/'.$r->token, ['componenti' => [['name' => 'Parziale', 'sex' => 'X']]])->assertUnprocessable();
        $this->postJsonWeb('/checkin/'.$r->token, ['componenti' => [['name' => 'Parziale', 'date_nac' => 'non-data']]])->assertUnprocessable();
        $this->postJsonWeb('/checkin/'.$r->token, ['oa_country' => 'NAZIONE-INESISTENTE'])->assertUnprocessable();
        $this->postJsonWeb('/checkin/'.$r->token, ['componenti' => [['name' => 'Parziale', 'country_nac' => 'NAZIONE-INESISTENTE']]])->assertUnprocessable();
        $this->postJsonWeb('/checkin/'.$r->token, ['save_mode' => 'web_complete', 'name' => 'Parziale'])->assertUnprocessable();
        $this->assertNull($r->fresh()->compilato_at);
        $this->assertSame('web', $r->schedina->fresh()->circuito);
    }

    public function test_apertura_esterna_e_soltanto_richiesta_non_invio_o_consegna_e_revoca_la_blocca(): void
    {
        $r = $this->scenario();
        $r->update(['whatsapp' => '+390000000000']);
        $data = ['oggetto' => 'Invito', 'corpo' => 'Apri {{link}}', 'canale' => 'whatsapp', 'idempotenza' => (string) Str::uuid()];
        $this->postWeb(route('web_checkin.prepara', $r->id), $data)->assertRedirect();
        $c = WebCheckinComunicazione::firstOrFail();
        $url = route('web_checkin.comunicazione.apri', [$r->id, $c->id]);
        $this->postWeb($url, ['_token' => 'errato'])->assertStatus(419);
        $this->postWeb($url)->assertRedirect();
        $this->assertSame('apertura_richiesta', $c->fresh()->stato);
        $this->postWeb($url)->assertRedirect();
        $this->assertSame(1, WebCheckinComunicazione::count());
        app(WebCheckinLink::class)->revoke($r);
        $this->postJsonWeb($url)->assertUnprocessable();
    }

    public static function figure(): array
    {
        return [['OSPITE SINGOLO', null], ['CAPO FAMIGLIA', 'FAMILIARE'], ['CAPO GRUPPO', 'MEMBRO GRUPPO']];
    }

    public function test_moduli_senza_versione_o_con_versione_errata_non_scrivono(): void
    {
        $r = $this->scenario();
        $before = Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->getAttributes();
        $this->postJson('/checkin/'.$r->token, ['name' => 'Vecchio modulo'])->assertUnprocessable()->assertJsonValidationErrors('web_versione');
        $this->postJson('/checkin/'.$r->token, ['name' => 'Vecchio modulo', 'web_versione' => str_repeat('0', 64)])->assertStatus(409);
        $this->putJson(route('schedina.update', $r->schedina_id), ['save_mode' => 'draft', 'name' => 'Vecchio modulo'])->assertUnprocessable()->assertJsonValidationErrors('web_versione_gestione');
        $this->putJson(route('web_checkin.update', $r->id), ['note' => 'Vecchio modulo'])->assertUnprocessable()->assertJsonValidationErrors('web_versione_richiesta');
        $this->assertSame($before, Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->getAttributes());
        $this->assertSame('SEGRETO-INTERNO', $r->fresh()->note);
    }

    public function test_form_pubblico_non_riusa_dati_o_feedback_di_una_sessione_gestionale_estranea(): void
    {
        $r = $this->scenario();
        $this->withSession(['_old_input' => ['name' => 'SEGRETO-ALTRO-CLIENTE', 'customer_email' => 'segreto@example.invalid'], 'success' => 'SEGRETO-FEEDBACK-GESTIONALE'])
            ->get('/checkin/'.$r->token)->assertOk()->assertDontSee('SEGRETO-ALTRO-CLIENTE')->assertDontSee('segreto@example.invalid')->assertDontSee('SEGRETO-FEEDBACK-GESTIONALE');
    }

    public function test_note_lunghe_valide_conservate_e_limite_reale_in_byte_verificato_prima_di_sql(): void
    {
        $r = $this->scenario();
        $r->schedina->forceFill(['circuito' => 'schedina'])->save();
        $r->update(['stato' => 'convertito', 'note' => str_repeat('x', 60000)]);
        $this->putJsonWeb(route('web_checkin.update', $r->id), ['note' => str_repeat('x', 60000)])->assertRedirect();
        $this->putJsonWeb(route('web_checkin.update', $r->id), ['note' => str_repeat('😀', 20000)])->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->assertSame(str_repeat('x', 60000), $r->fresh()->note);
    }

    public function test_nuova_richiesta_non_inventa_camere_letti_o_tipo_alloggiato(): void
    {
        $r = $this->scenario();
        $data = $r->only(['email', 'nome_referente', 'quantita_persone']);
        $data['nome_referente'] = str_repeat('R', 150);
        $data += ['numero_prenotazione' => 'NUOVA-SINTETICA', 'arrivo' => $r->arrivo->toDateString(), 'partenza' => $r->partenza->toDateString()];
        $this->postWeb(route('web_checkin.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $created = WebCheckinRichiesta::where('numero_prenotazione', 'NUOVA-SINTETICA')->firstOrFail();
        $sheet = Schedina::withoutGlobalScopes()->findOrFail($created->schedina_id);
        $this->assertNull($sheet->room); $this->assertNull($sheet->beds); $this->assertNull($sheet->relationship);
        $this->assertNull($sheet->name); $this->assertSame($data['nome_referente'], $created->nome_referente);
        $this->assertSame('web', $sheet->circuito); $this->assertSame(0, Schedina::effettive()->count());
    }

    public function test_creazione_in_strutture_distinte_non_collide_con_codici_storici_globali(): void
    {
        $legacy = $this->scenario();
        $legacy->update(['codice' => 'WC1']);
        $before = $legacy->fresh()->getAttributes();
        $other = $this->structureFor(null);
        foreach ([$legacy->struttura_id, $other->id] as $sid) {
            $this->actingAs($this->actor('struttura_user', null, $sid));
            foreach ([1, 2] as $number) {
                $ref = 'TENANT-SINTETICO-'.$sid.'-'.$number;
                $this->withSession(['struttura_corrente_id' => $sid])->post(route('web_checkin.store'), [
                    'numero_prenotazione' => $ref, 'nome_referente' => 'Referente sintetico', 'email' => 'sintetico@example.invalid',
                    'arrivo' => now()->addDays(3)->toDateString(), 'partenza' => now()->addDays(6)->toDateString(), 'quantita_persone' => 1,
                ])->assertSessionHasNoErrors()->assertRedirect();
                $created = WebCheckinRichiesta::where('struttura_id', $sid)->where('numero_prenotazione', $ref)->firstOrFail();
                $this->assertLessThanOrEqual(30, strlen($created->codice));
                $this->assertMatchesRegularExpression('/\A[A-Za-z0-9]+\z/', $created->codice);
            }
        }
        $this->assertDatabaseCount('web_checkin_richieste', 5);
        $this->assertSame(5, WebCheckinRichiesta::pluck('codice')->unique()->count());
        $this->assertSame($before, $legacy->fresh()->getAttributes());
    }

    public function test_parametro_arrival_non_attiva_il_giorno_mobile_nel_form_pubblico(): void
    {
        $r = $this->scenario();
        $response = $this->get('/checkin/'.$r->token.'?arrival=1');
        $response->assertOk();
        $this->assertSame(0, preg_match('/<form[^>]+data-arrivi-clock=/', $response->getContent()));
        $this->assertSame(0, preg_match('/<input[^>]+name="arrivi_context"/', $response->getContent()));
        $this->assertSame($r->arrivo->toDateString(), Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->arrive);
    }

    public function test_modelli_e_storico_esterni_non_si_possono_leggere_duplicare_o_usare(): void
    {
        $a = $this->scenario();
        $this->get(route('web_checkin.comunicazioni', $a->id))->assertOk();
        $b = $this->scenario();
        $this->withSession(['struttura_corrente_id' => $b->struttura_id])->get(route('web_checkin.comunicazioni', $b->id))->assertOk();
        $model = WebCheckinModello::where('struttura_id', $b->struttura_id)->firstOrFail();
        $data = ['modello_id' => $model->id, 'oggetto' => 'Invito', 'corpo' => 'Apri {{link}}', 'canale' => 'testo', 'idempotenza' => (string) Str::uuid()];
        $this->postWeb(route('web_checkin.prepara', $b->id), $data)->assertRedirect();
        $c = WebCheckinComunicazione::firstOrFail();
        $this->actingAs($this->actor('struttura_user', null, $a->struttura_id));
        $this->withSession(['struttura_corrente_id' => $a->struttura_id])->get(route('web_checkin.comunicazioni', $b->id))->assertNotFound();
        $this->get(route('web_checkin.comunicazione', [$a->id, $c->id]))->assertNotFound();
        $this->postJsonWeb(route('web_checkin.prepara', $a->id), $data)->assertNotFound();
        $this->postJsonWeb(route('web_checkin.modello', $a->id), $data + ['nome' => 'Copia esterna', 'azione' => 'duplica'])->assertNotFound();
        $this->assertSame(6, WebCheckinModello::count());
        $this->assertSame(1, WebCheckinComunicazione::count());
    }

    public function test_cataloghi_pubblici_e_validazione_usano_il_tenant_del_link_anche_con_sessione_estranea(): void
    {
        $a = $this->scenario();
        $b = $this->scenario();
        foreach ([[$a, 'Catalogo-A'], [$b, 'SEGRETO-CATALOGO-B']] as [$r, $label]) {
            \App\Models\Titolo::forceCreate(['struttura_id' => $r->struttura_id, 'nome' => $label, 'attivo' => true]);
            \App\Models\TipoVia::forceCreate(['struttura_id' => $r->struttura_id, 'nome' => $label, 'attivo' => true]);
            \App\Models\RilasciatoDa::forceCreate(['struttura_id' => $r->struttura_id, 'name' => $label, 'attivo' => true]);
        }
        $this->withSession(['struttura_corrente_id' => $b->struttura_id])->get('/checkin/'.$a->token)->assertOk()->assertSee('Catalogo-A')->assertDontSee('SEGRETO-CATALOGO-B');
        $this->postWeb('/checkin/'.$a->token, ['type' => 'Catalogo-A', 'name' => 'Ospite'])->assertOk();
        $this->postJsonWeb('/checkin/'.$a->token, ['type' => 'SEGRETO-CATALOGO-B'])->assertUnprocessable();
        $this->assertSame('Catalogo-A', Schedina::withoutGlobalScopes()->findOrFail($a->schedina_id)->type);
    }

    public function test_componenti_con_sola_nascita_e_modifica_note_richiesta_non_cancellano_dati_ricevuti(): void
    {
        $r = $this->scenario();
        $this->postWeb('/checkin/'.$r->token, ['name' => 'Titolare ricevuto', 'surname' => 'Sintetico', 'componenti' => [['date_nac' => '2000-01-01']]])->assertOk();
        $this->postWeb('/checkin/'.$r->token, ['name' => 'Titolare corretto'])->assertOk();
        $data = $r->only(['numero_prenotazione', 'email', 'nome_referente', 'quantita_persone']);
        $data += ['arrivo' => $r->arrivo->toDateString(), 'partenza' => $r->partenza->toDateString(), 'note' => 'Nota interna aggiornata'];
        $this->putWeb(route('web_checkin.update', $r->id), $data)->assertSessionHasNoErrors()->assertRedirect();
        $sheet = Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id);
        $this->assertSame('Titolare corretto', $sheet->name);
        $this->assertSame('Sintetico', $sheet->surname);
        $this->assertSame(1, \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $sheet->id)->count());
        $this->get('/checkin/'.$r->token)->assertOk()->assertDontSee('Nota interna aggiornata');
        $this->assertSame('Nota interna aggiornata', $r->fresh()->note);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('figure')]
    public function test_compilazione_valida_bozza_e_finalizzazione_stesso_id_con_dati_preservati(string $head, ?string $member): void
    {
        $r = $this->scenario();
        $this->cataloghiSchedina($r->struttura_id);
        $r->update(['quantita_persone' => $member ? 2 : 1]);
        $payload = array_replace($this->validSchedinaPayload(), ['arrive' => $r->arrivo->toDateString(), 'departure' => $r->partenza->toDateString(), 'relationship' => $head, 'cant_people' => $member ? 2 : 1, 'save_mode' => 'web_complete']);
        if ($member) $payload['componenti'] = [['name' => 'Componente', 'surname' => 'Sintetico', 'sex' => 'F', 'relationship' => $member, 'exent' => 'NO', 'country_nac' => 'ITALIA', 'city_nac' => 'ITALIANA', 'province_nac' => 'RM', 'regione_nac' => 'Lazio', 'comune_nac' => 'Roma', 'date_nac' => '2000-01-01', 'country'=>'ITALIA', 'regione'=>'Lazio', 'province'=>'RM', 'city'=>'Roma']];
        $this->postWeb('/checkin/'.$r->token, $payload)->assertSessionHasNoErrors()->assertOk();
        $this->assertSame('compilato', $r->fresh()->stato);
        $this->assertSame(0, Schedina::effettive()->count());
        $r->refresh(); $sheet = Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id);
        $payload['componenti'] = \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $sheet->id)->get()->map->getAttributes()->all();
        $payload['web_versione_gestione'] = app(\App\Services\WebCheckinVersione::class)->of($r);
        $payload['save_mode'] = 'draft';
        $this->putWeb(route('schedina.update', $sheet->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('bozza', $sheet->fresh()->circuito);
        $this->assertSame(0, Schedina::effettive()->count());
        $this->get('/checkin/'.$r->token)->assertOk()->assertSeeText('Grazie! Abbiamo ricevuto i tuoi dati.')->assertDontSee('<form', false);
        $this->putJsonWeb(route('schedina.update', $sheet->id), $payload)->assertStatus(409);
        $r->refresh(); $payload['web_versione_gestione'] = app(\App\Services\WebCheckinVersione::class)->of($r); $payload['save_mode'] = 'full';
        $this->putWeb(route('schedina.update', $sheet->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('schedina', $sheet->fresh()->circuito);
        $this->assertDatabaseCount('schedina', 1);
        $this->assertSame($member ? 1 : 0, \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $sheet->id)->count());
        $this->assertSame('SEGRETO-INTERNO', $r->fresh()->note);
        $this->assertSame('PRENOTAZIONE-SINTETICA', $r->fresh()->numero_prenotazione);
        foreach (['questura_transmissions', 'istat_transmissions', 'tassa_exports'] as $table) $this->assertDatabaseCount($table, 0);
    }
}
