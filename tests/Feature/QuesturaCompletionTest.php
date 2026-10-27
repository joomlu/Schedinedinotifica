<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\GeoNazione;
use App\Models\QuesturaExport;
use App\Models\QuesturaTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\QuesturaTxtExportService;
use App\Services\QuesturaWebService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaCompletionTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function source(): Schedina
    {
        config(['questura.enabled' => true]);
        $s = $this->structureFor(null);
        $s->update(['questura_username' => 'ACCOUNT-SINTETICO', 'questura_password' => 'PASSWORD-SINTETICA', 'questura_wskey' => 'WSKEY-SINTETICA']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);

        return Schedina::create(['struttura_id' => $s->id, 'scheda' => 'QA-SINTETICA', 'circuito' => 'schedina', 'relationship' => '16', 'cant_people' => 1,
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(), 'surname' => 'Esempio', 'name' => 'Ospite', 'sex' => 'F',
            'oa_date_nac' => '1980-02-29', 'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777']);
    }

    private function period(): array
    {
        return ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
    }

    public function test_anteprima_privata_corrisponde_ai_byte_del_download_e_dello_storico(): void
    {
        $s = $this->source();
        $page = $this->get('/questura')->assertOk()->assertDontSee('Canale Questura disabilitato');
        $this->assertStringContainsString('no-store', (string) $page->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $page->headers->get('Cache-Control'));
        $txt = $page->viewData('preview_txt');
        $this->assertIsString($txt);
        $this->assertSame(168, mb_strlen($txt, 'UTF-8'));
        $hash = $page->viewData('preview_hash');
        $this->assertSame(hash('sha256', $txt), $hash);
        $download = $this->post('/questura/download/periodo', $this->period() + ['preview_hash' => $hash])->assertOk()->assertContent($txt);
        $export = QuesturaExport::where('struttura_id', $s->struttura_id)->sole();
        $this->assertSame($txt, Storage::disk('local')->get($export->path));
        $this->get('/questura/download/storico/'.$export->id)->assertStreamedContent($txt);
    }

    public function test_download_senza_anteprima_o_con_anteprima_superata_non_archivia(): void
    {
        $s = $this->source();
        $hash = hash('sha256', app(QuesturaTxtExportService::class)->buildTxtPerSchedina($s->fresh()));
        $this->post('/questura/download/periodo', $this->period())->assertRedirect()->assertSessionHasErrors('questura_preview');
        $s->update(['name' => 'Modificato']);
        $this->post('/questura/download/periodo', $this->period() + ['preview_hash' => $hash])->assertRedirect()->assertSessionHasErrors('questura_preview');
        $this->post('/questura/download/schedina/'.$s->id, ['preview_hash' => $hash])->assertRedirect()->assertSessionHasErrors('questura_preview');
        $this->assertSame(0, QuesturaExport::count());
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertSame([], Storage::disk('local')->allFiles('questura/struttura_'.$s->struttura_id));
    }

    public function test_ws_test_rifiuta_anteprima_superata_prima_del_client_e_dello_storico(): void
    {
        $s = $this->source();
        $hash = hash('sha256', app(QuesturaTxtExportService::class)->buildTxtPerSchedina($s->fresh()));
        $client = new class extends QuesturaWebService
        {
            public int $calls = 0;

            public function verify(Struttura $s, string $txt): array
            {
                $this->calls++;

                return ['state' => 'technical_error', 'mode' => 'test'];
            }
        };
        $this->app->instance(QuesturaWebService::class, $client);
        $s->update(['name' => 'Modificato']);
        $this->post('/questura/ws/verify', $this->period() + ['preview_hash' => $hash])->assertRedirect()->assertSessionHasErrors('questura_preview');
        $this->assertSame(0, $client->calls);
        $this->assertSame(0, QuesturaTransmission::count());
    }

    public function test_nomi_utf8_non_vengono_traslitterati_o_troncati_e_gli_offset_contano_caratteri(): void
    {
        $s = $this->source();
        $s->update(['surname' => 'Żółć', 'name' => 'Élodie']);
        $svc = app(QuesturaTxtExportService::class);
        $analysis = $svc->analizzaSchedine(collect([$s->fresh()]));
        $this->assertTrue($analysis->first()['valida'], json_encode($analysis->first()['errors']));
        $txt = $svc->buildTxt($analysis);
        $this->assertSame(168, mb_strlen($txt, 'UTF-8'));
        $this->assertGreaterThan(168, strlen($txt));
        $this->assertSame('ŻÓŁĆ'.str_repeat(' ', 46), mb_substr($txt, 14, 50, 'UTF-8'));
        $this->assertSame('ÉLODIE'.str_repeat(' ', 24), mb_substr($txt, 64, 30, 'UTF-8'));
        $this->assertSame('2', mb_substr($txt, 94, 1, 'UTF-8'));
        foreach ([str_repeat('É', 31), "Nome\nSeconda riga", "Nome\u{202E}"] as $invalid) {
            $s->name = $invalid;
            $this->assertFalse($svc->analizzaSchedine(collect([$s]))->first()['valida']);
        }
    }

    public function test_nascita_titolare_successiva_all_arrivo_blocca_il_txt(): void
    {
        $s = $this->source();
        $s->oa_date_nac = today()->addDay()->toDateString();
        $this->assertFalse(app(QuesturaTxtExportService::class)->analizzaSchedine(collect([$s]))->first()['valida']);
    }

    public function test_quantita_incoerente_blocca_il_txt(): void
    {
        $s = $this->source();
        $s->cant_people = 2;
        $this->assertFalse(app(QuesturaTxtExportService::class)->analizzaSchedine(collect([$s]))->first()['valida']);
    }

    public function test_nascita_componente_successiva_all_arrivo_blocca_il_txt(): void
    {
        $s = $this->source();
        $s->relationship = '17';
        $s->cant_people = 2;
        $s->setRelation('componenti', collect([new Componenti(['id' => 999, 'struttura_id' => $s->struttura_id, 'relationship' => '19', 'surname' => 'Minore', 'name' => 'Sintetico', 'sex' => 'M', 'date_nac' => today()->addDay()->toDateString(), 'country_nac' => '777', 'city_nac' => 'Francese'])]));
        $this->assertFalse(app(QuesturaTxtExportService::class)->analizzaSchedine(collect([$s]))->first()['valida']);
        $s->componenti->first()->date_nac = '2000-01-01';
        $this->assertTrue(app(QuesturaTxtExportService::class)->analizzaSchedine(collect([$s]))->first()['valida']);
    }

    public function test_anteprima_espone_impronta_e_testo_del_file_corrente(): void
    {
        $s = $this->source();
        $txt = app(QuesturaTxtExportService::class)->buildTxtPerSchedina($s->fresh());
        $page = $this->get('/questura')->assertOk();
        $this->assertSame($txt, $page->viewData('preview_txt'));
        $this->assertSame(hash('sha256', $txt), $page->viewData('preview_hash'));
    }

    public function test_download_con_anteprima_superata_rifiutato_prima_della_copia(): void
    {
        $s = $this->source();
        $hash = hash('sha256', app(QuesturaTxtExportService::class)->buildTxtPerSchedina($s->fresh()));
        $s->update(['name' => 'Modificato']);
        $this->post('/questura/download/periodo', $this->period() + ['preview_hash' => $hash])->assertRedirect()->assertSessionHasErrors('questura_preview');
        $this->assertSame(0, QuesturaExport::count());
    }

    public function test_periodo_malformato_viene_rifiutato_senza_errore500(): void
    {
        $this->source();
        $this->get('/questura?dal=non-una-data&al=2026-01-01')->assertRedirect()->assertSessionHasErrors('dal');
        $this->assertSame(0, QuesturaExport::count());
        $this->assertSame(0, QuesturaTransmission::count());
    }

    public function test_storico_finalizzato_senza_ricevuta_resta_consultabile_senza_falsa_accettazione(): void
    {
        $s = $this->source();
        $tx = QuesturaTransmission::create(['struttura_id' => $s->struttura_id, 'mode' => 'send', 'status' => 'sent']);
        \Illuminate\Support\Facades\DB::table('questura_transmissions')->where('id', $tx->id)->update(['finalized_at' => now()]);
        $export = QuesturaExport::create(['struttura_id' => $s->struttura_id, 'dal' => today(), 'al' => today(), 'filename' => 'legacy-sintetico.txt', 'path' => 'questura/legacy-sintetico.txt', 'sha256' => str_repeat('a', 64), 'status' => 'generated']);
        \Illuminate\Support\Facades\DB::table('questura_exports')->where('id', $export->id)->update(['finalized_at' => now()]);
        $this->get('/questura')->assertOk()->assertSee('ricevuta non collegata.')->assertDontSee('Accettato');
    }

    public function test_filtri_e_paginazione_non_cambiano_i_byte_del_periodo(): void
    {
        $s = $this->source();
        for ($i = 0; $i < 11; $i++) {
            $copy = $s->replicate();
            $copy->name = 'Ospite '.$i;
            $copy->save();
        }
        $full = $this->get('/questura')->assertOk();
        $filtered = $this->get('/questura?q=inesistente&page=2')->assertOk();
        $this->assertSame($full->viewData('preview_txt'), $filtered->viewData('preview_txt'));
        $this->assertSame(12, $filtered->viewData('preview_righe'));
        $this->assertSame(0, $filtered->viewData('analisi')->total());
        $this->assertSame(0, QuesturaExport::count());
    }

    public function test_storici_completi_indipendenti_e_scheda_persistente(): void
    {
        $s = $this->source();
        $other = $this->structureFor(null);
        foreach ([$s->struttura_id, $other->id] as $sid) {
            for ($i = 0; $i < 32; $i++) {
                QuesturaExport::create(['struttura_id' => $sid, 'dal' => today(), 'al' => today(), 'filename' => 'sintetico.txt', 'path' => 'questura/sintetico', 'sha256' => str_repeat('a', 64), 'status' => 'generated']);
                $tx = QuesturaTransmission::create(['struttura_id' => $sid, 'mode' => 'verify', 'status' => 'unknown']);
                \App\Models\QuesturaReceipt::create(['struttura_id' => $sid, 'questura_transmission_id' => $tx->id, 'remote_date' => today()->subDays($i + 1), 'filename' => 'sintetica.pdf', 'path' => 'questura/sintetica.pdf', 'mime' => 'application/pdf', 'sha256' => str_repeat('a', 64), 'byte_size' => 30]);
            }
        }
        $page = $this->get('/questura?exports_page=3&transmissions_page=2&receipts_page=3&tab=export')->assertOk();
        $this->assertSame(32, $page->viewData('storico')->total());
        $this->assertCount(2, $page->viewData('storico'));
        $this->assertSame(32, $page->viewData('trasmissioni')->total());
        $this->assertSame(2, $page->viewData('trasmissioni')->currentPage());
        $this->assertSame('export', $page->viewData('tab'));
        $this->assertSame(32, $page->viewData('ricevute')->total());
        $this->assertCount(2, $page->viewData('ricevute'));
        foreach (['storico', 'trasmissioni'] as $key) {
            foreach ($page->viewData($key) as $row) {
                $this->assertSame($s->struttura_id, $row->struttura_id);
            }
        }
        $page->assertSee('tab=export', false)->assertSee('tab=elettronico', false);
    }

    public function test_file_standard_non_supera_mille_alloggiati(): void
    {
        $s = $this->source();
        $service = app(QuesturaTxtExportService::class);
        $line = $service->buildTxtPerSchedina($s);
        $analysis = collect([['valida' => true, 'righe' => array_fill(0, 1000, $line)]]);
        $this->assertSame(1000, count(explode("\r\n", $service->buildTxt($analysis))));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->buildTxt(collect([['valida' => true, 'righe' => array_fill(0, 1001, $line)]]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tipiCollegati')]
    public function test_famiglia_e_gruppo_ordinano_capo_e_componenti_senza_documenti(string $capo, string $membro): void
    {
        $s = $this->source();
        $s->relationship = $capo;
        $s->cant_people = 2;
        $s->setRelation('componenti', collect([new Componenti(['id' => 999, 'struttura_id' => $s->struttura_id, 'relationship' => $membro, 'surname' => 'Esempio', 'name' => 'Componente', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => '777', 'city_nac' => 'Francese'])]));
        $txt = app(QuesturaTxtExportService::class)->buildTxtPerSchedina($s);
        $lines = explode("\r\n", $txt);
        $this->assertCount(2, $lines);
        $this->assertSame($capo, substr($lines[0], 0, 2));
        $this->assertSame($membro, substr($lines[1], 0, 2));
        $this->assertSame(str_repeat(' ', 34), substr($lines[1], 134, 34));
        $this->assertSame(338, strlen($txt));
        $this->assertFalse(str_ends_with($txt, "\r\n"));
    }

    public static function tipiCollegati(): array
    {
        return ['famiglia' => ['17', '19'], 'gruppo' => ['18', '20']];
    }

    public function test_canale_off_mostra_anteprima_in_lettura_e_disabilita_le_azioni_mutanti(): void
    {
        $this->source();
        config(['questura.enabled' => false]);
        $page = $this->get('/questura')->assertOk()->assertSee('Canale Questura disabilitato');
        $this->assertNotEmpty($page->viewData('preview_txt'));
        $dom = new \DOMDocument;
        @$dom->loadHTML($page->getContent());
        $xpath = new \DOMXPath($dom);
        foreach (['/download/periodo', '/ws/verify', '/ws/send', '/ws/tables'] as $suffix) {
            $this->assertSame(1, $xpath->query('//form[contains(@action,"'.$suffix.'")]//button[@disabled]')->length);
        }
        $this->assertSame(0, QuesturaExport::count());
        $this->assertSame(0, QuesturaTransmission::count());
    }

    public function test_filtri_non_testuali_vengono_rifiutati_senza_errore500(): void
    {
        $this->source();
        foreach (['q', 'filtro'] as $field) {
            $this->get('/questura?'.http_build_query([$field => ['valore']]))->assertRedirect()->assertSessionHasErrors($field);
        }
        $this->assertSame(0, QuesturaExport::count());
        $this->assertSame(0, QuesturaTransmission::count());
    }

    public function test_codici_remoti_non_ricevono_significati_non_documentati(): void
    {
        $s = $this->source();
        QuesturaTransmission::create(['struttura_id' => $s->struttura_id, 'mode' => 'verify', 'status' => 'rejected', 'result' => ['state' => 'rejected', 'mode' => 'test', 'valid_rows' => 0, 'row_errors' => [['row' => 1, 'code' => '11'], ['row' => 2, 'code' => '12']]]]);
        $this->get('/questura')->assertOk()->assertSee('codice 11; verificare l’esito nel portale')->assertSee('codice 12; verificare l’esito nel portale')->assertDontSee('formato non corretto')->assertDontSee('campo non corretto');
    }
    public function test_verifica_positiva_riguarda_tutte_le_righe_e_non_attesta_accettazione(): void
    {
        $s = $this->source();
        foreach ([2, 1] as $valid) {
            QuesturaTransmission::create(['struttura_id' => $s->struttura_id, 'mode' => 'verify', 'status' => 'unknown', 'righe_count' => 2, 'result' => ['state' => 'unknown', 'mode' => 'test', 'valid_rows' => $valid, 'row_errors' => []]]);
        }
        $page = $this->get('/questura')->assertOk()->assertSee('Il Test non costituisce l’invio ufficiale.');
        $this->assertSame(1, substr_count($page->getContent(), 'Verifica positiva'));
        foreach (QuesturaTransmission::all() as $tx) { $this->assertSame('unknown', $tx->status); $this->assertFalse($tx->esitoSicuro()['accepted']); }
    }

}
