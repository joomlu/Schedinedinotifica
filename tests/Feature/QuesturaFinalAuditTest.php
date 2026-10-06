<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaExport, QuesturaReceipt, QuesturaTransmission, Schedina, Struttura};
use App\Services\{QuesturaRetentionService, QuesturaWebService};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaFinalAuditTest extends TestCase
{
    use RefreshDatabase { beginDatabaseTransaction as private beginFixtureTransaction; }
    use StrutturaFixtures;

    public function beginDatabaseTransaction(): void
    {
        $dbFailure = $this->name() === 'test_errore_db_pre_rete_non_replica_snapshot_personale_nei_log';
        if ($dbFailure) {
            // DDL prima della transazione fixture: nessun commit implicito dei dati.
            DB::unprepared("CREATE TRIGGER questura_audit_db_failure BEFORE INSERT ON questura_transmissions FOR EACH ROW BEGIN IF NEW.mode = 'send' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GUASTO DB SINTETICO'; END IF; END");
        }
        $this->beginFixtureTransaction();
        if ($dbFailure) {
            // RefreshDatabase registra prima il rollback; DDL soltanto dopo di esso.
            $this->beforeApplicationDestroyed(fn () => DB::unprepared('DROP TRIGGER questura_audit_db_failure'));
        }
    }

    private Struttura $structure;
    private Schedina $source;
    private QuesturaSoapDouble $double;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $this->structure = $this->structureFor(null);
        $this->structure->update(['questura_username' => 'fixture-account', 'questura_password' => 'PASSWORD-RISERVATA-SINTETICA', 'questura_wskey' => 'WSKEY-RISERVATA-SINTETICA']);
        $this->actingAs($this->actor('struttura_user', null, $this->structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $this->source = Schedina::create([
            'struttura_id' => $this->structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => 'OspiteSinteticoRiservato', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
        $this->double = new class extends QuesturaSoapDouble {
            public ?string $negative = null;
            public bool $invalidPdf = false;
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if ($name === $this->negative) {
                    $this->calls[] = [$name, $args[0]];
                    return (object)[$name.'Result' => (object)['esito' => false, 'ErroreDettaglio' => 'PASSWORD-RISERVATA-SINTETICA']];
                }
                if ($name === 'Ricevuta' && $this->invalidPdf) {
                    $this->calls[] = [$name, $args[0]];
                    return (object)['RicevutaResult' => (object)['esito' => true], 'PDF' => 'BLOB-INVALIDO'];
                }
                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $this->app->instance(QuesturaWebService::class, new class($this->double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
        // Solo log del filesystem effimero attestato, mai log operativi.
        config(['logging.default' => 'single', 'logging.channels.single.path' => storage_path('logs/audit-fixture.log')]);
        $this->app->make('log')->forgetChannel('single');
    }

    private function period(): array { return ['dal' => today()->toDateString(), 'al' => today()->toDateString()]; }
    private function send(): QuesturaTransmission
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $this->post('/questura/ws/send', $this->period())->assertRedirect();
        return QuesturaTransmission::where('mode', 'send')->firstOrFail();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('failureCases')]
    public function test_frontiere_test_non_autorizzano_send_ne_esposizioni(string $case, string $expected): void
    {
        if ($case === 'token' || $case === 'timeout') { $this->double->fail = $case === 'token' ? 'GenerateToken' : 'Test'; }
        if ($case === 'autenticazione' || $case === 'negativo') { $this->double->negative = $case === 'autenticazione' ? 'Authentication_Test' : 'Test'; }
        $response = $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $verify = QuesturaTransmission::where('mode', 'verify')->firstOrFail();
        $this->assertSame($expected, $verify->status);
        $this->assertFalse($verify->result['accepted']);
        $calls = count($this->double->calls);
        $this->post('/questura/ws/send', $this->period())->assertSessionHasErrors('questura_ws');
        $this->assertSame($calls, count($this->double->calls));
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertPrivacy($response->getContent(), $verify);
    }
    public static function failureCases(): array
    {
        return [['token', 'technical_error'], ['timeout', 'technical_error'], ['autenticazione', 'rejected'], ['negativo', 'rejected']];
    }

    private function assertPrivacy(string $response, QuesturaTransmission $tx): void
    {
        $log = is_file(storage_path('logs/audit-fixture.log')) ? file_get_contents(storage_path('logs/audit-fixture.log')) : '';
        $audit = json_encode(DB::table('struttura_audit_logs')->get());
        $events = DB::table('questura_transmission_events')->where('questura_transmission_id', $tx->id)->pluck('result')->implode('');
        foreach ([$response, json_encode($tx->result), $events, $audit, $log, json_encode($tx->toArray()), json_encode($this->structure->toArray())] as $surface) {
            $this->assertStringNotContainsString('PASSWORD-RISERVATA-SINTETICA', $surface);
            $this->assertStringNotContainsString('WSKEY-RISERVATA-SINTETICA', $surface);
        }
        foreach ([$response, $events, $audit, $log, json_encode($tx->toArray())] as $surface) {
            $this->assertStringNotContainsString('OspiteSinteticoRiservato', $surface);
        }
    }

    public function test_errore_db_pre_rete_non_replica_snapshot_personale_nei_log(): void
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $verify = QuesturaTransmission::where('mode', 'verify')->firstOrFail();
        $base64 = $verify->payload['txt_base64'];
        $calls = count($this->double->calls);
        // Guasto DB reale e circoscritto alla fixture: rifiuto INSERT Send.
        // Nessun payload o segreto nel messaggio del trigger.
        $this->post('/questura/ws/send', $this->period())->assertStatus(500);
        $this->assertSame($calls, count($this->double->calls));
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->assertTrue(Schedina::whereKey($this->source->id)->exists());
        $log = is_file(storage_path('logs/audit-fixture.log')) ? file_get_contents(storage_path('logs/audit-fixture.log')) : '';
        // Solo booleano nell'eventuale failure: nessun TXT/base64 nel diff PHPUnit.
        $this->assertFalse(str_contains($log, $base64), 'Il log di errore DB contiene una copia base64 dello snapshot personale Questura.');
    }

    public function test_test_ripetuto_doppio_submit_e_privacy_dopo_send(): void
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $tx = $this->send();
        $this->post('/questura/ws/send', $this->period())->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, count(array_filter($this->double->calls, fn ($c) => $c[0] === 'Send')));
        $this->assertSame(2, QuesturaTransmission::where('mode', 'verify')->count());
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertPrivacy('', $tx);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_ruoli_autorizzati_alla_propria_struttura(string $role): void
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin);
        $this->structure->update(['proprietario_id' => $owner->id]);
        $user = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $role === 'struttura_user' ? $this->structure->id : null);
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $this->structure->id]);
        $this->get('/questura')->assertOk();
        $tx = $this->send();
        $this->assertSame('sent', $tx->status); $this->assertFalse($tx->result['accepted']);
    }
    public static function roles(): array { return [['super_admin'], ['admin'], ['proprietario'], ['struttura_user']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('protectedRoutes')]
    public function test_auth_ruolo_csrf_e_tenant_sulle_route(string $method, string $route): void
    {
        $calls = count($this->double->calls);
        $this->app['auth']->forgetGuards();
        $this->call($method, $route)->assertRedirect(route('login'));
        $this->actingAs($this->actor('ruolo_non_ammesso', null, $this->structure->id));
        $this->call($method, $route)->assertForbidden();
        $this->actingAs($this->actor('struttura_user', null, $this->structure->id));
        if ($method === 'POST') { $this->call($method, $route, ['_token' => 'errato'])->assertStatus(419); }
        $b = $this->structureFor(null);
        $this->call($method, $route.'?struttura_id='.$b->id)->assertForbidden();
        $this->withSession(['struttura_corrente_id' => $b->id]);
        $this->call($method, $route)->assertForbidden();
        $this->assertSame($calls, count($this->double->calls));
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertSame(0, QuesturaExport::count());
    }
    public static function protectedRoutes(): array
    {
        return [['POST', '/questura/ws/verify'], ['POST', '/questura/ws/send'], ['POST', '/questura/ws/receipt/999999'], ['GET', '/questura/ws/receipt/999999'], ['POST', '/questura/ws/999999/finalizza'], ['GET', '/questura/ricevute/999999'], ['GET', '/questura/ws/payload/999999'], ['GET', '/questura/download/storico/999999'], ['POST', '/questura/txt/999999/ricevuta']];
    }

    public function test_id_reali_di_altro_tenant_non_accedono_a_payload_ricevuta_o_retention(): void
    {
        $b = $this->structureFor(null);
        $txt = 'ELENCO SINTETICO B'; $day = now()->subDay();
        $path = 'questura/struttura_'.$b->id.'/fixture-b.txt';
        Storage::disk('local')->put($path, $txt);
        $export = QuesturaExport::create(['struttura_id' => $b->id, 'dal' => $day, 'al' => $day, 'filename' => 'fixture-b.txt', 'path' => $path, 'sha256' => hash('sha256', $txt), 'status' => 'generated']);
        $tx = QuesturaTransmission::create(['struttura_id' => $b->id, 'mode' => 'send', 'status' => 'sent', 'executed_at' => $day, 'sha256' => hash('sha256', $txt), 'payload' => ['transport_mode' => 'live', 'txt_base64' => base64_encode($txt)]]);
        $receipt = app(QuesturaRetentionService::class)->archiveReceipt($b, $day, "%PDF-1.4\nSINTETICO B\n%%EOF", $tx->id);
        foreach (['/questura/download/storico/'.$export->id, '/questura/ws/payload/'.$tx->id, '/questura/ws/receipt/'.$tx->id, '/questura/ricevute/'.$receipt->id] as $route) { $this->get($route)->assertNotFound(); }
        $this->post('/questura/ws/receipt/'.$tx->id)->assertNotFound();
        $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertNotFound();
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_confirmed' => 1, 'communication_date' => $day->toDateString()])->assertNotFound();
        $this->assertSame([], $this->double->calls);
        $this->assertNotNull($tx->fresh()->payload); $this->assertNull($tx->fresh()->finalized_at);
        $this->assertSame($txt, Storage::disk('local')->get($path));
        $this->assertTrue(Storage::disk('local')->exists($receipt->path));
    }

    public function test_ricevuta_invalida_retry_e_duplicato_senza_secondo_send(): void
    {
        $before = $this->source->fresh()->getRawOriginal(); $tx = $this->send();
        Carbon::setTestNow(now()->addDay());
        try {
            $this->double->invalidPdf = true;
            $this->post('/questura/ws/receipt/'.$tx->id)->assertSessionHasErrors('questura_ws');
            $this->assertSame(0, QuesturaReceipt::count()); $this->assertNotNull($tx->fresh()->payload);
            $this->double->invalidPdf = false;
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $r = QuesturaReceipt::firstOrFail(); $acquired = $r->acquired_at->toDateTimeString(); $calls = count($this->double->calls);
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $this->assertSame($calls, count($this->double->calls)); $this->assertSame(1, QuesturaReceipt::count());
            $this->assertSame($acquired, $r->fresh()->acquired_at->toDateTimeString());
            $this->get('/questura/ws/receipt/'.$tx->id)->assertOk()->assertContent(Storage::disk('local')->get($r->path));
            $this->assertNull($tx->fresh()->payload); $this->assertSame($before, $this->source->fresh()->getRawOriginal());
            $this->assertSame(1, DB::table('questura_send_reservations')->count());
        } finally { Carbon::setTestNow(); }
    }

    public function test_ricevuta_tardiva_archiviata_riutilizzabile_ma_nuova_fuori_finestra_no(): void
    {
        $tx = $this->send(); $at = now();
        Carbon::setTestNow($at->copy()->addDays(30));
        try {
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $r = QuesturaReceipt::firstOrFail(); $calls = count($this->double->calls);
            Carbon::setTestNow($at->copy()->addDays(31));
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $this->get('/questura/ricevute/'.$r->id)->assertOk();
            $this->assertSame($calls, count($this->double->calls));
            $b = $this->structureFor(null);
            try { app(QuesturaRetentionService::class)->archiveReceipt($b, $at, "%PDF-1.4\nSINTETICO\n%%EOF", null); $this->fail('Nuova ricevuta fuori finestra'); }
            catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
            $this->assertSame(1, QuesturaReceipt::count());
        } finally { Carbon::setTestNow(); }
    }
}
