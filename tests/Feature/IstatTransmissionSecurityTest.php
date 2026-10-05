<?php

namespace Tests\Feature;

use App\Http\Controllers\IstatTabellaAController;
use App\Models\IstatTransmission;
use App\Models\Struttura;
use App\Services\IstatTabellaAService;
use App\Services\IstatWebService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Retains the normal fail-closed bootstrap. No real endpoint is permitted. */
class IstatTransmissionSecurityTest extends TestCase
{
    public function test_http_response_and_exception_never_expose_provider_material(): void
    {
        Http::preventStrayRequests();
        $struttura = new Struttura([
            'istat_username' => 'FIXTURE_USER', 'istat_password' => 'FIXTURE_PASSWORD',
            'istat_ws_url' => 'https://fixture.invalid', 'istat_ws_simulazione' => false,
        ]);
        $service = new IstatWebService(new IstatTabellaAService());
        Http::fake(['*' => Http::response('<password>FIXTURE_PASSWORD</password>', 200)]);
        $r = $service->send($struttura, '<movimenti><codice>FIXTURE</codice><prodotto>Fixture</prodotto><movimento><data>20260401</data><struttura><apertura>SI</apertura><camereoccupate>0</camereoccupate><cameredisponibili>1</cameredisponibili><lettidisponibili>1</lettidisponibili></struttura></movimento></movimenti>', Carbon::now(), Carbon::now());
        $this->assertSame('sent', $r['state']);
        $this->assertFalse($r['accepted']);
        $this->assertStringNotContainsString('FIXTURE_PASSWORD', json_encode($r));
        Http::fake(fn () => throw new \RuntimeException('FIXTURE_PASSWORD request SOAP'));
        $r = $service->send($struttura, '<movimenti><codice>FIXTURE</codice><prodotto>Fixture</prodotto><movimento><data>20260401</data><struttura><apertura>SI</apertura><camereoccupate>0</camereoccupate><cameredisponibili>1</cameredisponibili><lettidisponibili>1</lettidisponibili></struttura></movimento></movimenti>', Carbon::now(), Carbon::now());
        $this->assertSame('technical_error', $r['state']);
        $this->assertStringNotContainsString('FIXTURE_PASSWORD', json_encode($r));
    }

    public function test_simulation_and_receipt_are_not_official_acceptance(): void
    {
        Http::preventStrayRequests();
        Http::fake();
        $service = new IstatWebService(new IstatTabellaAService());
        $struttura = new Struttura(['istat_ws_simulazione' => true]);
        foreach (['verify', 'send'] as $mode) {
            $result = $service->$mode($struttura, '<xml/>', Carbon::now(), Carbon::now());
            $this->assertSame('simulation', $result['state']);
            $this->assertFalse($result['accepted']);
        }
        $this->assertArrayNotHasKey('receipt_binary', $service->receipt($struttura, Carbon::now()));
        Http::assertNothingSent();
    }

    public function test_historical_summary_and_serialization_do_not_expose_old_details(): void
    {
        $tx = new IstatTransmission([
            'status' => 'success', 'mode' => 'send',
            'result' => ['raw' => ['soap' => 'FIXTURE_PASSWORD']],
            'response_message' => 'FIXTURE_PASSWORD', 'response_detail' => 'FIXTURE_PASSWORD',
        ]);
        $before = $tx->getAttributes();
        $controller = new IstatTabellaAController(new IstatTabellaAService(), new IstatWebService(new IstatTabellaAService()));
        $method = new \ReflectionMethod($controller, 'buildOperatorReceiptPdf');
        $pdf = $method->invoke($controller, new Struttura(), $tx);
        $this->assertStringContainsString('NON e una ricevuta ufficiale', $pdf);
        $this->assertStringNotContainsString('FIXTURE_PASSWORD', $pdf);
        $this->assertStringNotContainsString('FIXTURE_PASSWORD', $tx->toJson());
        $this->assertSame($before, $tx->getAttributes());
        $this->assertSame('unknown', $tx->esitoSicuro()['state']);
    }
}
