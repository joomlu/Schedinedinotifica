<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\TassaEsenzione;
use App\Services\TassaConfig\AnalisiDocumentoTassa;
use App\Services\TassaConfig\FonteUfficialeTassa;
use App\Services\TassaConfig\GestioneProfiliTassa;
use App\Services\TassaConfig\ProfiloTassaRepository;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class ConfigurazioneTassaSuperadminTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->travelTo(\Carbon\Carbon::parse('2026-10-13'));
        // Questi documenti sono fixture di una consultazione istituzionale completa,
        // non fonti reali. Le prove HTTP sottostanti verificano separatamente il flag.
        $r = app(ProfiloTassaRepository::class);
        foreach ([2026, 2027] as $anno) {
            $scheda = $r->leggi($r->scheda('Bellaria-Igea Marina', $anno));
            $scheda['verifica_completa'] = true;
            $r->scrivi($r->scheda('Bellaria-Igea Marina', $anno), $scheda);
        }

        $this->app->bind(FonteUfficialeTassa::class, fn () => new class extends FonteUfficialeTassa
        {
            protected function indirizzi(string $host): array
            {
                return ['93.184.216.34'];
            }
        });
    }

    private function contesto(int $anno = 2027): array
    {
        return ['comune' => 'Bellaria-Igea Marina', 'anno' => $anno, 'categoria' => 3];
    }

    private function testo(int $anno = 2027): string
    {
        return "Regolamento Imposta di soggiorno approvato dal Comune di Bellaria-Igea Marina. Tariffa per persona e per notte. Art. 2\nValidità dal 01/01/$anno al 31/12/$anno. In vigore dal 01/01/$anno. Periodo di applicazione dell’imposta dal 01/06/$anno al 30/09/$anno. Massimo 6 pernottamenti consecutivi. Esenzione fino a 17 anni inclusi. Età minima imponibile: 18. Le esenzioni restano invariate. Art. 4\nAlberghi 1 stelle euro 1,00\nAlberghi 2 stelle euro 1,00\nAlberghi 3 stelle euro 1,50\nAlberghi 4 stelle euro 2,50\nAlberghi 5 stelle euro 2,50";
    }

    public function test_tutte_le_azioni_rifiutano_ruoli_diversi_dal_superadmin(): void
    {
        foreach (['admin', 'proprietario', 'struttura_user'] as $ruolo) {
            $this->actingAs($this->actor($ruolo));
            $this->get('/superadmin/tassa/configurazione')->assertForbidden();
            foreach (['verifica', 'documento', 'conferma'] as $azione) {
                $this->post('/superadmin/tassa/configurazione/'.$azione, $this->contesto())->assertForbidden();
            }
            $this->get('/superadmin/tassa/configurazione/documenti/'.str_repeat('a', 64).'?'.http_build_query($this->contesto()))->assertForbidden();
        }
        $this->actingAs($this->actor('super_admin'));
        $this->get('/superadmin/tassa/configurazione')->assertOk()->assertSee('Configurazione Tassa di soggiorno')->assertSee('Familiari del gestore');
        $this->get('/superadmin/tassa/configurazione?'.http_build_query($this->contesto()))->assertOk()->assertSee('Non è disponibile un profilo comunale');
    }

    public function test_upload_testo_e_pdf_non_certificano_da_soli_la_fonte(): void
    {
        $this->actingAs($this->actor('super_admin'));
        $this->post('/superadmin/tassa/configurazione/documento', $this->contesto() + ['testo' => $this->testo()])->assertRedirect();
        $r = app(ProfiloTassaRepository::class);
        $s = $r->leggi($r->scheda('Bellaria-Igea Marina', 2027));
        $d = reset($s['documenti']);
        $this->assertFalse($d['ufficiale']);
        $this->assertCount(1, $r->profiliBellaria());
        Storage::disk('local')->assertExists($d['path']);
        $this->get('/superadmin/tassa/configurazione/documenti/'.$d['id'].'?'.http_build_query($this->contesto()))->assertOk();
        $this->post('/superadmin/tassa/configurazione/documento', $this->contesto() + ['pdf' => UploadedFile::fake()->createWithContent('norma.pdf', "%PDF-1.4\nDocumento sintetico, non ufficiale")])->assertRedirect();
        $this->assertCount(2, $r->leggi($r->scheda('Bellaria-Igea Marina', 2027))['documenti']);
        $this->assertCount(1, $r->profiliBellaria());
    }

    public function test_fonte_completa_futura_si_applica_senza_conferma_e_senza_duplicare_valori(): void
    {
        Http::fake(['*' => Http::response($this->testo(), 200)]);
        $this->actingAs($this->actor('super_admin'));
        $this->post('/superadmin/tassa/configurazione/documento', $this->contesto() + ['fonte' => 'https://www.comune.bellaria-igea-marina.rn.it/norma'])->assertRedirect()->assertSessionHasNoErrors();
        $this->artisan('tassa:verifica-profili')->assertSuccessful();
        $r = app(ProfiloTassaRepository::class);
        $p = $r->profiliBellaria();
        $this->assertCount(2, $p, json_encode($r->leggi($r->scheda('Bellaria-Igea Marina', 2027))));
        $this->assertSame(config('tassa_profili')[0], $p[0]);
        $this->assertSame('2027-06-01', $p[1]['inizio']);
        $saved = json_decode(Storage::disk('local')->get(Storage::disk('local')->files('tassa-profili/versioni')[0]), true);
        $this->assertSame('bellaria-alberghi-2026-v1', $saved['riusa_versione']);
        $this->assertArrayNotHasKey('tariffe', $saved['profilo']);
        $this->assertSame('automatica', $saved['provenienza']['modo']);
        $this->get('/superadmin/tassa/configurazione?'.http_build_query($this->contesto()))->assertOk()->assertSee('Completo');
    }

    public function test_documento_incompleto_contraddittorio_o_anno_limitato_non_viene_prolungato(): void
    {
        $g = app(GestioneProfiliTassa::class);
        foreach ([$this->testo(2026), str_replace('Alberghi 3 stelle euro 1,50', '', $this->testo()), $this->testo()."\nAlberghi 3 stelle euro 9,00", str_replace('In vigore dal 01/01/2027.', '', $this->testo()), str_replace('In vigore dal 01/01/2027.', 'In vigore dal 01/02/2027.', $this->testo())] as $testo) {
            $d = $g->conserva('Bellaria-Igea Marina', 2027, $testo, 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
            $this->assertNotEmpty($d['analisi']['problemi']);
            $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        }
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $this->assertEmpty(app(AnalisiDocumentoTassa::class)->estrai($this->testo(2026), 2027)['valori']);
        $this->assertArrayNotHasKey('tariffa_3', app(AnalisiDocumentoTassa::class)->estrai(str_replace('Alberghi 3 stelle euro 1,50', '', $this->testo()), 2027)['valori']);
    }

    public function test_errore_rete_non_dimostra_assenza_di_nuove_disposizioni_e_ssrf_respinto(): void
    {
        Http::fake(['*' => Http::response('', 503)]);
        $g = app(GestioneProfiliTassa::class);
        $s = $g->verifica('Bellaria-Igea Marina', 2027);
        $this->assertNotEmpty($s['verifica']);
        $this->assertStringContainsString('non significa', implode(' ', $s['verifica']));
        $this->assertCount(1, $g->archivio->profiliBellaria());
        foreach (['https://127.0.0.1/norma', 'http://www.comune.bellaria-igea-marina.rn.it/norma', 'https://utente:password@www.comune.bellaria-igea-marina.rn.it/norma'] as $url) {
            try {
                $g->fonti->scarica($url, 'Bellaria-Igea Marina');
                $this->fail('Fonte non sicura accettata');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertArrayHasKey('fonte', $e->errors());
            }
        }
    }

    public function test_storico_e_calcolo_450_restano_invariati(): void
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $es = TassaEsenzione::create(['struttura_id' => $s->id, 'codice' => '450', 'descrizione' => 'Familiari del gestore se anagraficamente conviventi', 'attivo' => true, 'richiede_nota' => true]);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Fixture', 'surname' => 'Familiari', 'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'oa_date_nac' => '1980-01-01', 'exent' => '450', 'is_arrive' => 0]);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
        $prima = $p->fresh()->getAttributes();
        $g = app(GestioneProfiliTassa::class);
        $d = $g->conserva('Bellaria-Igea Marina', 2026, $this->testo(2026), 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        try {
            $g->provaApplicazione('Bellaria-Igea Marina', 2026, $d);
            $this->fail('Storico riscritto');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('validita', $e->errors());
        }
        $this->assertSame($prima, $p->fresh()->getAttributes());
        $this->assertSame('450', $es->fresh()->codice);
        $this->assertCount(1, $g->archivio->profiliBellaria());
    }

    public function test_continuita_esplicita_del_nuovo_anno_riutilizza_il_profilo_e_quella_incerta_non_lo_fa(): void
    {
        $g = app(GestioneProfiliTassa::class);
        $t = 'Comune di Bellaria-Igea Marina. Deliberazione Imposta di soggiorno approvata. Sono confermate per l’anno 2027 le tariffe, il periodo, i limiti e le esenzioni dell’anno 2026. In vigore dal 01/01/2027.';
        $d = $g->conserva('Bellaria-Igea Marina', 2027, str_replace('In vigore dal 01/01/2027.', '', $t), 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $t, 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        $this->assertCount(2, $g->archivio->profiliBellaria());
        $this->assertSame('2027-09-30', $g->archivio->profiliBellaria()[1]['fine']);
    }

    public function test_conferma_manuale_richiede_fonte_verificata_e_salva_motivazione_senza_modificare_la_baseline(): void
    {
        $this->actingAs($this->actor('super_admin'));
        $g = app(GestioneProfiliTassa::class);
        $testo = $this->testo();
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $testo, 'testo', null, false);
        $valori = app(AnalisiDocumentoTassa::class)->estrai($testo, 2027)['valori'];
        $payload = $this->contesto() + $valori + ['documento' => $d['id'], 'conferma' => 1, 'motivazione' => 'Articoli 2 e 4 della fixture sintetica verificati dal Superadmin.'];
        $this->post('/superadmin/tassa/configurazione/conferma', $payload)->assertSessionHasErrors('riscontro_comunale');
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $testo, 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        $this->post('/superadmin/tassa/configurazione/conferma', array_replace($payload, ['conferma' => 0]))->assertSessionHasErrors('conferma');
        $this->post('/superadmin/tassa/configurazione/conferma', $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(2, $g->archivio->profiliBellaria());
        $this->assertSame(config('tassa_profili')[0], $g->archivio->profiliBellaria()[0]);
        $s = $g->archivio->leggi($g->archivio->scheda('Bellaria-Igea Marina', 2027));
        $this->assertSame($payload['motivazione'], $s['documenti'][$d['id']]['revisione']['motivazione']);
    }

    public function test_verifica_periodica_e_manuale_attendono_tutti_gli_allegati_e_respingono_conflitti(): void
    {
        $root = config('tassa_fonti.Bellaria-Igea Marina.indice');
        Http::fake([
            $root => Http::response('<a href="/tariffe-a">Tariffe</a><a href="/tariffe-b">Deliberazione</a>', 200),
            'https://www.comune.bellaria-igea-marina.rn.it/it/unita_organizzative/imposta-di-soggiorno' => Http::response('Pagina ufficio tributi, fixture senza nuove disposizioni.', 200),
            'https://www.comune.bellaria-igea-marina.rn.it/tariffe-a' => Http::response($this->testo(), 200),
            'https://www.comune.bellaria-igea-marina.rn.it/tariffe-b' => Http::response(str_replace('euro 1,50', 'euro 2,00', $this->testo()), 200),
        ]);
        $g = app(GestioneProfiliTassa::class);
        $s = $g->verifica('Bellaria-Igea Marina', 2027);
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $this->assertStringContainsString('discordanti', implode(' ', $s['verifica']));
        $this->artisan('tassa:verifica-profili')->assertSuccessful();
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $this->assertNotNull($g->archivio->leggi($g->archivio->scheda('Bellaria-Igea Marina', 2026))['ultima_verifica']);
    }

    public function test_profilo_futuro_idempotente_coerente_con_il_calcolo_esistente_e_storico_preservato(): void
    {
        $g = app(GestioneProfiliTassa::class);
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $this->testo(), 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        $this->assertCount(2, $g->archivio->profiliBellaria());
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Fixture', 'surname' => 'Futuro', 'arrive' => '2027-06-01', 'departure' => '2027-06-03', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 3);
        $this->get('/tassa_di_soggiorno?anno_fiscale=2027')->assertOk()->assertSee('Dal 01/06/2027 al 30/09/2027');
        $storico = app(TassaDiSoggiornoService::class)->risolviProfiloFiscale($s, \Carbon\Carbon::parse('2026-06-01'));
        $this->assertSame('bellaria-alberghi-2026-v1', $storico['regola_versione']);
        $this->assertSame('1.50', $storico['tassa_soggiorno']);
    }

    public function test_host_privato_redirect_e_html_nascosto_non_diventano_regole_ufficiali(): void
    {
        $fonte = new class extends FonteUfficialeTassa
        {
            protected function indirizzi(string $host): array
            {
                return ['127.0.0.1'];
            }
        };
        try {
            $fonte->scarica('https://www.comune.bellaria-igea-marina.rn.it/norma', 'Bellaria-Igea Marina');
            $this->fail('Indirizzo privato accettato');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fonte', $e->errors());
        }
        Http::fake(['*' => Http::response('', 302, ['Location' => 'https://127.0.0.1/interno'])]);
        try {
            app(FonteUfficialeTassa::class)->scarica('https://www.comune.bellaria-igea-marina.rn.it/norma', 'Bellaria-Igea Marina');
            $this->fail('Redirect seguito');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fonte', $e->errors());
        }
        Http::assertSentCount(1);
        $a = app(AnalisiDocumentoTassa::class);
        $testo = $a->testo('<h1>Pagina istituzionale</h1><script>'.$this->testo().'</script>', 'html');
        $this->assertNotEmpty($a->estrai($testo['testo'], 2027)['problemi']);
    }

    public function test_documento_collegato_ufficialmente_ammette_host_esterno_soltanto_per_url_esatto(): void
    {
        $url = 'https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/fixture/norma.pdf';
        Http::fake(['*' => Http::response($this->testo(), 200)]);
        $f = app(FonteUfficialeTassa::class);
        try {
            $f->scarica($url, 'Bellaria-Igea Marina');
            $this->fail('Host esterno non collegato accettato');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fonte', $e->errors());
        }
        $this->assertSame($this->testo(), $f->scarica($url, 'Bellaria-Igea Marina', [$url])['bytes']);
        try {
            $f->scarica($url.'?altro=1', 'Bellaria-Igea Marina', [$url]);
            $this->fail('URL differente accettato');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fonte', $e->errors());
        }
    }

    public function test_documento_ricevuto_dal_comune_richiede_riscontro_e_vigore_attestati_dal_superadmin(): void
    {
        $this->actingAs($this->actor('super_admin'));
        $g = app(GestioneProfiliTassa::class);
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $this->testo(), 'testo', null, false);
        $v = $d['analisi']['valori'];
        $payload = $this->contesto() + $v + ['documento' => $d['id'], 'conferma' => 1, 'motivazione' => 'Verifica sintetica degli articoli 2 e 4 del documento ricevuto.'];
        $this->post('/superadmin/tassa/configurazione/conferma', $payload)->assertSessionHasErrors(['riscontro_comunale', 'riferimento_vigore']);
        $this->assertCount(1, $g->archivio->profiliBellaria());
        $payload += ['verifica_provenienza' => 1, 'riscontro_comunale' => 'Fixture: protocollo comunale 123 del 10 ottobre 2026 riscontrato dall’ufficio.', 'riferimento_vigore' => 'Fixture: articolo 2, vigenza dal primo gennaio 2027.'];
        $this->post('/superadmin/tassa/configurazione/conferma', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $s = $g->archivio->leggi($g->archivio->scheda('Bellaria-Igea Marina', 2027));
        $this->assertArrayHasKey('attestazione_superadmin', $s['documenti'][$d['id']]);
        $this->assertCount(2, $g->archivio->profiliBellaria());
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $s['documenti'][$d['id']]);
        $this->assertSame('confermata dal Superadmin', $s['applicazione']['modo']);
    }

    public function test_richiesta_rifiutata_se_il_superadmin_viene_revocato_prima_del_controllo(): void
    {
        $u = $this->actor('super_admin');
        $this->actingAs($u);
        $this->post('/superadmin/tassa/configurazione/verifica', $this->contesto())->assertRedirect();
        $richieste = app(\App\Services\TassaConfig\RichiesteVerificaTassa::class);
        $this->assertTrue($richieste->inCoda('Bellaria-Igea Marina', 2027));
        $u->update(['ruolo' => 'admin']);
        $richieste->processa();
        $this->assertFalse($richieste->inCoda('Bellaria-Igea Marina', 2027));
        $r = app(ProfiloTassaRepository::class);
        $s = $r->leggi($r->scheda('Bellaria-Igea Marina', 2027));
        $this->assertStringContainsString('annullata', implode(' ', $s['verifica']));
        $this->assertCount(1, $r->profiliBellaria());
    }

    public function test_consultazione_incompleta_impedisce_import_automatico_anche_con_un_documento_completo(): void
    {
        $g = app(GestioneProfiliTassa::class);
        $id = $g->archivio->scheda('Bellaria-Igea Marina', 2027);
        $s = $g->archivio->leggi($id);
        $s['verifica_completa'] = false;
        $g->archivio->scrivi($id, $s);
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $this->testo(), 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        try {
            $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
            $this->fail('Fonte incerta applicata');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('fonte', $e->errors());
        }
        $this->assertCount(1, $g->archivio->profiliBellaria());
    }

    public function test_nuovi_parametri_non_riscrivono_legacy_e_revisione_futura_preserva_export_e_versione_originale(): void
    {
        $g = app(GestioneProfiliTassa::class);
        $testo = str_replace('euro 1,50', 'euro 2,00', $this->testo());
        $d = $g->conserva('Bellaria-Igea Marina', 2027, $testo, 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
        $g->provaApplicazione('Bellaria-Igea Marina', 2027, $d);
        $originale = $g->archivio->profiliBellaria()[1];
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $legacy = \App\Models\TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-01-01', 'fine' => '2026-12-31', 'max_age_children' => 17, 'min_age_adult' => 18]);
        $primaLegacy = $legacy->fresh()->getAttributes();
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Fixture', 'surname' => 'Revisione', 'arrive' => '2027-06-01', 'departure' => '2027-06-10', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 12);
        $data = ['data_da' => '2027-06-01', 'data_a' => '2027-06-10'];
        $this->post('/tassa_di_soggiorno/rapporto/consolida', $data)->assertRedirect()->assertSessionHasNoErrors();
        $export = \App\Models\TassaExport::firstOrFail();
        $snapshot = $export->getRawOriginal('snapshot');
        $csv = $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->getContent();
        $nuovo = $g->conserva('Bellaria-Igea Marina', 2027, str_replace('euro 2,00', 'euro 2,50', $testo), 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/revisione', true);
        $g->applica('Bellaria-Igea Marina', 2027, $nuovo, $nuovo['analisi']['valori'], 'confermata dal Superadmin');
        $this->assertCount(3, $g->archivio->profiliBellaria(true));
        $this->assertCount(2, $g->archivio->profiliBellaria());
        $this->assertContains($originale, $g->archivio->profiliBellaria(true));
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 15);
        $this->assertSame($snapshot, $export->fresh()->getRawOriginal('snapshot'));
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->getContent());
        $this->assertSame($primaLegacy, $legacy->fresh()->getAttributes());
        $p26 = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Fixture', 'surname' => 'Storico', 'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->get('/schedine/'.$p26->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 3);
    }

    public function test_unita_precisione_categoria_superior_e_limiti_incompatibili_non_vengono_inventati(): void
    {
        $a = app(AnalisiDocumentoTassa::class);
        $this->assertArrayNotHasKey('tariffa_3', $a->estrai(str_replace('euro 1,50', 'euro 1,234', $this->testo()), 2027)['valori']);
        $this->assertArrayNotHasKey('tariffa_3', $a->estrai(str_replace('Alberghi 3 stelle euro 1,50', 'Alberghi 3 stelle superior euro 2,00', $this->testo()), 2027)['valori']);
        $this->assertStringContainsString('Unità', implode(' ', $a->estrai(str_replace('Tariffa per persona e per notte.', '', $this->testo()), 2027)['problemi']));
        $g = app(GestioneProfiliTassa::class);
        foreach ([str_replace('Massimo 6', 'Massimo 5', $this->testo()), $this->testo()."\nAlberghi 3 stelle superior euro 2,00", $this->testo()."\nTariffa per camera"] as $testo) {
            $d = $g->conserva('Bellaria-Igea Marina', 2027, $testo, 'testo', 'https://www.comune.bellaria-igea-marina.rn.it/norma', true);
            try {
                $g->applica('Bellaria-Igea Marina', 2027, $d, $d['analisi']['valori'], 'confermata dal Superadmin');
                $this->fail('Disposizione incompatibile applicata');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertArrayHasKey('configurazione', $e->errors());
            }
        }
        $this->assertCount(1, $g->archivio->profiliBellaria());
    }
}
