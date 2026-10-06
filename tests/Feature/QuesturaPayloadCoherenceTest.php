<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaTransmission, Schedina, Struttura};
use App\Services\{QuesturaTxtExportService, QuesturaWebService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaPayloadCoherenceTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private Struttura $structure;
    private Schedina $source;
    private QuesturaSoapDouble $double;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $this->structure = $this->structureFor(null);
        $this->structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $this->structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $this->source = $this->source('Prima');
        $this->double = new class extends QuesturaSoapDouble {
            public bool $negative = false;
            public bool $testMalformed = false;
            public $observe = null;
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if ($this->observe) { ($this->observe)($name, $args); }
                if ($name === 'Test') {
                    $this->calls[] = [$name, $args[0]];
                    if ($this->testMalformed) { return (object)['HTTP' => 200]; }
                    $rows = count($args[0]['ElencoSchedine']['string']);
                    return (object)['TestResult' => (object)['esito' => !$this->negative], 'result' => (object)[
                        'SchedineValide' => $rows, 'Dettaglio' => (object)['EsitoOperazioneServizio' => array_fill(0, $rows, (object)['esito' => true])],
                    ]];
                }
                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $this->app->instance(QuesturaWebService::class, new class($this->double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
    }

    private function source(string $name): Schedina
    {
        return Schedina::create([
            'struttura_id' => $this->structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => $name, 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
    }

    private function period(): array { return ['dal' => today()->toDateString(), 'al' => today()->toDateString()]; }
    private function verify(): QuesturaTransmission
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        return QuesturaTransmission::where('mode', 'verify')->latest('id')->firstOrFail();
    }
    private function sends(): array { return array_values(array_filter($this->double->calls, fn ($call) => $call[0] === 'Send')); }
    private function blocked(): void
    {
        $calls = count($this->double->calls);
        $this->post('/questura/ws/send', $this->period()+['sha256' => 'forgiato', 'test_positive' => true])->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertSame($calls, count($this->double->calls), 'Blocco prima di qualsiasi nuova chiamata SOAP.');
        $this->assertSame([], $this->sends());
        $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('changes')]
    public function test_modifiche_del_payload_richiedono_nuovo_test(string $field): void
    {
        $verify = $this->verify();
        $value = match ($field) { 'name' => 'Modificato', 'departure' => today()->addDays(3)->toDateString(), default => 'NUOVO123' };
        $this->source->update([$field => $value]);
        $this->blocked();
        $this->assertSame($verify->sha256, $verify->fresh()->sha256);
        $next = $this->verify();
        $this->assertNotSame($verify->sha256, $next->sha256);
        $this->post('/questura/ws/send', $this->period())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(1, $this->sends());
        $this->assertSame($next->sha256, QuesturaTransmission::where('mode', 'send')->firstOrFail()->sha256);
    }
    public static function changes(): array { return [['name'], ['departure'], ['or_doc']]; }

    public function test_campo_pms_non_trasmesso_non_invalida_test(): void
    {
        $verify = $this->verify();
        $this->source->update(['or_address' => 'Via sintetica']);
        $this->post('/questura/ws/send', $this->period())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertCount(1, $this->sends());
        $this->assertSame($verify->sha256, hash('sha256', implode("\r\n", $this->sends()[0][1]['ElencoSchedine']['string'])));
    }

    public function test_modifica_un_solo_elemento_del_batch_blocca_tutto(): void
    {
        $b = $this->source('Seconda'); $this->source('Terza');
        $verify = $this->verify();
        $this->assertSame(3, $verify->result['valid_rows']);
        $b->update(['name' => 'Modificata']);
        $this->blocked();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTests')]
    public function test_test_assente_negativo_invalido_o_estraneo_non_autorizza(string $case): void
    {
        if ($case !== 'assente') {
            $this->double->negative = $case === 'negativo';
            $this->double->testMalformed = $case === 'malformato';
            if ($case === 'ordine') { $this->source('Seconda'); $this->source('Terza'); }
            $verify = $this->verify();
            $alteration = match ($case) {
                'tenant' => ['struttura_id' => $this->structureFor(null)->id],
                'identita' => ['schedina_ids' => json_encode([999999])],
                'ordine' => ['schedina_ids' => json_encode(array_reverse($verify->schedina_ids))],
                'componenti' => ['component_ids' => json_encode([999999])],
                'positivo_non_dimostrato' => ['result' => json_encode(['state' => 'unknown', 'accepted' => false])],
                'byte_corrotto' => ['payload' => json_encode(array_replace($verify->payload, ['txt_base64' => base64_encode('DIVERSO')]))],
                'finalizzato' => ['finalized_at' => now()],
                'trasporto' => ['payload' => json_encode(array_replace($verify->payload, ['transport_mode' => 'simulation']))],
                'minimizzato' => ['payload_deleted_at' => now()],
                'in_progress' => ['status' => 'in_progress'],
                'record' => ['righe_count' => 2],
                default => [],
            };
            if ($alteration) { DB::table('questura_transmissions')->where('id', $verify->id)->update($alteration); }
        }
        $this->blocked();
    }
    public static function invalidTests(): array
    {
        return array_map(fn ($case) => [$case], ['assente', 'negativo', 'malformato', 'tenant', 'identita', 'ordine', 'trasporto', 'minimizzato', 'in_progress', 'record', 'componenti', 'positivo_non_dimostrato', 'byte_corrotto', 'finalizzato']);
    }

    public function test_test_negativo_successivo_invalida_quello_positivo(): void
    {
        $this->verify(); $this->double->negative = true; $this->verify(); $this->blocked();
    }

    public function test_identita_diversa_con_byte_identici_richiede_nuovo_test(): void
    {
        $verify = $this->verify();
        $this->source->update(['arrive' => today()->addDay()->toDateString()]);
        $this->source('Prima');
        $this->assertSame($verify->sha256, hash('sha256', (new QuesturaTxtExportService())->buildTxtPerSchedina(Schedina::latest('id')->firstOrFail())));
        $this->blocked();
    }

    public function test_toctou_non_rigenera_il_payload_fra_controllo_e_trasporto(): void
    {
        $verify = $this->verify();
        $builder = new class extends QuesturaTxtExportService {
            public int $builds = 0;
            public function buildTxt($analisi): string
            {
                $this->builds++;
                if ($this->builds > 1) { throw new \LogicException('Rigenerazione divergente fra controllo e trasporto'); }
                return parent::buildTxt($analisi);
            }
        };
        // Nuovo controller: dipendenza reale sostituita prima della richiesta Send.
        $this->app->forgetInstance(\App\Http\Controllers\QuesturaExportController::class);
        $this->app->instance(QuesturaTxtExportService::class, $builder);
        $this->double->observe = function ($name, $args) use ($verify) {
            if ($name !== 'Send') { return; }
            $this->assertSame($verify->sha256, hash('sha256', implode("\r\n", $args[0]['ElencoSchedine']['string'])));
            $this->assertSame(1, DB::table('questura_send_reservations')->count());
            $this->assertSame(base64_decode($verify->payload['txt_base64'], true), implode("\r\n", $args[0]['ElencoSchedine']['string']));
        };
        $this->post('/questura/ws/send', $this->period())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, $builder->builds);
        $this->assertCount(1, $this->sends());
    }

    public function test_q2_blocca_poi_nuovo_test_consente_primo_send_incerto_e_q1_blocca_reinvio(): void
    {
        $this->verify(); $this->source->update(['name' => 'Modificata']); $this->blocked();
        $this->verify(); $this->double->fail = 'Send';
        $this->post('/questura/ws/send', $this->period())->assertRedirect();
        $send = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $this->assertSame('uncertain', $send->status);
        $this->double->fail = null;
        $this->source->update(['name' => 'Ancora modificata']); $this->verify();
        $this->post('/questura/ws/send', $this->period())->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertCount(1, $this->sends());
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
        $this->assertSame(1, QuesturaTransmission::where('mode', 'send')->count());
    }
}
