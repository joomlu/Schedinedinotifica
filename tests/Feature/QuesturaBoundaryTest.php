<?php

namespace Tests\Feature;

use App\Models\{Struttura, Schedina, Componenti, GeoNazione, QuesturaExport, QuesturaTransmission};
use App\Services\{QuesturaTxtExportService, QuesturaWebService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaBoundaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['questura.enabled' => true]);
    }

    use RefreshDatabase, StrutturaFixtures;

    private function schedina(): Schedina
    {
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $s = new Schedina([
            'id' => 900, 'struttura_id' => 91, 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => now()->toDateString(), 'departure' => now()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => 'Ospite', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT',
            'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
        $s->setRelation('componenti', collect());
        return $s;
    }

    public function test_record_offset_codici_newline_determinismo(): void
    {
        $s = $this->schedina();
        $service = new QuesturaTxtExportService();
        $txt = $service->buildTxtPerSchedina($s);
        $this->assertSame(168, strlen($txt));
        $this->assertSame('16', substr($txt, 0, 2));
        $this->assertSame(now()->format('d/m/Y'), substr($txt, 2, 10));
        $this->assertSame('02', substr($txt, 12, 2));
        $this->assertSame(str_pad('ESEMPIO', 50), substr($txt, 14, 50));
        $this->assertSame('2', substr($txt, 94, 1));
        $this->assertSame('29/02/1980', substr($txt, 95, 10));
        $this->assertSame(str_repeat(' ', 11), substr($txt, 105, 11));
        $this->assertSame('100000215', substr($txt, 116, 9));
        $this->assertSame('100000215', substr($txt, 125, 9));
        $this->assertSame('IDENT', substr($txt, 134, 5));
        $this->assertSame('100000215', substr($txt, 159, 9));
        $this->assertSame($txt, $service->buildTxtPerSchedina($s));
        $accented = clone $s; $accented->name = 'Élodie'; $accented->surname = "D’Angelo";
        $accentTxt = $service->buildTxtPerSchedina($accented);
        $this->assertSame(str_pad('ELODIE', 30), substr($accentTxt, 64, 30));
        $this->assertSame(str_pad("D'ANGELO", 50), substr($accentTxt, 14, 50));
        $tooLong = clone $s; $tooLong->or_doc = str_repeat('X', 21);
        $this->assertFalse($service->analizzaSchedine(collect([$tooLong]))->first()['valida']);
        $unsupported = clone $s; $unsupported->name = '李John';
        $this->assertFalse($service->analizzaSchedine(collect([$unsupported]))->first()['valida']);
        $s->relationship = '17';
        $s->setRelation('componenti', collect([new Componenti(['id' => 10, 'struttura_id' => 91, 'relationship' => '19', 'surname' => 'Esempio', 'name' => 'Familiare', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => '777', 'city_nac' => 'Francese'])]));
        $txt = $service->buildTxtPerSchedina($s);
        $this->assertSame(338, strlen($txt));
        $this->assertSame("\r\n", substr($txt, 168, 2));
        $this->assertSame(str_repeat(' ', 34), substr($txt, 304, 34));
        $this->assertFalse(str_ends_with($txt, "\r\n"));
    }

    public function test_rifiuta_vuoti_date_impossibili_e_periodi_invertiti(): void
    {
        $s = $this->schedina();
        $service = new QuesturaTxtExportService();
        foreach (['name' => '', 'oa_date_nac' => '1980-02-31', 'departure' => now()->subDay()->toDateString()] as $field => $value) {
            $copy = clone $s; $copy->{$field} = $value;
            $this->assertFalse($service->analizzaSchedine(collect([$copy]))->first()['valida']);
        }
    }

    public function test_guardrail_non_apre_soap_in_testing_anche_con_config_abilitata(): void
    {
        config(['questura.enabled' => true]);
        $ws = new class extends QuesturaWebService {
            protected function makeClient(): \SoapClient { throw new \LogicException('Il trasporto non deve essere aperto.'); }
        };
        $s = new Struttura(['questura_ws_simulazione' => false]);
        $this->assertSame('simulation', $ws->send($s, 'SINTETICO')['state']);
        $this->assertSame('simulation', $ws->verify($s, 'SINTETICO')['state']);
    }

    public function test_download_originale_hash_e_tenant(): void
    {
        $mine = $this->structureFor(null); $other = $this->structureFor(null);
        $bytes = 'TXT-SINTETICO';
        Storage::disk('local')->put('questura/test.txt', $bytes);
        $export = QuesturaExport::create(['struttura_id' => $mine->id, 'dal' => now(), 'al' => now(), 'filename' => 'test.txt', 'path' => 'questura/test.txt', 'sha256' => hash('sha256', $bytes), 'byte_size' => strlen($bytes)]);
        $response = $this->actingAs($this->actor('struttura_user', null, $mine->id))->get('/questura/download/storico/'.$export->id)->assertOk()->assertDownload('test.txt');
        $this->assertSame($bytes, $response->streamedContent());
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id])->get('/questura/download/storico/'.$export->id)->assertNotFound();
        $this->get('/questura/download/storico/'.$export->id.'?sid='.$mine->id)->assertForbidden();
        $this->actingAs($this->actor('struttura_user', null, $mine->id))->withSession(['struttura_corrente_id' => $mine->id]);
        Storage::disk('local')->put('questura/test.txt', 'ALTERATO');
        $this->get('/questura/download/storico/'.$export->id)->assertStatus(409);
    }

    public function test_snapshot_immutabile_e_deduplicazione_atomica(): void
    {
        $data = ['struttura_id' => null, 'mode' => 'send', 'status' => 'in_progress', 'send_key' => hash('sha256', 'sintetico'), 'payload' => ['txt_base64' => base64_encode('ORIGINALE')]];
        $tx = QuesturaTransmission::create($data);
        $tx->update(['status' => 'uncertain']);
        $this->assertSame('ORIGINALE', base64_decode($tx->fresh()->payload['txt_base64']));
        try { $tx->update(['payload' => ['txt_base64' => 'ALTERATO']]); $this->fail('Snapshot riscritto'); }
        catch (\LogicException) { $this->assertSame('ORIGINALE', base64_decode($tx->fresh()->payload['txt_base64'])); }
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        QuesturaTransmission::create($data);
    }
    public function test_ricevuta_archiviazione_download_e_diniego_cross_tenant(): void
    {
        $mine = $this->structureFor(null); $other = $this->structureFor(null);
        $pdf = "%PDF-1.4\nSINTETICO\n%%EOF";
        $service = new class($pdf) extends QuesturaWebService {
            public int $calls = 0;
            public function __construct(private string $pdf) {}
            public function receipt(Struttura $s, \Carbon\Carbon $date): array {
                $this->calls++;
                return ['state' => 'receipt_available', 'bytes' => $this->pdf, 'mime' => 'application/pdf'];
            }
        };
        $this->app->instance(QuesturaWebService::class, $service);
        $tx = QuesturaTransmission::create(['struttura_id' => $mine->id, 'mode' => 'send', 'status' => 'sent', 'executed_at' => now()->subDay()]);
        $this->actingAs($this->actor('struttura_user', null, $mine->id));
        $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
        $this->get('/questura/ws/receipt/'.$tx->id)->assertOk()->assertContent($pdf);
        $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
        $this->assertSame(1, $service->calls);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        $this->post('/questura/ws/receipt/'.$tx->id)->assertNotFound();
        $this->get('/questura/ws/receipt/'.$tx->id)->assertNotFound();
        $this->assertSame(1, $service->calls);
    }

    public function test_cestino_non_serializza_segreti_questura(): void
    {
        $model = new Struttura(['questura_password' => 'sintetica', 'questura_wskey' => 'sintetica']);
        $method = new \ReflectionMethod(\App\Services\CestinoService::class, 'minimizeRestorePayload');
        $payload = $method->invoke(new \App\Services\CestinoService(), $model, ['questura_password' => 'legacy-sintetica', 'questura_wskey' => 'legacy-sintetica', 'nome_struttura' => 'Fixture']);
        $this->assertArrayNotHasKey('questura_password', $payload);
        $this->assertArrayNotHasKey('questura_wskey', $payload);
    }
    public function test_generazione_storico_immutabile_e_tentativo_prima_del_trasporto(): void
    {
        $mine = $this->structureFor(null);
        $mine->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $mine->id));
        $s = $this->schedina(); $s->struttura_id = $mine->id; $s->circuito = 'schedina'; $s->save();
        $txt = (new QuesturaTxtExportService())->buildTxtPerSchedina($s);
        $response = $this->post('/questura/download/schedina/'.$s->id)->assertOk()->assertContent($txt);
        $export = QuesturaExport::where('struttura_id', $mine->id)->firstOrFail();
        $this->assertSame(hash('sha256', $txt), $export->sha256);
        $this->assertSame('generated', $export->status);
        $this->post('/questura/ws/verify', ['dal' => now()->toDateString(), 'al' => now()->toDateString()])->assertRedirect();
        $tx = QuesturaTransmission::where('struttura_id', $mine->id)->firstOrFail();
        $this->assertSame($txt, base64_decode($tx->payload['txt_base64']));
        $this->assertSame('simulation', $tx->status);
        $this->assertSame(['in_progress', 'simulation'], \Illuminate\Support\Facades\DB::table('questura_transmission_events')->where('questura_transmission_id', $tx->id)->orderBy('id')->pluck('status')->all());
        $s->update(['name' => 'Nuovo']);
        $this->get('/questura/download/storico/'.$export->id)->assertOk();
        $this->assertSame($txt, $this->get('/questura/download/storico/'.$export->id)->streamedContent());
        $this->get('/questura/ws/payload/'.$tx->id)->assertOk()->assertContent($txt);
        $this->post('/questura/ws/verify')->assertRedirect();
        $this->post('/questura/ws/send')->assertRedirect();
        $this->post('/questura/ws/send')->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
        $this->get('/questura/ws/send')->assertNotFound();
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
        $this->get('/questura/download/schedina/'.$s->id)->assertNotFound();
        $this->assertSame(1, QuesturaExport::count());
        $this->post('/questura/ws/send', ['_token' => 'errato'])->assertStatus(419);
    }

    public function test_comune_questura_distinto_da_id_geo_e_istat(): void
    {
        $s = $this->schedina();
        $nation = GeoNazione::forceCreate(['id' => 888, 'nome' => 'Italia', 'codice_iso2' => 'IT', 'cittadinanza' => 'Italiana', 'is_italia' => true]);
        $region = \App\Models\GeoRegione::create(['geo_nazione_id' => $nation->id, 'codice_regione' => 'SYN', 'nome' => 'Lazio']);
        $province = \App\Models\GeoProvincia::create(['geo_regione_id' => $region->id, 'sigla' => 'RM', 'nome' => 'Roma']);
        $city = \App\Models\GeoComune::create(['geo_provincia_id' => $province->id, 'codice_istat' => '058091', 'nome' => 'Roma']);
        $s->oa_country = (string) $nation->id; $s->oa_prov = (string) $province->id; $s->oa_city = (string) $city->id;
        $s->or_published_country = (string) $nation->id; $s->or_published_city = (string) $city->id;
        $txt = (new QuesturaTxtExportService())->buildTxtPerSchedina($s);
        $this->assertSame('412058091', substr($txt, 105, 9));
        $this->assertSame('RM', substr($txt, 114, 2));
        $this->assertSame('100000100', substr($txt, 116, 9));
        $this->assertSame('412058091', substr($txt, 159, 9));
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('scenariCiclo')]
    public function test_ciclo_ws_sintetico_e_crash_dopo_send(string $scenario): void
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $db = \Illuminate\Support\Facades\DB::class;
        $level = $db::transactionLevel();
        $mine = $this->structureFor(null);
        $mine->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $actor = $this->actor('struttura_user', null, $mine->id); $this->actingAs($actor);
        $s = $this->schedina(); $s->struttura_id = $mine->id; $s->circuito = 'schedina'; $s->save();
        $client = \App\Models\Customers::create(['struttura_id' => $mine->id, 'name' => 'Cliente sintetico', 'surname' => 'Esempio']);
        $clientBefore = $client->fresh()->getRawOriginal();
        $txt = (new QuesturaTxtExportService())->buildTxtPerSchedina($s);
        $this->post('/questura/download/schedina/'.$s->id)->assertOk()->assertContent($txt);
        $export = QuesturaExport::firstOrFail();
        $double = new class extends QuesturaSoapDouble {
            public $observe;
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed {
                if (in_array($name, ['Test', 'Send'], true)) { ($this->observe)($name, $args[0]); }
                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $double->observe = function ($name, $params) use ($txt, $level, $db) {
            $this->assertSame($level, $db::transactionLevel(), 'Transazione applicativa aperta durante SOAP');
            $tx = QuesturaTransmission::latest('id')->firstOrFail();
            $this->assertSame('in_progress', $tx->status);
            $this->assertSame($txt, base64_decode($tx->payload['txt_base64'], true));
            $this->assertSame(['string' => [$txt]], $params['ElencoSchedine']);
        };
        $ws = new class($double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        };
        $this->app->instance(QuesturaWebService::class, $ws);
        $period = ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
        $this->post('/questura/ws/verify', $period)->assertRedirect();
        $this->assertNotContains('Send', array_column($double->calls, 0));
        $this->assertSame(0, \App\Models\QuesturaReceipt::count());
        $this->assertTrue(Storage::disk('local')->exists($export->path));
        if ($scenario === 'timeout') { $double->fail = 'Send'; }
        if ($scenario === 'crash') {
            QuesturaTransmission::updating(function ($tx) { if ($tx->mode === 'send') { throw new \RuntimeException('CRASH DB SINTETICO'); } });
        }
        $this->post('/questura/ws/send', $period)->assertStatus($scenario === 'crash' ? 500 : 302);
        if ($scenario === 'crash') { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertSame(['successo' => 'sent', 'timeout' => 'uncertain', 'crash' => 'in_progress'][$scenario], $tx->status);
        $this->assertNotNull($tx->payload); $this->assertNull($tx->finalized_at);
        $this->post('/questura/ws/send', $period)->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, count(array_filter(array_column($double->calls, 0), fn ($n) => $n === 'Send')));
        $schedinaBefore = $s->fresh()->getRawOriginal();
        \Carbon\Carbon::setTestNow(now()->addDay());
        try {
            $double->fail = null;
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $r = \App\Models\QuesturaReceipt::firstOrFail();
            $this->get('/questura/ricevute/'.$r->id)->assertOk()->assertContent("%PDF-1.4\nSINTETICO\n%%EOF");
            if ($scenario !== 'successo') {
                $this->assertNotNull($tx->fresh()->payload);
                $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertRedirect();
                $this->assertNotNull($tx->fresh()->reconciled_at);
            }
            $this->assertNull($tx->fresh()->payload); $this->assertNotNull($tx->fresh()->payload_deleted_at);
            $this->assertFalse(Storage::disk('local')->exists($export->path));
            $this->assertSame(hash('sha256', $txt), $tx->fresh()->sha256);
            $this->assertSame($schedinaBefore, $s->fresh()->getRawOriginal());
            $this->assertSame($clientBefore, $client->fresh()->getRawOriginal());
        } finally { \Carbon\Carbon::setTestNow(); }
    }

    public function test_matrice_invalidi_blocca_prima_del_trasporto(): void
    {
        $mine = $this->structureFor(null);
        $mine->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $mine->id));
        $s = $this->schedina(); $s->struttura_id = $mine->id; $s->circuito = 'schedina'; $s->save();
        $original = $s->getRawOriginal();
        $ws = new class extends QuesturaWebService {
            public int $calls = 0;
            public function send(Struttura $s, string $txt): array { $this->calls++; throw new \LogicException('Trasporto vietato per elenco invalido'); }
        };
        $this->app->instance(QuesturaWebService::class, $ws);
        $cases = [
            ['name' => ''], ['relationship' => '17'], ['relationship' => '18'],
            ['oa_country' => 'STATO INESISTENTE'], ['or_doc' => ''],
            ['oa_country' => 'ITALIA', 'oa_prov' => 'RM', 'oa_city' => 'COMUNE INESISTENTE'],
            ['name' => '李'], ['oa_date_nac' => '1980-02-31'],
            ['departure' => today()->addDays(31)->toDateString()], ['or_doctype' => 'CODICE INESISTENTE'],
        ];
        $sourceService = new class extends QuesturaTxtExportService {
            public ?Schedina $invalid = null;
            public function schedinePerPeriodo(int $strutturaId, \Carbon\Carbon $dal, \Carbon\Carbon $al): \Illuminate\Support\Collection {
                return $this->invalid ? collect([$this->invalid]) : parent::schedinePerPeriodo($strutturaId, $dal, $al);
            }
        };
        $this->app->instance(QuesturaTxtExportService::class, $sourceService);
        foreach ($cases as $change) {
            $sourceService->invalid = null;
            \Illuminate\Support\Facades\DB::table('schedina')->where('id', $s->id)->update(array_intersect_key($original, array_flip(['name', 'relationship', 'oa_country', 'oa_prov', 'oa_city', 'or_doc', 'oa_date_nac', 'departure', 'or_doctype'])));
            if (isset($change['oa_date_nac'])) {
                // La colonna DATE rifiuta già date impossibili: inject della sola sorgente
                // in memoria, preservando validazione/mapping/controller/trasporto reali.
                $invalid = clone $s; $invalid->oa_date_nac = $change['oa_date_nac'];
                $sourceService->invalid = $invalid;
            } else { \Illuminate\Support\Facades\DB::table('schedina')->where('id', $s->id)->update($change); }
            $this->post('/questura/ws/send', ['dal' => today()->toDateString(), 'al' => today()->toDateString()])->assertRedirect()->assertSessionHasErrors();
            $this->assertSame(0, $ws->calls); $this->assertSame(0, QuesturaTransmission::count());
        }
        $s->relationship = '17';
        $s->setRelation('componenti', collect([new Componenti(['struttura_id' => $mine->id + 1000, 'relationship' => '19', 'name' => 'Sintetico', 'surname' => 'Esempio', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => '777', 'city_nac' => 'Francese'])]));
        $this->assertFalse((new QuesturaTxtExportService())->analizzaSchedine(collect([$s]))->first()['valida']);
        $this->assertSame(0, $ws->calls);
    }

    public function test_autenticazione_e_manutenzione_bloccano_azioni_questura(): void
    {
        $this->post('/questura/ws/send')->assertRedirect(route('login'));
        $this->assertSame(0, QuesturaTransmission::count());
        $s = $this->structureFor(null); $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->app->maintenanceMode()->activate(['time' => time(), 'retry' => 60]);
        try {
            $this->post('/questura/ws/send')->assertStatus(503);
            $this->get('/questura')->assertStatus(503);
            $this->assertSame(0, QuesturaTransmission::count());
        } finally { $this->app->maintenanceMode()->deactivate(); }
    }

    public static function scenariCiclo(): array { return [['successo'], ['timeout'], ['crash']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('scenariReinvioModificato')]
    public function test_schedina_gia_inviata_non_riparte_con_hash_diverso(string $scenario): void
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $structure = $this->structureFor(null);
        $structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        $s = $this->schedina(); $s->struttura_id = $structure->id; $s->circuito = 'schedina'; $s->save();
        $double = new QuesturaSoapDouble(); $double->fail = $scenario === 'incerto' ? 'Send' : null;
        $this->app->instance(QuesturaWebService::class, new class($double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
        $period = ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
        $this->post('/questura/ws/verify', $period)->assertRedirect();
        $this->post('/questura/ws/send', $period)->assertRedirect();
        $first = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertSame($scenario === 'incerto' ? 'uncertain' : 'sent', $first->status);
        if ($scenario === 'concluso') {
            \Carbon\Carbon::setTestNow(now()->addDay());
            try {
                $this->post('/questura/ws/receipt/'.$first->id)->assertRedirect();
                $this->assertNotNull($first->fresh()->finalized_at);
                $this->assertNull($first->fresh()->payload);
            } finally { \Carbon\Carbon::setTestNow(); }
        }
        $s->update(['name' => 'Correzione sintetica']);
        $double->fail = null;
        $this->post('/questura/ws/verify', $period)->assertRedirect();
        $this->post('/questura/ws/send', $period)->assertRedirect();
        $second = QuesturaTransmission::where('mode', 'send')->latest('id')->firstOrFail();
        // Nessuna asserzione viene ridotta per accettare un reinvio: è il gate richiesto.
        $this->assertSame(1, count(array_filter(array_column($double->calls, 0), fn ($name) => $name === 'Send')),
            'La stessa Schedina ha prodotto un secondo Send dopo modifica del TXT, senza riconciliazione/autorizzazione di reinvio.');
        $this->assertSame($first->id, $second->id);
    }

    public static function scenariReinvioModificato(): array { return [['incerto'], ['concluso']]; }

}
