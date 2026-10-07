<?php

namespace Tests\Feature;

use App\Models\GeoNazione;
use App\Models\QuesturaExport;
use App\Models\QuesturaReceipt;
use App\Models\QuesturaTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\QuesturaRetentionService;
use App\Services\QuesturaWebService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaConclusiveAuditTest extends TestCase
{
    use RefreshDatabase;
    use StrutturaFixtures;

    private Struttura $structure;

    private Schedina $source;

    private QuesturaSoapDouble $double;

    protected function setUp(): void
    {
        parent::setUp();
        config(['questura.enabled' => true]);
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
        $this->double = new class extends QuesturaSoapDouble
        {
            public ?string $negative = null;

            public bool $invalidPdf = false;

            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if ($name === $this->negative) {
                    $this->calls[] = [$name, $args[0]];

                    return (object) [$name.'Result' => (object) ['esito' => false, 'ErroreDettaglio' => 'PASSWORD-RISERVATA-SINTETICA']];
                }
                if ($name === 'Ricevuta' && $this->invalidPdf) {
                    $this->calls[] = [$name, $args[0]];

                    return (object) ['RicevutaResult' => (object) ['esito' => true], 'PDF' => 'BLOB-INVALIDO'];
                }

                return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
            }
        };
        $this->app->instance(QuesturaWebService::class, new class($this->double) extends QuesturaWebService
        {
            public function __construct(private \SoapClient $double) {}

            protected function isSimulation(Struttura $s): bool
            {
                return false;
            }

            protected function makeClient(): \SoapClient
            {
                return $this->double;
            }
        });
        // Solo log del filesystem effimero attestato, mai log operativi.
        config(['logging.default' => 'single', 'logging.channels.single.path' => storage_path('logs/audit-fixture.log')]);
        $this->app->make('log')->forgetChannel('single');
    }

    private function period(): array
    {
        return ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
    }

    private function send(): QuesturaTransmission
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $this->post('/questura/ws/send', $this->period())->assertRedirect();

        return QuesturaTransmission::where('mode', 'send')->firstOrFail();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('generationRoutes')]
    public function test_auth_csrf_ruolo_e_tenant_pagina_generazione(string $method, string $route): void
    {
        $this->app['auth']->forgetGuards();
        $this->call($method, $route)->assertRedirect(route('login'));
        $this->actingAs($this->actor('ruolo_non_ammesso', null, $this->structure->id));
        $this->call($method, $route)->assertForbidden();
        $this->actingAs($this->actor('struttura_user', null, $this->structure->id));
        if ($method === 'POST') {
            $this->call($method, $route, ['_token' => 'errato'])->assertStatus(419);
        }
        $b = $this->structureFor(null);
        $this->call($method, $route.'?struttura_id='.$b->id)->assertForbidden();
        $this->withSession(['struttura_corrente_id' => $b->id]);
        $this->call($method, $route)->assertForbidden();
        $this->assertSame([], $this->double->calls);
        $this->assertSame(0, QuesturaExport::count());
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertTrue($this->source->fresh()->exists);
    }

    public static function generationRoutes(): array
    {
        return [['GET', '/questura'], ['POST', '/questura/download/periodo'], ['POST', '/questura/download/schedina/999999']];
    }

    public function test_generazione_download_refresh_non_trasmettono_ne_puliscono(): void
    {
        $before = $this->source->fresh()->getRawOriginal();
        $a = $this->post('/questura/download/periodo', $this->period())->assertOk();
        $b = $this->post('/questura/download/schedina/'.$this->source->id)->assertOk();
        $this->assertSame($a->getContent(), $b->getContent());
        $exports = QuesturaExport::orderBy('id')->get();
        $this->assertCount(2, $exports);
        foreach ($exports as $export) {
            $this->get('/questura/download/storico/'.$export->id)->assertOk()->assertStreamedContent($a->getContent());
            $this->assertNull($export->fresh()->finalized_at);
            $this->assertTrue(Storage::disk('local')->exists($export->path));
        }
        $this->get('/questura')->assertOk();
        $this->get('/questura')->assertOk();
        $this->assertSame([], $this->double->calls);
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertSame(0, QuesturaReceipt::count());
        // La generazione aggiorna soltanto i tre metadati di esportazione previsti.
        $after = $this->source->fresh()->getRawOriginal();
        $this->assertSame(2, $this->source->fresh()->questura_export_count);
        foreach (['questura_exported_at', 'questura_export_count', 'last_questura_export_id'] as $key) {
            unset($before[$key], $after[$key]);
        }
        $this->assertSame($before, $after);
        $foreign = $this->source->replicate();
        $foreign->struttura_id = $this->structureFor(null)->id;
        $foreign->save();
        $this->post('/questura/download/schedina/'.$foreign->id)->assertNotFound();
        $this->assertSame(2, QuesturaExport::count());
    }

    public function test_ricevuta_mancante_o_alterata_blocca_download_e_riconciliazione(): void
    {
        $tx = $this->send();
        $payload = $tx->payload;
        Carbon::setTestNow(now()->addDay());
        try {
            $this->get('/questura/ws/receipt/'.$tx->id)->assertNotFound();
            $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertNotFound();
            $r = app(QuesturaRetentionService::class)->archiveReceipt($this->structure, $tx->executed_at, "%PDF-1.4\nSINTETICO\n%%EOF", $tx->id);
            Storage::disk('local')->put($r->path, 'PDF ALTERATO SINTETICO');
            $this->get('/questura/ricevute/'.$r->id)->assertStatus(409);
            $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertStatus(409);
            $this->post('/questura/ws/receipt/'.$tx->id)->assertStatus(409);
            $this->assertSame($payload, $tx->fresh()->payload);
            $this->assertNull($tx->fresh()->finalized_at);
            $this->assertSame(1, DB::table('questura_send_reservations')->count());
            $this->assertTrue($this->source->fresh()->exists);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_incerto_ricevuta_giornaliera_non_associa_accettazione_senza_conferma(): void
    {
        $this->double->fail = 'Send';
        $tx = $this->send();
        $this->assertSame('uncertain', $tx->status);
        $before = $this->source->fresh()->getRawOriginal();
        Carbon::setTestNow(now()->addDay());
        try {
            $this->double->fail = null;
            $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
            $r = QuesturaReceipt::firstOrFail();
            $calls = count($this->double->calls);
            $this->assertNull($tx->fresh()->finalized_at);
            $this->assertNull($tx->fresh()->questura_receipt_id);
            $this->assertNotNull($tx->fresh()->payload);
            $this->post('/questura/ws/'.$tx->id.'/finalizza')->assertSessionHasErrors('reconciled');
            $this->assertNull($tx->fresh()->finalized_at);
            $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertRedirect();
            $events = DB::table('questura_transmission_events')->count();
            $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertRedirect();
            $this->assertSame($events, DB::table('questura_transmission_events')->count());
            $this->assertSame($calls, count($this->double->calls));
            $this->assertNull($tx->fresh()->payload);
            $this->assertSame($r->id, $tx->fresh()->questura_receipt_id);
            $this->assertNotNull($tx->fresh()->reconciled_at);
            $this->assertSame(1, DB::table('questura_send_reservations')->count());
            $this->get('/questura/ws/payload/'.$tx->id)->assertStatus(410);
            $this->get('/questura/ricevute/'.$r->id)->assertOk();
            $this->assertSame($before, $this->source->fresh()->getRawOriginal());
            $log = is_file(storage_path('logs/audit-fixture.log')) ? file_get_contents(storage_path('logs/audit-fixture.log')) : '';
            foreach ([$this->source->name, 'PASSWORD-RISERVATA-SINTETICA', 'WSKEY-RISERVATA-SINTETICA', base64_encode('OspiteSinteticoRiservato')] as $marker) {
                $this->assertFalse(str_contains($log, $marker));
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('restrictedOwners')]
    public function test_membership_ownership_sulle_operazioni_questura(string $role): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $foreign = $this->structureFor($owner);
        $user = $role === 'admin' ? $this->actor('admin') : $this->actor('proprietario', $this->ownerFor($this->actor('admin'))->id);
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $foreign->id]);
        foreach ([['GET', '/questura'], ['POST', '/questura/download/periodo'], ['POST', '/questura/ws/verify'], ['POST', '/questura/ws/send'], ['POST', '/questura/ws/receipt/999999'], ['POST', '/questura/ws/999999/finalizza']] as [$method, $route]) {
            $this->withSession(['struttura_corrente_id' => $foreign->id])->call($method, $route.'?struttura_id='.$foreign->id)->assertForbidden();
        }
        $this->assertSame([], $this->double->calls);
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertSame(0, QuesturaExport::count());
    }

    public static function restrictedOwners(): array
    {
        return [['admin'], ['proprietario']];
    }

    public function test_ricevuta_non_associabile_a_test_o_esito_non_trasmesso(): void
    {
        $this->post('/questura/ws/verify', $this->period())->assertRedirect();
        $verify = QuesturaTransmission::where('mode', 'verify')->firstOrFail();
        $calls = count($this->double->calls);
        $this->post('/questura/ws/receipt/'.$verify->id)->assertStatus(409);
        $this->post('/questura/ws/receipt/999999')->assertNotFound();
        $this->assertSame($calls, count($this->double->calls));
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertNotNull($verify->fresh()->payload);
        $this->assertNull($verify->fresh()->finalized_at);
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
    }
}
