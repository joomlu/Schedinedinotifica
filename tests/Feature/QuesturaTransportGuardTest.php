<?php

namespace Tests\Feature;

use App\Models\GeoNazione;
use App\Models\QuesturaTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\QuesturaWebService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaTransportGuardTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_audit_off_web_rifiutato_non_persiste(): void
    {
        config(['questura.enabled' => false]);
        $structure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        $this->post('/questura/ws/verify')->assertRedirect(route('questura.index'))->assertSessionHasErrors('questura_ws');
        $this->assertSame(0, DB::table('struttura_audit_logs')->count());
    }

    public function test_audit_off_json_rifiutato_non_persiste(): void
    {
        config(['questura.enabled' => false]);
        $structure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        $this->postJson('/questura/ws/verify')->assertStatus(409);
        $this->assertSame(0, DB::table('struttura_audit_logs')->count());
    }

    public function test_redirect_non_questura_resta_auditato_anche_con_header_in_richiesta(): void
    {
        config(['questura.enabled' => false]);
        $structure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        // Endpoint sintetico: usa il vero gruppo web e il middleware audit, senza altri writer.
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth'])->post('/audit-fixture', fn () => redirect('/questura'))
            ->name('customer.audit_fixture');
        $this->withHeader(\App\Exceptions\QuesturaTransportDisabledException::RESPONSE_HEADER, '1')
            ->post('/audit-fixture')->assertRedirect('/questura');
        $this->assertSame(1, DB::table('struttura_audit_logs')->where('route_name', 'customer.audit_fixture')->count());
    }

    public function test_header_in_richiesta_non_sopprime_audit_questura_on(): void
    {
        config(['questura.enabled' => true]);
        $structure = $this->structureFor(null);
        $structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key', 'questura_ws_simulazione' => true]);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        $this->withHeader(\App\Exceptions\QuesturaTransportDisabledException::RESPONSE_HEADER, '1')
            ->post('/questura/ws/verify', ['dal' => today()->toDateString(), 'al' => today()->toDateString()])->assertRedirect();
        $this->assertSame(1, DB::table('struttura_audit_logs')->where('route_name', 'questura.ws.verify')->count());
    }

    public static function blockedOperations(): array
    {
        $cases = [];
        foreach (['verify', 'send', 'receipt', 'downloadReferenceTables'] as $operation) {
            foreach ([false, true] as $simulation) {
                foreach ([false, null, 'true'] as $enabled) {
                    $cases[] = [$operation, $simulation, $enabled];
                }
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('blockedOperations')]
    public function test_blocco_prima_di_simulazione_client_e_wsdl(string $operation, bool $simulation, mixed $enabled): void
    {
        config(['questura.enabled' => $enabled]);
        $service = new class extends QuesturaWebService
        {
            public int $constructed = 0;

            public int $simulationChecks = 0;

            protected function isSimulation(Struttura $structure): bool
            {
                $this->simulationChecks++;

                return parent::isSimulation($structure);
            }

            protected function makeClient(): \SoapClient
            {
                $this->constructed++;
                throw new \LogicException('Trasporto del test non raggiungibile');
            }
        };
        $structure = new Struttura(['questura_ws_simulazione' => $simulation]);
        $caught = null;
        try {
            match ($operation) {
                'receipt' => $service->receipt($structure, now()->subDay()),
                'downloadReferenceTables' => $service->downloadReferenceTables($structure),
                default => $service->{$operation}($structure, 'RIGA-SINTETICA'),
            };
        } catch (\RuntimeException $exception) {
            $caught = $exception;
        }
        $this->assertNotNull($caught, 'Operazione disabilitata deve essere rifiutata, senza esito simulato.');
        $this->assertSame('App\\Exceptions\\QuesturaTransportDisabledException', $caught::class);
        $this->assertSame(0, $service->constructed);
        $this->assertSame(0, $service->simulationChecks);
        $this->assertTrue($this->app->runningInConsole());
    }

    public static function simulationModes(): array
    {
        return [[false], [true]];
    }

    public static function httpModes(): array
    {
        $cases = [];
        foreach ([false, true] as $simulation) {
            foreach ([false, null, 'true'] as $enabled) {
                foreach ([false, true] as $json) {
                    $cases[] = [$simulation, $enabled, $json];
                }
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('httpModes')]
    public function test_http_off_non_crea_o_modifica_archivi(bool $simulation, mixed $enabled, bool $json): void
    {
        config(['questura.enabled' => $enabled]);
        $structure = $this->structureFor(null);
        $structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key', 'questura_ws_simulazione' => $simulation]);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $source = Schedina::create([
            'struttura_id' => $structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => 'Fixture', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
        $tx = QuesturaTransmission::create(['struttura_id' => $structure->id, 'mode' => 'send', 'status' => 'uncertain', 'executed_at' => now()->subDay()]);
        $export = \App\Models\QuesturaExport::create(['struttura_id' => $structure->id, 'dal' => today(), 'al' => today(), 'filename' => 'fixture.txt', 'created_at' => now()->subDays(2)]);
        DB::table('questura_receipts')->insert([
            'struttura_id' => $structure->id, 'questura_transmission_id' => $tx->id,
            'remote_date' => today()->subDay()->toDateString(), 'filename' => 'fixture.pdf', 'path' => 'fixture.pdf',
            'mime' => 'application/pdf', 'sha256' => hash('sha256', 'fixture'), 'byte_size' => 7,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $receiptsBefore = DB::table('questura_receipts')->get()->toJson();
        $before = DB::table('questura_transmissions')->get()->toJson();
        $exportsBefore = DB::table('questura_exports')->get()->toJson();
        $filesBefore = \Illuminate\Support\Facades\Storage::disk('local')->allFiles();
        $sourceBefore = $source->fresh()->getAttributes();
        foreach (['/questura/ws/verify', '/questura/ws/send', '/questura/ws/tables', '/questura/ws/receipt/'.$tx->id, '/questura/txt/'.$export->id.'/ricevuta'] as $path) {
            $data = ['dal' => today()->toDateString(), 'al' => today()->toDateString(), 'communication_date' => today()->subDay()->toDateString(), 'communication_confirmed' => true];
            $response = $json ? $this->postJson($path, $data) : $this->post($path, $data);
            if ($json) {
                $response->assertStatus(409)->assertExactJson(['message' => 'Trasporto Questura globalmente disabilitato.']);
            } else {
                $response->assertRedirect()->assertSessionHasErrors('questura_ws');
            }
            $this->assertSame(hash('sha256', $before), hash('sha256', DB::table('questura_transmissions')->get()->toJson()), 'Il blocco deve preservare tutti i record di trasmissione.');
            $this->assertSame(0, DB::table('struttura_audit_logs')->count());
            $this->assertSame(0, DB::table('questura_transmission_events')->count());
            $this->assertSame(0, DB::table('questura_send_reservations')->count());
            $this->assertSame($receiptsBefore, DB::table('questura_receipts')->get()->toJson());
            $this->assertSame($sourceBefore, $source->fresh()->getAttributes());
            $this->assertSame($exportsBefore, DB::table('questura_exports')->get()->toJson());
            $this->assertSame($filesBefore, \Illuminate\Support\Facades\Storage::disk('local')->allFiles());
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('simulationModes')]
    public function test_on_supera_guardia_solo_con_double_offline(bool $simulation): void
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        config(['questura.enabled' => true]);
        $double = new QuesturaSoapDouble;
        $service = new class($double, $simulation) extends QuesturaWebService
        {
            public function __construct(private \SoapClient $double, private bool $simulation) {}

            protected function isSimulation(Struttura $structure): bool
            {
                return $this->simulation;
            }

            protected function makeClient(): \SoapClient
            {
                return $this->double;
            }
        };
        $structure = new Struttura(['questura_username' => 'fixture', 'questura_password' => 'fixture', 'questura_wskey' => 'fixture']);
        $this->assertSame($simulation ? 'simulation' : 'unknown', $service->verify($structure, 'RIGA')['state']);
        $this->assertSame($simulation ? 'simulation' : 'sent', $service->send($structure, 'RIGA')['state']);
        $this->assertSame($simulation ? 'unavailable' : 'receipt_available', $service->receipt($structure, now()->subDay())['state']);
        $this->assertSame('unavailable', $service->downloadReferenceTables($structure)['state']);
        $this->assertCount($simulation ? 0 : 8, $double->calls);
    }

    public static function finalizationModes(): array
    {
        return ['OFF Web' => [false, false], 'OFF JSON' => [false, true], 'ON Web' => [true, false], 'ON JSON' => [true, true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('finalizationModes')]
    public function test_finalizzazione_off_preserva_db_file_e_non_invoca_retention(bool $enabled, bool $json): void
    {
        config(['questura.enabled' => $enabled]);
        $structure = $this->structureFor(null);
        $actor = $this->actor('struttura_user', null, $structure->id);
        $this->actingAs($actor);
        $day = now()->subDay()->startOfDay();
        $txt = 'TXT-FINALIZZAZIONE-SINTETICO';
        $pdf = "%PDF-1.4\nRICEVUTA-FINALIZZAZIONE-SINTETICA\n%%EOF";
        $path = 'questura/struttura_'.$structure->id.'/finalize-fixture.txt';
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->put($path, $txt);
        $hash = hash('sha256', $txt);
        $export = \App\Models\QuesturaExport::create(['struttura_id' => $structure->id, 'dal' => $day, 'al' => $day, 'filename' => 'finalize-fixture.txt', 'path' => $path, 'sha256' => $hash, 'byte_size' => strlen($txt), 'status' => 'generated', 'created_at' => $day]);
        $tx = QuesturaTransmission::create(['struttura_id' => $structure->id, 'mode' => 'send', 'status' => 'sent', 'executed_at' => $day, 'sha256' => $hash, 'payload' => ['transport_mode' => 'live', 'txt_base64' => base64_encode($txt)], 'result' => ['fixture' => 'sintetica']]);
        DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $tx->id, 'status' => 'sent', 'result' => json_encode(['fixture' => 'sintetica']), 'created_at' => now()]);
        DB::table('questura_send_reservations')->insert(['struttura_id' => $structure->id, 'schedina_id' => 901, 'transport_mode' => 'live', 'questura_transmission_id' => $tx->id, 'created_at' => now()]);
        $receipt = app(\App\Services\QuesturaRetentionService::class)->archiveReceipt($structure, $day, $pdf, $tx->id);
        $retention = new class extends \App\Services\QuesturaRetentionService
        {
            public int $calls = 0;

            public function finalizeTransmission(int $structureId, int $id, int $receiptId, int $actorId, bool $reconciled = false): void
            {
                $this->calls++;
                parent::finalizeTransmission($structureId, $id, $receiptId, $actorId, $reconciled);
            }
        };
        $this->app->instance(\App\Services\QuesturaRetentionService::class, $retention);
        $tables = ['questura_transmissions', 'questura_exports', 'questura_transmission_events', 'questura_receipts', 'questura_send_reservations', 'schedina', 'struttura_audit_logs'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = hash('sha256', DB::table($table)->orderBy('id')->get()->toJson());
        }
        $filesBefore = [];
        foreach ($disk->allFiles() as $file) {
            $filesBefore[$file] = hash('sha256', $disk->get($file));
        }
        $url = '/questura/ws/'.$tx->id.'/finalizza';
        $response = $json ? $this->postJson($url, ['reconciled' => true]) : $this->post($url, ['reconciled' => true]);
        if ($enabled) {
            $this->assertSame(1, DB::table('struttura_audit_logs')->count());
            $response->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame(1, $retention->calls);
            $this->assertNotNull($tx->fresh()->finalized_at);
            $this->assertNotNull($export->fresh()->finalized_at);
            $this->assertFalse($disk->exists($path));
            $this->assertSame($pdf, $disk->get($receipt->path));

            return;
        }
        $this->assertSame(0, $retention->calls, 'OFF: retention invocata='.$retention->calls.', TXT presente='.(int) $disk->exists($path).', payload eliminato='.(int) ($tx->fresh()->payload === null).', eventi='.DB::table('questura_transmission_events')->count());
        if ($json) {
            $response->assertStatus(409)->assertExactJson(['message' => 'Trasporto Questura globalmente disabilitato.']);
        } else {
            $response->assertRedirect()->assertSessionHasErrors('questura_ws');
        }
        foreach ($tables as $table) {
            $this->assertSame($before[$table], hash('sha256', DB::table($table)->orderBy('id')->get()->toJson()), $table.' modificata con OFF');
        }
        $filesAfter = [];
        foreach ($disk->allFiles() as $file) {
            $filesAfter[$file] = hash('sha256', $disk->get($file));
        }
        $this->assertSame($filesBefore, $filesAfter);
        $this->assertSame($txt, $disk->get($path));
        $this->assertSame($pdf, $disk->get($receipt->path));
    }

    public static function exportModes(): array
    {
        $cases = [];
        foreach (['periodo', 'schedina'] as $scope) {
            foreach ([false, true] as $enabled) {
                foreach ([false, true] as $json) {
                    $cases[$scope.' '.($enabled ? 'ON' : 'OFF').' '.($json ? 'JSON' : 'Web')] = [$scope, $enabled, $json];
                }
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('exportModes')]
    public function test_export_off_blocca_prima_del_payload_e_on_conserva_download(string $scope, bool $enabled, bool $json): void
    {
        config(['questura.enabled' => $enabled]);
        $structure = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $source = Schedina::create([
            'struttura_id' => $structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => 'Fixture export', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777',
        ])->fresh();
        $builder = new class extends \App\Services\QuesturaTxtExportService
        {
            public int $generated = 0;

            public function buildTxt(\Illuminate\Support\Collection $analysis): string
            {
                $this->generated++;

                return parent::buildTxt($analysis);
            }

            public function buildTxtPerSchedina(Schedina $source): string
            {
                $this->generated++;

                return parent::buildTxtPerSchedina($source);
            }
        };
        $this->app->instance(\App\Services\QuesturaTxtExportService::class, $builder);
        $tables = ['schedina', 'questura_exports', 'questura_transmissions', 'questura_transmission_events', 'questura_receipts', 'questura_send_reservations', 'struttura_audit_logs'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = hash('sha256', DB::table($table)->orderBy('id')->get()->toJson());
        }
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->put('questura/export-preservato.txt', 'SNAPSHOT-SINTETICO-PREESISTENTE');
        $filesBefore = [];
        foreach ($disk->allFiles() as $file) {
            $filesBefore[$file] = hash('sha256', $disk->get($file));
        }
        $url = $scope === 'periodo' ? '/questura/download/periodo' : '/questura/download/schedina/'.$source->id;
        $data = ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
        $response = $json ? $this->postJson($url, $data) : $this->post($url, $data);
        if ($enabled) {
            $this->assertSame(1, DB::table('struttura_audit_logs')->count());
            // Entrambi i POST restituiscono TXT anche con Accept JSON: contratto ON preesistente.
            $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
            $this->assertStringStartsWith('attachment;', $response->headers->get('Content-Disposition'));
            $this->assertSame(1, $builder->generated);
            $this->assertSame(1, DB::table('questura_exports')->count());
            $export = \App\Models\QuesturaExport::firstOrFail();
            $this->assertSame($response->getContent(), $disk->get($export->path));
            $this->assertSame(168, strlen($response->getContent()));
            $this->assertSame(1, (int) $source->fresh()->questura_export_count);
            $this->assertSame($export->id, (int) $source->fresh()->last_questura_export_id);

            return;
        }
        $this->assertSame(0, $builder->generated, 'OFF: payload generati='.$builder->generated.', export='.DB::table('questura_exports')->count().', TXT='.count($disk->allFiles('questura')).', contatore Schedina='.(int) $source->fresh()->questura_export_count);
        if ($json) {
            $response->assertStatus(409)->assertExactJson(['message' => 'Trasporto Questura globalmente disabilitato.']);
        } else {
            $response->assertRedirect()->assertSessionHasErrors('questura_ws');
        }
        foreach ($tables as $table) {
            $this->assertSame($before[$table], hash('sha256', DB::table($table)->orderBy('id')->get()->toJson()), $table.' modificata con OFF');
        }
        $filesAfter = [];
        foreach ($disk->allFiles() as $file) {
            $filesAfter[$file] = hash('sha256', $disk->get($file));
        }
        $this->assertSame($filesBefore, $filesAfter);
    }

    public function test_default_config_e_false_senza_variabile(): void
    {
        $this->assertFalse((require config_path('questura.php'))['enabled']);
    }
}
