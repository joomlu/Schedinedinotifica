<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaTransmission, Schedina, Struttura};
use App\Services\QuesturaWebService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['questura.enabled' => true]);
    }

    use RefreshDatabase, StrutturaFixtures;

    private function source(Struttura $structure, string $name = 'Ospite', ?string $arrival = null): Schedina
    {
        if (!GeoNazione::whereKey(777)->exists()) {
            GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        }
        return Schedina::create([
            'struttura_id' => $structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => $arrival ?? today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => $name, 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT',
            'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
    }

    private function actorFor(Struttura $structure): void
    {
        $structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $structure->id))
            ->withSession(['struttura_corrente_id' => $structure->id]);
    }

    private function soap(): QuesturaSoapDouble
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $double = new class extends QuesturaSoapDouble {
            public ?string $reject = null;
            public bool $zero = false;
            public $observe = null;
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if ($this->observe) { ($this->observe)($name); }
                if ($this->reject === $name) {
                    $this->calls[] = [$name, $args[0]];
                    return (object)[$name.'Result' => (object)['esito' => false]];
                }
                if ($this->zero && in_array($name, ['Send', 'Test'], true)) {
                    $this->calls[] = [$name, $args[0]];
                    return (object)[$name.'Result' => (object)['esito' => true], 'result' => (object)[
                        'SchedineValide' => 0, 'Dettaglio' => (object)['EsitoOperazioneServizio' => [(object)['esito' => false, 'ErroreCod' => '12']]],
                    ]];
                }
                if ($name === 'Test') {
                    $this->calls[] = [$name, $args[0]];
                    $rows = count($args[0]['ElencoSchedine']['string']);
                    return (object)['TestResult' => (object)['esito' => true], 'result' => (object)[
                        'SchedineValide' => $rows, 'Dettaglio' => (object)['EsitoOperazioneServizio' => array_fill(0, $rows, (object)['esito' => true])],
                    ]];
                }
                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $this->app->instance(QuesturaWebService::class, new class($double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
        return $double;
    }

    private function period(?string $arrival = null): array
    {
        return ['dal' => $arrival ?? today()->toDateString(), 'al' => $arrival ?? today()->toDateString()];
    }

    private function sends(QuesturaSoapDouble $double): int
    {
        return count(array_filter(array_column($double->calls, 0), fn ($n) => $n === 'Send'));
    }

    // Precondizione Q2: vero POST Test positivo prima delle prove del solo Send.
    private function prepareSendTest(QuesturaSoapDouble $double, array $period): void
    {
        $service = new \App\Services\QuesturaTxtExportService();
        $structureId = $this->app['auth']->user()->struttura_id;
        $analysis = $service->analizzaSchedine($service->schedinePerPeriodo($structureId, \Carbon\Carbon::parse($period['dal']), \Carbon\Carbon::parse($period['al'])));
        $txt = $service->buildTxt($analysis);
        $latest = QuesturaTransmission::where('struttura_id', $structureId)->where('mode', 'verify')->latest('id')->first();
        $simulation = $this->app->make(QuesturaWebService::class)->credentialsStatus(Struttura::findOrFail($structureId))['simulation'];
        if ($latest && $latest->schedina_ids === $analysis->pluck('schedina.id')->values()->all() && $latest->sha256 === hash('sha256', $txt) && ($latest->payload['transport_mode'] ?? null) === ($simulation ? 'simulation' : 'live') && !$latest->finalized_at
            && in_array($latest->status, ['unknown', 'simulation'], true)) { return; }
        $previousCalls = $double->calls;
        [$fail, $reject, $zero] = [$double->fail, $double->reject, $double->zero];
        $double->fail = null; $double->reject = null; $double->zero = false;
        try { $this->post('/questura/ws/verify', $period)->assertRedirect(); }
        finally { $double->fail = $fail; $double->reject = $reject; $double->zero = $zero; }
        // Le osservazioni successive riguardano esclusivamente il tentativo Send.
        $double->calls = $previousCalls;
    }

    private function sendWithTest(QuesturaSoapDouble $double, array $period)
    {
        $this->prepareSendTest($double, $period);
        return $this->post('/questura/ws/send', $period);
    }

    public function test_riserva_precede_rete_e_blocca_modifiche_di_ogni_campo(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure);
        $s = $this->source($structure); $before = $s->fresh()->getRawOriginal(); $double = $this->soap();
        $level = DB::transactionLevel();
        $double->observe = function ($name) use ($s, $level) {
            if ($name !== 'Send') { return; }
            $this->assertSame($level, DB::transactionLevel());
            $claim = DB::table('questura_send_reservations')->where('schedina_id', $s->id)->first();
            $this->assertNotNull($claim);
            $this->assertNotNull(QuesturaTransmission::findOrFail($claim->questura_transmission_id)->identity_reserved_at);
        };
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame($before, $s->fresh()->getRawOriginal());
        foreach (['name' => 'Nuovo', 'surname' => 'Corretto', 'departure' => today()->addDays(3)->toDateString(), 'or_doc' => 'NUOVO123', 'or_address' => 'Via sintetica'] as $field => $value) {
            $s->update([$field => $value]);
            $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        }
        $this->assertSame(1, $this->sends($double));
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
    }

    public function test_schedina_diversa_e_tenant_diverso_restano_indipendenti(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null); $double = $this->soap();
        $this->actorFor($a); $first = $this->source($a, 'Primo', today()->subDay()->toDateString());
        $this->sendWithTest($double, $this->period($first->arrive))->assertRedirect();
        $second = $this->source($a, 'Secondo');
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->actorFor($b); $third = $this->source($b, 'Terzo');
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame(3, $this->sends($double));
        $this->assertSame(3, DB::table('questura_send_reservations')->count());
        $this->assertSame([$first->id, $second->id], DB::table('questura_send_reservations')->where('struttura_id', $a->id)->orderBy('schedina_id')->pluck('schedina_id')->all());
        $this->assertSame([$third->id], DB::table('questura_send_reservations')->where('struttura_id', $b->id)->pluck('schedina_id')->all());
    }

    public function test_vincolo_db_conflitto_http_e_rollback_intero_elenco(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null); $this->actorFor($a); $double = $this->soap();
        $first = $this->source($a, 'Primo'); $second = $this->source($a, 'Secondo');
        $tx = QuesturaTransmission::create(['struttura_id' => $a->id, 'mode' => 'send', 'status' => 'in_progress', 'identity_reserved_at' => now()]);
        $claim = ['struttura_id' => $a->id, 'schedina_id' => $second->id, 'transport_mode' => 'live', 'questura_transmission_id' => $tx->id, 'created_at' => now()];
        DB::table('questura_send_reservations')->insert($claim);
        try {
            DB::transaction(fn () => DB::table('questura_send_reservations')->insert($claim));
            $this->fail('Due riserve valide per la stessa identità');
        } catch (UniqueConstraintViolationException) { $this->assertSame(1, DB::table('questura_send_reservations')->count()); }
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(0, $this->sends($double));
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_transmission_events')->whereIn('questura_transmission_id', QuesturaTransmission::where('mode', 'send')->select('id'))->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->where('schedina_id', $first->id)->count());
        // Gli ID Schedina sono PK globali: stessa identità in un altro tenant
        // si verifica a livello del vincolo, senza creare due PMS con la stessa PK.
        $claim['struttura_id'] = $b->id;
        $otherTx = QuesturaTransmission::create(['struttura_id' => $b->id, 'mode' => 'send', 'status' => 'in_progress', 'identity_reserved_at' => now()]);
        $claim['questura_transmission_id'] = $otherTx->id;
        DB::table('questura_send_reservations')->insert($claim);
        $this->assertSame(2, DB::table('questura_send_reservations')->count());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('retryCases')]
    public function test_retry_solo_con_esclusione_certa_della_trasmissione(string $case): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        if ($case === 'token' || $case === 'auth_exception') { $double->fail = $case === 'token' ? 'GenerateToken' : 'Authentication_Test'; }
        if ($case === 'auth_false') { $double->reject = 'Authentication_Test'; }
        if ($case === 'zero') { $double->zero = true; }
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $first = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertTrue($first->result['transmission_excluded']);
        $this->assertNull($first->send_key);
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->assertSame($case === 'zero' ? 1 : 0, $this->sends($double));
        $double->fail = null; $double->reject = null; $double->zero = false;
        // Stessi byte: il retry legittimo non viene impedito dalla vecchia send_key.
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame('sent', QuesturaTransmission::where('mode', 'send')->latest('id')->firstOrFail()->status);
        $this->assertSame(2, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $this->assertTrue(Schedina::where('id', $s->id)->exists());
    }

    public static function retryCases(): array { return [['token'], ['auth_exception'], ['auth_false'], ['zero']]; }

    public function test_esito_generale_send_negativo_senza_dettaglio_non_prova_zero_acquisizioni(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        $double->reject = 'Send';
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertSame('rejected', $tx->status);
        $this->assertArrayNotHasKey('transmission_excluded', $tx->result);
        $s->update(['name' => 'Modificato']); $double->reject = null;
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, $this->sends($double));
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
    }

    public function test_validazione_locale_e_test_ws_falliti_non_riservano_send(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        $s->update(['name' => '']);
        $this->post('/questura/ws/send', $this->period())->assertRedirect()->assertSessionHasErrors();
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame([], $double->calls);
        $s->update(['name' => 'Corretto']); $double->reject = 'Test';
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $this->assertSame('rejected', QuesturaTransmission::where('mode', 'verify')->firstOrFail()->status);
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->assertSame(0, $this->sends($double));
        // Il nuovo Test positivo prepara il Send senza indebolire le riserve Q1.
        $double->reject = null;
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame(1, $this->sends($double));
    }

    public function test_risposta_incoerente_e_errore_generico_non_liberano_riserva(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        $double->malformed = true;
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertSame('uncertain', $tx->status);
        $this->assertArrayNotHasKey('transmission_excluded', $tx->result);
        $s->update(['name' => 'Diverso']); $double->malformed = false;
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, $this->sends($double));
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
    }

    public function test_retention_conserva_solo_identita_tecniche_e_blocca_overlap(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $tx = QuesturaTransmission::where('mode', 'send')->firstOrFail(); $before = $s->fresh()->getRawOriginal();
        $claims = DB::table('questura_send_reservations')->get()->map(fn ($row) => (array) $row)->all();
        \Carbon\Carbon::setTestNow(now()->addDay());
        try {
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $this->assertNull($tx->fresh()->payload);
            $this->assertNull($tx->fresh()->schedina_ids);
            $this->assertNotNull($tx->fresh()->finalized_at);
            $this->assertSame($claims, DB::table('questura_send_reservations')->get()->map(fn ($row) => (array) $row)->all());
            $this->assertSame(['id', 'struttura_id', 'schedina_id', 'transport_mode', 'questura_transmission_id', 'created_at'], array_keys((array) $claims[0]));
            $this->assertSame($before, $s->fresh()->getRawOriginal());
            $this->assertDatabaseCount('questura_receipts', 1);
            $this->source($structure, 'Nuovo', $s->arrive);
            $this->sendWithTest($double, $this->period($s->arrive))->assertRedirect()->assertSessionHasErrors('questura_ws');
            $this->assertSame(1, $this->sends($double));
            $this->assertSame(1, DB::table('questura_send_reservations')->count());
        } finally { \Carbon\Carbon::setTestNow(); }
    }

    public function test_legacy_minimizzato_senza_identita_blocca_solo_il_suo_tenant(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null); $this->actorFor($a); $double = $this->soap();
        $this->source($a);
        $tx = QuesturaTransmission::create(['struttura_id' => $a->id, 'mode' => 'send', 'status' => 'sent']);
        DB::table('questura_transmissions')->where('id', $tx->id)->update(['finalized_at' => now(), 'payload' => null, 'schedina_ids' => null]);
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(0, $this->sends($double));
        $this->actorFor($b); $this->source($b, 'Altro tenant');
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame(1, $this->sends($double));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('blockingCases')]
    public function test_parziale_o_crash_dopo_send_bloccano_anche_payload_modificato(string $case): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure);
        $s = $this->source($structure, 'Primo'); $this->source($structure, 'Secondo'); $double = $this->soap();
        if ($case === 'partial') { $double->partial = true; }
        $this->prepareSendTest($double, $this->period());
        if ($case === 'crash') {
            $this->prepareSendTest($double, $this->period());
        QuesturaTransmission::updating(fn () => throw new \RuntimeException('CRASH SINTETICO'));
        }
        try {
            $this->sendWithTest($double, $this->period())->assertStatus($case === 'crash' ? 500 : 302);
        } finally { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $this->assertSame($case === 'crash' ? 'in_progress' : 'partial', QuesturaTransmission::where('mode', 'send')->firstOrFail()->status);
        $this->assertSame(2, DB::table('questura_send_reservations')->count());
        $s->update(['name' => 'Corretto']);
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(1, $this->sends($double));
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
    }

    public static function blockingCases(): array { return [['partial'], ['crash']]; }

    public function test_errore_db_prima_della_rete_annulla_riserve_e_consente_retry(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $this->source($structure); $double = $this->soap();
        $this->prepareSendTest($double, $this->period());
        QuesturaTransmission::created(fn () => throw new \RuntimeException('ERRORE DB PRIMA DI SOAP'));
        try {
            $this->sendWithTest($double, $this->period())->assertStatus(500);
        } finally { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $this->assertSame([], $double->calls);
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame(1, $this->sends($double));
    }

    public function test_errore_db_dopo_esclusione_certa_non_libera_un_tentativo_non_consolidato(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $s = $this->source($structure); $double = $this->soap();
        $double->fail = 'GenerateToken';
        $this->prepareSendTest($double, $this->period());
        QuesturaTransmission::updating(fn () => throw new \RuntimeException('CRASH REGISTRAZIONE ESITO'));
        try {
            $this->sendWithTest($double, $this->period())->assertStatus(500);
        } finally { QuesturaTransmission::flushEventListeners(); QuesturaTransmission::clearBootedModels(); }
        $this->assertSame('in_progress', QuesturaTransmission::where('mode', 'send')->firstOrFail()->status);
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $double->fail = null; $s->update(['name' => 'Modificato']);
        $this->sendWithTest($double, $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame(0, $this->sends($double));
    }

    public function test_simulazione_non_occupa_la_riserva_live(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $this->source($structure);
        $double = $this->soap();
        $this->app->instance(QuesturaWebService::class, new class($double) extends QuesturaWebService {
            public bool $simulate = true;
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return $this->simulate; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
        $ws = $this->app->make(QuesturaWebService::class);
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame('simulation', QuesturaTransmission::where('mode', 'send')->firstOrFail()->status);
        $this->assertSame([], $double->calls);
        $ws->simulate = false;
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $this->assertSame(1, $this->sends($double));
        $this->assertSame(['live', 'simulation'], DB::table('questura_send_reservations')->orderBy('transport_mode')->pluck('transport_mode')->all());
    }

    public function test_schedina_distinta_con_byte_identici_non_collide_sul_vecchio_hash(): void
    {
        $structure = $this->structureFor(null); $this->actorFor($structure); $first = $this->source($structure); $double = $this->soap();
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $original = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        // Sposta solo la selezione della prima fixture; la sua identità resta riservata.
        $first->update(['arrive' => today()->addDay()->toDateString()]);
        $second = $this->source($structure);
        $this->sendWithTest($double, $this->period())->assertRedirect();
        $next = QuesturaTransmission::where('mode', 'send')->latest('id')->firstOrFail();
        $this->assertSame($original->sha256, $next->sha256);
        $this->assertNotSame($original->send_key, $next->send_key);
        $this->assertSame([$second->id], $next->schedina_ids);
        $this->assertSame(2, $this->sends($double));
        $this->assertSame(2, DB::table('questura_send_reservations')->count());
    }
}
