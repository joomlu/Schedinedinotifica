<?php
// Standalone: php -n, no Composer/Laravel/PDO/browser or real SOAP transport.
namespace {
    if (!class_exists('SoapClient')) { class SoapClient {} }
    if (!function_exists('mb_strtolower')) { function mb_strtolower($value) { return strtolower($value); } }
    function abort($status, $message) { throw new \RuntimeException($message, $status); }
    function config($key) { return $key === 'questura.enabled' ? true : null; }
    function now() { return 'FIXTURE_TIME'; }
    class FakeSoapClient extends \SoapClient {
        public static array $calls = [];
        public static bool $fail = false;
        public static bool $callFail = false;
        public static bool $reject = false;
        public static array $options = [];
        public function __construct($wsdl, $options) {
            self::$options = $options;
            if (self::$fail) { throw new \RuntimeException('SECRET constructor password'); }
        }
        public function __soapCall(string $method, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed {
            self::$calls[] = $method;
            if (self::$callFail) { throw new \RuntimeException('SECRET_TOKEN SOAP request'); }
            if ($method === 'GenerateToken') { return (object)['GenerateTokenResult'=>(object)['token'=>'SECRET_TOKEN', 'expires'=>date(DATE_ATOM, time()+3600)], 'result'=>(object)['esito'=>true]]; }
            if ($method === 'Authentication_Test') { return (object)['Authentication_TestResult'=>(object)['esito'=>true]]; }
            if (self::$reject) { return (object)[$method.'Result'=>(object)['esito'=>false, 'ErroreDettaglio'=>'SECRET_TOKEN']]; }
            return (object)[$method.'Result'=>(object)['esito'=>true], 'result'=>(object)['SchedineValide'=>1, 'Dettaglio'=>(object)['EsitoOperazioneServizio'=>[(object)['esito'=>true]]]], 'HTTP'=>200, 'accepted'=>true, 'message'=>'SECRET_PASSWORD',
                'nested'=>(object)['SOAP'=>'SECRET_SOAP','Authorization'=>'SECRET_TOKEN']];
        }
    }
}
namespace Carbon { class Carbon { public function toDateString() { return '2026-01-01'; } } }
namespace Illuminate\Http { class Request {} }
namespace App\Http\Controllers { class Controller {} }
namespace App\Models {
    class Struttura {
        public bool $questura_ws_simulazione = false;
        public string $questura_username = 'SECRET_USER';
        public string $questura_password = 'SECRET_PASSWORD';
        public string $questura_wskey = 'SECRET_KEY';
    }
    class QuesturaTransmission {
        public static array $saved = [];
        public static function query() { return new self(); }
        public function create($data) { self::$saved = $data; return $this; }
    }
}
namespace App\Services { class QuesturaTxtExportService {} class QuesturaRetentionService {} }
namespace {
    require __DIR__.'/../../app/Exceptions/QuesturaTransportDisabledException.php';
    require __DIR__.'/../../app/Services/EsitoTrasmissioneQuestura.php';
    require __DIR__.'/../../app/Services/QuesturaWebService.php';
    require __DIR__.'/../../app/Http/Controllers/QuesturaExportController.php';
    require __DIR__.'/../../app/Http/Controllers/ArchivosController.php';
    use App\Services\EsitoTrasmissioneQuestura as Esito;
    function check($ok) { if (!$ok) { throw new \RuntimeException('Safety assertion failed'); } }
    function safe($value) { check(!str_contains(json_encode($value, JSON_THROW_ON_ERROR), 'SECRET')); }
    $s = new \App\Models\Struttura();
    $ws = new class extends \App\Services\QuesturaWebService {
        protected function isSimulation(\App\Models\Struttura $s): bool { return $s->questura_ws_simulazione; }
        protected function makeClient(): \SoapClient {
            // Never invoke the native constructor or parent transport.
            return new \FakeSoapClient('fixture', ['trace'=>false]);
        }
    };
    $tests = [];
    $tests['SOAP 200 and positive body never prove acceptance; no token/body leaves service'] = function () use ($ws, $s) {
        $r = $ws->send($s, 'SECRET_TXT'); safe($r);
        check($r['state']==='sent' && $r['accepted']===false && $r['ok']===false);
        check(FakeSoapClient::$calls===['GenerateToken','Authentication_Test','Send']);
        check(FakeSoapClient::$options['trace']===false);
        check(str_contains(file_get_contents(__DIR__.'/../../app/Services/QuesturaWebService.php'), "'trace' => false"));
    };
    $tests['verification is unknown and retains Test operation'] = function () use ($ws,$s) {
        $r=$ws->verify($s,'SECRET_TXT'); safe($r); check($r['state']==='unknown');
        check(end(FakeSoapClient::$calls)==='Test');
    };
    $tests['external constructor exception is contained'] = function () use ($ws,$s) {
        FakeSoapClient::$fail=true;
        try { $r=$ws->send($s,'SECRET_TXT'); safe($r); check($r['state']==='technical_error'); }
        finally { FakeSoapClient::$fail=false; }
    };
    $tests['SOAP call exceptions do not escape with their chain'] = function () use ($ws,$s) {
        FakeSoapClient::$callFail=true;
        try { $r=$ws->send($s,'SECRET_TXT'); safe($r); check($r['state']==='technical_error'); }
        finally { FakeSoapClient::$callFail=false; }
    };
    $tests['negative provider text is replaced with a fixed message'] = function () use ($ws,$s) {
        FakeSoapClient::$reject=true;
        try { $r=$ws->send($s,'SECRET_TXT'); safe($r); check($r['state']==='rejected'); }
        finally { FakeSoapClient::$reject=false; }
    };
    $tests['simulation makes no SOAP calls and is never accepted'] = function () use ($ws,$s) {
        $s->questura_ws_simulazione=true; $before=FakeSoapClient::$calls;
        try { foreach(['verify','send'] as $m) { $r=$ws->$m($s,'SECRET_TXT'); safe($r); check($r['state']==='simulation' && !$r['accepted']); } }
        finally { $s->questura_ws_simulazione=false; }
        check($before===FakeSoapClient::$calls);
    };
    $tests['nested and unexpected fields discarded; caller acceptance ignored'] = function () {
        $r=Esito::sanifica((object)['state'=>'sent','mode'=>'send','accepted'=>true,
            'context'=>['token'=>'SECRET_TOKEN', 'expires'=>date(DATE_ATOM, time()+3600)],'raw'=>(object)['SOAP'=>'SECRET_SOAP'],
            'message'=>'SECRET_PASSWORD','transport'=>['headers'=>['Authorization'=>'SECRET']]]);
        safe($r); check(!$r['accepted'] && count($r)===6);
    };
    $tests['allowed slots cannot carry arrays objects or arbitrary strings'] = function () {
        $r=Esito::sanifica(['state'=>['SECRET'], 'mode'=>(object)['SECRET'=>'SECRET']]);
        safe($r); check($r['state']==='unknown');
        safe(Esito::sanifica(['state'=>'SECRET','mode'=>'SECRET']));
    };
    $tests['historical success never becomes accepted; history not rewritten'] = function () {
        $old=['raw'=>'SECRET','simulated'=>false]; $before=$old;
        $r=Esito::storico('success',$old); safe($r); check($r['state']==='unknown' && !$r['accepted'] && $before===$old);
        check(Esito::storico('error',[])['state']==='historical_error');
        check(Esito::storico('success',['simulated'=>true])['state']==='simulation');
    };
    $tests['receipt and tables cannot expose or persist unvalidated bodies'] = function () use ($ws,$s) {
        $before=FakeSoapClient::$calls;
        $s->questura_ws_simulazione=true;
        foreach([$ws->receipt($s,new \Carbon\Carbon()),$ws->downloadReferenceTables($s)] as $r) {
            safe($r); check($r['state']==='unavailable' && count($r)===6);
        }
        $s->questura_ws_simulazione=false;
        check(FakeSoapClient::$calls===$before);
    };
    $tests['actual persistence boundary projects injected provider material'] = function () use ($ws) {
        $controller=new \App\Http\Controllers\QuesturaExportController(new \App\Services\QuesturaTxtExportService(),$ws,new \App\Services\QuesturaRetentionService());
        $m=new \ReflectionMethod($controller,'storeTransmission');
        $m->invoke($controller,1,null,null,'send',new \Carbon\Carbon(),new \Carbon\Carbon(),[],0,[],
            ['state'=>'sent','raw'=>'SECRET','context'=>['token'=>'SECRET'],'accepted'=>true],
            'SECRET','SECRET','SECRET','SECRET');
        safe(\App\Models\QuesturaTransmission::$saved);
        check(\App\Models\QuesturaTransmission::$saved['response_detail']===null);
    };
    $tests['legacy handler aborts before any DB or storage dependency'] = function () {
        try { (new \App\Http\Controllers\ArchivosController())->generarArchivoHospedados(); check(false); }
        catch (\RuntimeException $e) { check($e->getCode()===410); }
    };
    $tests['controller has no confirmed-send mutation for simulation or attempts'] = function () {
        $src=file_get_contents(__DIR__.'/../../app/Http/Controllers/QuesturaExportController.php');
        foreach(['questura_sent_at', 'last_questura_transmission_id', "'questura_send_count' =>", 'getMessage()', 'receipt_binary', "Arr::except"] as $forbidden) { check(!str_contains($src,$forbidden)); }
        check(str_contains($src,"'questura_export_count' =>"));
    };
    $tests['view never presents legacy provider messages or success as acceptance'] = function () {
        $src=file_get_contents(__DIR__.'/../../resources/views/questura/index.blade.php');
        check(!str_contains($src,'$tx->response_message') && !str_contains($src,"\$tx->status === 'success'"));
        check(str_contains($src,'$tx->esitoSicuro()'));
    };
    foreach($tests as $name=>$test) { $test(); echo 'PASS '.$name.PHP_EOL; }
    echo count($tests).' safety checks passed; in-memory doubles only'.PHP_EOL;
}
