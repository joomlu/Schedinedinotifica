<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaReceipt, QuesturaTransmission, Schedina, Struttura};
use App\Services\{QuesturaRetentionService, QuesturaWebService};
use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Log, Storage};
use Monolog\Handler\TestHandler;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaLoggingPrivacyTest extends TestCase
{
    use RefreshDatabase { beginDatabaseTransaction as private beginFixtureTransaction; }
    use StrutturaFixtures;

    private Struttura $structure;
    private Schedina $source;
    private QuesturaSoapDouble $soap;
    private TestHandler $records;
    private string $txt = '';
    private string $sensitive = '';

    public function beginDatabaseTransaction(): void
    {
        $trigger = match ($this->name()) {
            'test_guasto_db_reservation_annulla_tentativo_senza_rete_o_copie_nei_log' => "BEFORE INSERT ON questura_send_reservations FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NomePrivacySintetico PASSWORD-Q3-SINTETICA'",
            'test_guasto_db_dopo_send_preserva_riserva_e_blocca_retry_senza_log_personali' => "BEFORE UPDATE ON questura_transmissions FOR EACH ROW BEGIN IF NEW.mode = 'send' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NomePrivacySintetico PASSWORD-Q3-SINTETICA'; END IF; END",
            'test_guasto_db_archivio_ricevuta_elimina_solo_file_effimero_e_non_logga_dati' => "BEFORE INSERT ON questura_receipts FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NomePrivacySintetico PASSWORD-Q3-SINTETICA'",
            'test_guasto_db_riconciliazione_preserva_snapshot_e_riserva_senza_log_personali' => "BEFORE UPDATE ON questura_transmissions FOR EACH ROW BEGIN IF NEW.payload IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NomePrivacySintetico PASSWORD-Q3-SINTETICA'; END IF; END",
            default => null,
        };
        if ($trigger) { DB::unprepared('CREATE TRIGGER questura_privacy_failure '.$trigger); }
        $this->beginFixtureTransaction();
        if ($trigger) { $this->beforeApplicationDestroyed(fn () => DB::unprepared('DROP TRIGGER questura_privacy_failure')); }
    }

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $this->structure = $this->structureFor(null);
        $this->structure->update(['questura_username' => 'UTENTE-Q3-SINTETICO', 'questura_password' => 'PASSWORD-Q3-SINTETICA', 'questura_wskey' => 'WSKEY-Q3-SINTETICA']);
        $this->actingAs($this->actor('struttura_user', null, $this->structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $this->source = Schedina::create([
            'struttura_id' => $this->structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'CognomePrivacySintetico', 'name' => 'NomePrivacySintetico', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'DOC-Q3-SINTETICO', 'or_published_country' => '777',
        ]);
        $this->soap = new class extends QuesturaSoapDouble {
            public string $sensitive = '';
            public bool $timeout = false;
            public bool $invalidPdf = false;
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if ($name === $this->fail) {
                    $this->calls[] = [$name, $args[0]];
                    if ($this->timeout) { throw new \RuntimeException($this->sensitive); }
                    throw new \SoapFault('Server', $this->sensitive);
                }
                if ($name === 'Ricevuta' && $this->invalidPdf) {
                    $this->calls[] = [$name, $args[0]];
                    return (object)['RicevutaResult' => (object)['esito' => true], 'PDF' => $this->sensitive];
                }
                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $this->app->instance(QuesturaWebService::class, new class($this->soap) extends QuesturaWebService {
            public function __construct(private \SoapClient $soap) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->soap; }
        });
        config(['logging.default' => 'single', 'logging.channels.single.path' => storage_path('logs/q3-'.md5($this->nameWithDataSet()).'.log')]);
        Log::forgetChannel('single');
        $this->records = new TestHandler();
        Log::channel('single')->getLogger()->pushHandler($this->records);
        QuesturaPrivacyException::$calls = 0;
    }

    private function period(): array { return ['dal' => $this->source->arrive, 'al' => $this->source->arrive]; }
    private function verify(): void
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'verify')->firstOrFail();
        $this->txt = base64_decode($tx->payload['txt_base64'], true);
        $this->sensitive = $this->txt.' '.base64_encode($this->txt).' NomePrivacySintetico CognomePrivacySintetico DOC-Q3-SINTETICO 1980-02-29 INDIRIZZO-Q3-SINTETICO UTENTE-Q3-SINTETICO PASSWORD-Q3-SINTETICA WSKEY-Q3-SINTETICA TOKEN-Q3-SINTETICO CERTIFICATO-Q3-SINTETICO HEADER-Q3-SINTETICO';
        $this->soap->sensitive = $this->sensitive;
    }
    private function sendCount(): int { return count(array_filter($this->soap->calls, fn ($c) => $c[0] === 'Send')); }
    private function safe(string $response = ''): void
    {
        $log = file_exists(config('logging.channels.single.path')) ? file_get_contents(config('logging.channels.single.path')) : '';
        $events = DB::table('questura_transmission_events')->pluck('result')->implode('');
        foreach ([$response, $log, $events, json_encode($this->records->getRecords())] as $surface) {
            foreach (array_filter([$this->txt, base64_encode($this->txt), 'NomePrivacySintetico', 'CognomePrivacySintetico', 'NOMEPRIVACYSINTETICO', 'COGNOMEPRIVACYSINTETICO', 'DOC-Q3-SINTETICO', '1980-02-29', '29021980', 'INDIRIZZO-Q3-SINTETICO', 'UTENTE-Q3-SINTETICO', 'PASSWORD-Q3-SINTETICA', 'WSKEY-Q3-SINTETICA', 'TOKEN-Q3-SINTETICO', 'TOKEN-SINTETICO', 'CERTIFICATO-Q3-SINTETICO', 'HEADER-Q3-SINTETICO']) as $value) {
                // In caso di fallimento non stampare copie del payload nel diff.
                $this->assertFalse(str_contains($surface, $value), 'Dato personale o segreto presente nella superficie diagnostica.');
            }
        }
        foreach ($this->records->getRecords() as $record) {
            $this->assertArrayNotHasKey('exception', $record->context);
            foreach ($record->context as $value) { $this->assertIsString($value); }
        }
        $this->assertTrue(Schedina::whereKey($this->source->id)->exists());
    }
    private function technicalLog(): void
    {
        $records = $this->records->getRecords();
        $this->assertNotEmpty($records);
        $this->assertSame('Errore nel ciclo Questura.', end($records)->message);
        $context = end($records)->context;
        $this->assertSame(['error_code', 'operation', 'exception_class'], array_keys($context));
        $this->assertSame('QUESTURA_WORKFLOW_ERROR', $context['error_code']);
        $this->assertNotEmpty($context['operation']);
        $this->assertNotEmpty($context['exception_class']);
    }

    public function test_guasto_db_reservation_annulla_tentativo_senza_rete_o_copie_nei_log(): void
    {
        $this->verify();
        $response = $this->post('/questura/ws/send', $this->period())->assertStatus(500);
        $this->assertSame(0, $this->sendCount());
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->technicalLog(); $this->safe($response->getContent());
    }

    public function test_guasto_db_dopo_send_preserva_riserva_e_blocca_retry_senza_log_personali(): void
    {
        $this->verify();
        $response = $this->post('/questura/ws/send', $this->period())->assertStatus(500);
        $this->assertSame(1, $this->sendCount());
        $this->assertSame('in_progress', QuesturaTransmission::where('mode', 'send')->firstOrFail()->status);
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $this->post('/questura/ws/send', $this->period())->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, $this->sendCount());
        $this->technicalLog(); $this->safe($response->getContent());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderCases')]
    public function test_eccezione_pre_rete_non_serializza_message_context_previous_o_report(bool $debug, bool $json): void
    {
        $this->verify(); config(['app.debug' => $debug]);
        $sensitive = $this->sensitive;
        QuesturaTransmission::creating(function ($tx) use ($sensitive) {
            if ($tx->mode === 'send') { throw new QuesturaPrivacyException($sensitive, 0, new \RuntimeException($sensitive)); }
        });
        try {
            $response = $this->withHeaders($json ? ['Accept' => 'application/json'] : [])->post('/questura/ws/send', $this->period())->assertStatus(500);
        } finally { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $this->assertSame(0, QuesturaPrivacyException::$calls);
        $this->assertSame(0, $this->sendCount());
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        if ($json) { $response->assertJsonPath('error_code', 'QUESTURA_WORKFLOW_ERROR'); }
        $this->technicalLog(); $this->safe($response->getContent());
    }
    public static function renderCases(): array { return [[false, false], [false, true], [true, false], [true, true]]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('httpFailureCases')]
    public function test_eccezione_http_server_non_espone_catena_con_debug_attivo(int $status): void
    {
        $this->verify(); config(['app.debug' => true]);
        $sensitive = $this->sensitive;
        QuesturaTransmission::creating(function ($tx) use ($sensitive, $status) {
            if ($tx->mode === 'send') { throw new \Symfony\Component\HttpKernel\Exception\HttpException($status, $sensitive, new \RuntimeException($sensitive)); }
        });
        try { $response = $this->post('/questura/ws/send', $this->period())->assertStatus($status); }
        finally { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $this->assertSame(0, $this->sendCount());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->safe($response->getContent());
    }
    public static function httpFailureCases(): array { return [[500], [503]]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('transportCases')]
    public function test_eccezioni_trasporto_e_timeout_non_registrano_payload_o_segreti(string $case): void
    {
        $this->verify();
        $receipt = str_starts_with($case, 'receipt');
        if ($receipt) { $this->post('/questura/ws/send', $this->period())->assertRedirect(); }
        $this->soap->fail = match ($case) { 'pre_send' => 'GenerateToken', 'receipt_fault' => 'Ricevuta', default => 'Send' };
        $this->soap->timeout = $case === 'timeout';
        $this->soap->invalidPdf = $case === 'receipt_parse';
        if ($case === 'receipt_parse') { $this->soap->fail = null; }
        if ($receipt) {
            $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
            Carbon::setTestNow(now()->addDay());
            try { $response = $this->post('/questura/ws/receipt/'.$tx->id)->assertSessionHasErrors('questura_ws'); }
            finally { Carbon::setTestNow(); }
            $this->assertSame(0, QuesturaReceipt::count());
            $this->assertNotNull($tx->fresh()->payload);
        } else {
            $response = $this->post('/questura/ws/send', $this->period())->assertRedirect();
            $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
            $this->assertSame($case === 'pre_send' ? 'technical_error' : 'uncertain', $tx->status);
            $this->assertSame($case === 'pre_send' ? 0 : 1, $this->sendCount());
            $this->assertSame($case === 'pre_send' ? 0 : 1, DB::table('questura_send_reservations')->count());
            $this->assertFalse($tx->result['accepted']);
        }
        $this->safe($response->getContent());
    }
    public static function transportCases(): array { return [['pre_send'], ['soap_fault'], ['timeout'], ['receipt_fault'], ['receipt_parse']]; }

    public function test_guasto_db_archivio_ricevuta_elimina_solo_file_effimero_e_non_logga_dati(): void
    {
        $this->verify(); $this->post('/questura/ws/send', $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        Carbon::setTestNow(now()->addDay());
        try { $response = $this->post('/questura/ws/receipt/'.$tx->id)->assertStatus(500); }
        finally { Carbon::setTestNow(); }
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertSame([], Storage::disk('local')->allFiles('questura/struttura_'.$this->structure->id.'/ricevute'));
        $this->assertNotNull($tx->fresh()->payload);
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $this->technicalLog(); $this->safe($response->getContent());
    }

    public function test_guasto_db_riconciliazione_preserva_snapshot_e_riserva_senza_log_personali(): void
    {
        $this->verify(); $this->post('/questura/ws/send', $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        DB::table('questura_transmissions')->where('id', $tx->id)->update(['status' => 'uncertain']);
        Carbon::setTestNow(now()->addDay());
        try {
            $receipt = app(QuesturaRetentionService::class)->archiveReceipt($this->structure, now()->subDay(), "%PDF-1.4\nSINTETICO\n%%EOF", $tx->id);
            $response = $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertStatus(500);
        } finally { Carbon::setTestNow(); }
        $this->assertNotNull($tx->fresh()->payload); $this->assertNull($tx->fresh()->finalized_at);
        $this->assertTrue(Storage::disk('local')->exists($receipt->path));
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $this->technicalLog(); $this->safe($response->getContent());
    }

    public function test_reporting_console_retention_senza_route_non_serializza_eccezione(): void
    {
        $this->verify(); $this->get('/login');
        $sensitive = $this->sensitive;
        QuesturaReceipt::creating(fn () => throw new QuesturaPrivacyException($sensitive, 0, new \RuntimeException($sensitive)));
        try {
            app(QuesturaRetentionService::class)->archiveReceipt($this->structure, now()->subDay(), "%PDF-1.4\nSINTETICO\n%%EOF", null);
            $this->fail('Guasto fixture non osservato');
        } catch (QuesturaPrivacyException $e) {
            $handler = app(ExceptionHandler::class); $handler->report($e);
            $output = new BufferedOutput(); $handler->renderForConsole($output, $e);
            $this->safe($output->fetch());
        } finally { QuesturaReceipt::flushEventListeners(); QuesturaReceipt::clearBootedModels(); }
        $this->assertSame(0, QuesturaPrivacyException::$calls);
        $this->technicalLog();
        $this->assertSame(0, QuesturaReceipt::count());
    }

    public function test_reporting_estraneo_a_questura_resta_invariato(): void
    {
        $this->get('/login');
        app(ExceptionHandler::class)->report(new \RuntimeException('ERRORE TECNICO GENERALE SINTETICO'));
        $records = $this->records->getRecords();
        $this->assertSame('ERRORE TECNICO GENERALE SINTETICO', end($records)->message);
        $this->assertInstanceOf(\RuntimeException::class, end($records)->context['exception']);
    }
}

// La catena e il context devono essere esclusi PRIMA di qualsiasi callback.
class QuesturaPrivacyException extends \RuntimeException
{
    public static int $calls = 0;
    public function context(): array { self::$calls++; return ['payload' => $this->getMessage(), 'snapshot' => $this->getPrevious()]; }
    public function report(): bool { self::$calls++; Log::error($this->getMessage(), $this->context()); return true; }
}
