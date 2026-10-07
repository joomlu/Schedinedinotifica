<?php

namespace Tests\Feature;

use App\Models\Struttura;
use App\Services\QuesturaWebService;
use Tests\TestCase;

class QuesturaSoapDouble extends \SoapClient
{
    public array $calls = [];
    public ?string $fail = null;
    public bool $malformed = false;
    public bool $partial = false;
    public function __construct() {}
    public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
    {
        $this->calls[] = [$name, $args[0]];
        if ($this->fail === $name) { throw new \RuntimeException('SEGRETO-SINTETICO'); }
        if ($name === 'GenerateToken') {
            return (object)['GenerateTokenResult' => (object)['token' => 'TOKEN-SINTETICO', 'expires' => date(DATE_ATOM, time()+3600)], 'result' => (object)['esito' => true]];
        }
        if ($this->malformed && $name === 'Send') { return (object)['HTTP' => 200]; }
        if ($this->partial && $name === 'Send') {
            return (object)['SendResult' => (object)['esito' => true], 'result' => (object)['SchedineValide' => 1, 'Dettaglio' => (object)['EsitoOperazioneServizio' => [(object)['esito' => true], (object)['esito' => false, 'ErroreCod' => '12', 'ErroreDettaglio' => 'SEGRETO-SINTETICO']]]]];
        }
        if ($name === 'Ricevuta') {
            return (object)['RicevutaResult' => (object)['esito' => true], 'PDF' => "%PDF-1.4\nSINTETICO\n%%EOF"];
        }
        return (object)[$name.'Result' => (object)['esito' => true], 'result' => (object)['SchedineValide' => 1, 'Dettaglio' => (object)['EsitoOperazioneServizio' => [(object)['esito' => true]]]]];
    }
}

class QuesturaWsContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['questura.enabled' => true]);
    }

    private function service(QuesturaSoapDouble $double): QuesturaWebService
    {
        return new class($double) extends QuesturaWebService {
            public function __construct(private QuesturaSoapDouble $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        };
    }

    public function test_contratto_parametri_lista_righe_e_segreti_non_escono(): void
    {
        $double = new QuesturaSoapDouble(); $ws = $this->service($double);
        $s = new Struttura(['questura_username' => 'UTENTE-SINTETICO', 'questura_password' => 'PASSWORD-SINTETICA', 'questura_wskey' => 'WSKEY-SINTETICA']);
        $r = $ws->send($s, str_repeat('X', 168));
        $this->assertSame(['GenerateToken', 'Authentication_Test', 'Send'], array_column($double->calls, 0));
        $this->assertSame('PASSWORD-SINTETICA', $double->calls[0][1]['Password']);
        $this->assertSame('UTENTE-SINTETICO', $double->calls[1][1]['Utente']);
        $this->assertSame(['string' => [str_repeat('X', 168)]], $double->calls[2][1]['ElencoSchedine']);
        $this->assertSame('sent', $r['state']);
        $this->assertFalse($r['accepted']);
        $this->assertStringNotContainsString('SINTETICO', json_encode($r));
    }

    public function test_timeout_e_risposta_malformata_send_sono_incerti_senza_retry(): void
    {
        $s = new Struttura();
        foreach (['timeout', 'malformed'] as $scenario) {
            $double = new QuesturaSoapDouble();
            $double->fail = $scenario === 'timeout' ? 'Send' : null;
            $double->malformed = $scenario === 'malformed';
            $r = $this->service($double)->send($s, 'RIGA-SINTETICA');
            $this->assertSame('uncertain', $r['state']);
            $this->assertCount(3, $double->calls);
            $this->assertStringNotContainsString('SEGRETO', json_encode($r));
        }
        $double = new QuesturaSoapDouble(); $double->fail = 'GenerateToken';
        $this->assertSame('technical_error', $this->service($double)->send($s, 'RIGA')['state']);
        $this->assertCount(1, $double->calls);
    }

    public function test_test_non_e_send_e_ricevuta_solo_giorni_documentati(): void
    {
        $double = new QuesturaSoapDouble(); $ws = $this->service($double); $s = new Struttura();
        $this->assertSame('unknown', $ws->verify($s, 'RIGA')['state']);
        $this->assertSame('Test', $double->calls[2][0]);
        $before = count($double->calls);
        $this->assertSame('unavailable', $ws->receipt($s, now())['state']);
        $this->assertSame($before, count($double->calls));
        $r = $ws->receipt($s, now()->subDay());
        $this->assertSame('receipt_available', $r['state']);
        $this->assertSame("%PDF-1.4\nSINTETICO\n%%EOF", $r['bytes']);
        $this->assertSame('Ricevuta', end($double->calls)[0]);
    }
    public function test_acquisizione_parziale_e_errori_per_riga_sanitizzati(): void
    {
        $double = new QuesturaSoapDouble(); $double->partial = true;
        $r = $this->service($double)->send(new Struttura(), "RIGA1\r\nRIGA2");
        $this->assertSame('partial', $r['state']);
        $this->assertSame(1, $r['valid_rows']);
        $this->assertSame([['row' => 2, 'code' => '12']], $r['row_errors']);
        $this->assertStringNotContainsString('SEGRETO', json_encode($r));
        $this->assertFalse($r['accepted']);
    }

    public function test_serializzazione_soap_e_pdf_sul_wsdl_ufficiale_offline(): void
    {
        $client = new QuesturaOfflineSoapClient();
        $service = new class($client) extends QuesturaWebService {
            public function __construct(private QuesturaOfflineSoapClient $client) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->client; }
        };
        $model = new Struttura(['questura_username' => 'UTENTE-SINTETICO', 'questura_password' => 'PASSWORD-SINTETICA', 'questura_wskey' => 'WSKEY-SINTETICA']);
        $result = $service->send($model, str_repeat('X', 168));
        $this->assertSame('sent', $result['state']);
        $this->assertSame(1, $result['valid_rows']);
        $xml = new \DOMDocument(); $xml->loadXML($client->requests[2]);
        $xpath = new \DOMXPath($xml);
        $this->assertSame(1, $xpath->query('//*[local-name()="ElencoSchedine"]/*[local-name()="string"]')->length);
        $this->assertSame('UTENTE-SINTETICO', $xpath->evaluate('string(//*[local-name()="Send"]/*[local-name()="Utente"])'));
        $receipt = $service->receipt($model, now()->subDay());
        $this->assertSame("%PDF-1.4\nSINTETICO\n%%EOF", $receipt['bytes']);
        $this->assertCount(5, $client->requests);
    }

}

class QuesturaOfflineSoapClient extends \SoapClient
{
    public array $requests = [];
    public function __construct()
    {
        parent::__construct(base_path('reference/questura/service.wsdl'), ['soap_version' => SOAP_1_2, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS]);
    }
    public function __doRequest(string $request, string $location, string $action, int $version, bool $oneWay = false): ?string
    {
        $this->requests[] = $request;
        $method = substr($action, strrpos($action, '/') + 1);
        $body = match ($method) {
            'GenerateToken' => '<GenerateTokenResult><issued>'.date(DATE_ATOM).'</issued><expires>'.date(DATE_ATOM, time()+3600).'</expires><token>TOKEN-SINTETICO</token></GenerateTokenResult><result><esito>true</esito></result>',
            'Authentication_Test' => '<Authentication_TestResult><esito>true</esito></Authentication_TestResult>',
            'Send', 'Test' => '<'.$method.'Result><esito>true</esito></'.$method.'Result><result><SchedineValide>1</SchedineValide><Dettaglio><EsitoOperazioneServizio><esito>true</esito></EsitoOperazioneServizio></Dettaglio></result>',
            'Ricevuta' => '<RicevutaResult><esito>true</esito></RicevutaResult><PDF>'.base64_encode("%PDF-1.4\nSINTETICO\n%%EOF").'</PDF>',
            default => throw new \LogicException('Metodo offline inatteso'),
        };
        return '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"><soap:Body><'.$method.'Response xmlns="AlloggiatiService">'.$body.'</'.$method.'Response></soap:Body></soap:Envelope>';
    }
}
