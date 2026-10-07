<?php

// Standalone safety probe: no Composer, Laravel, PDO, sockets, PHPUnit or browser.
namespace App\Models {
    class Struttura {
        public string $istat_username = 'SYNTHETIC_USER';
        public string $istat_password = 'SYNTHETIC_PASSWORD';
        public string $istat_codice_struttura = 'FIXTURE';
        public string $istat_ws_url = 'https://datiturismo.regione.emilia-romagna.it/ws/checkinV2';
        public string $regione = 'Emilia-Romagna';
    }
}
namespace Carbon { class Carbon {} }
namespace App\Services {
    class IstatTabellaAService {
        public function buildSoapEnvelope($struttura, $xml, $mode): string {
            return '<password>'.$struttura->istat_password.'</password>'.$xml;
        }
    }
}
namespace Illuminate\Support\Facades {
    class Http {
        public static int $calls = 0;
        public static int $status = 200;
        public static string $body = '';
        public static bool $fail = false;
        public static bool $redirectsBlocked = false;
        public static function withBasicAuth($user, $password): self { return new self(); }
        public function withHeaders($headers): self { return $this; }
        public function withoutRedirecting(): self { self::$redirectsBlocked = true; return $this; }
        public function timeout($seconds): self { return $this; }
        public function send($method, $url, $options): self {
            self::$calls++;
            if (self::$fail) {
                throw new \RuntimeException('SYNTHETIC_PASSWORD Authorization Cookie '.$url.$options['body']);
            }
            return $this;
        }
        public function status(): int { return self::$status; }
        public function body(): string { return self::$body; }
    }
}
namespace {
    $GLOBALS['istat_enabled'] = true;
    function config($key, $default = null) { return $key === 'istat.enabled' ? $GLOBALS['istat_enabled'] : $default; }
    function blank($value): bool { return $value === null || $value === ''; }
    function base_path($path): string { return __DIR__.'/../../'.$path; }
    require __DIR__.'/../../app/Services/IstatXmlValidator.php';
    require __DIR__.'/../../app/Services/IstatResponseParser.php';
    require __DIR__.'/../../app/Services/EsitoTrasmissioneIstat.php';
    require __DIR__.'/../../app/Services/IstatWebService.php';
    use App\Services\EsitoTrasmissioneIstat as Esito;
    use Illuminate\Support\Facades\Http;
    function check(bool $value): void { if (!$value) throw new \RuntimeException('Safety assertion failed'); }
    function safe(array $value): void {
        $json = json_encode($value, JSON_THROW_ON_ERROR);
        foreach (['SYNTHETIC_', 'password', 'Authorization', 'Cookie', '<soap', 'fixture.invalid'] as $secret) {
            check(!str_contains($json, $secret));
        }
    }
    $service = new \App\Services\IstatWebService(new \App\Services\IstatTabellaAService());
    $struttura = new \App\Models\Struttura();
    $date = new \Carbon\Carbon();
    $tests = [];
    $tests['provider echo cannot enter result'] = function () use ($service, $struttura, $date) {
        Http::$body = '<soap><password>SYNTHETIC_PASSWORD</password><user>SYNTHETIC_USER</user><cookie>SYNTHETIC_COOKIE</cookie></soap>';
        $r = $service->send($struttura, '<xml/>', $date, $date);
        safe($r); check($r['state'] === 'uncertain' && !$r['accepted'] && !$r['ok']);
    };
    $tests['controlled exception discards request and URL'] = function () use ($service, $struttura, $date) {
        Http::$fail = true;
        try { $r = $service->send($struttura, '<xml/>', $date, $date); }
        finally { Http::$fail = false; }
        safe($r); check($r['state'] === 'uncertain');
    };
    $tests['recursive arrays and objects are projected'] = function () {
        $r = Esito::sanifica((object)['state'=>'sent', 'message'=>'SYNTHETIC_PASSWORD',
            'transport'=>(object)['http_status'=>200, 'headers'=>['Authorization'=>'SYNTHETIC_TOKEN']],
            'nested'=>['more'=>(object)['cookie'=>'SYNTHETIC_COOKIE']]]);
        safe($r); check($r['transport'] === ['http_status'=>200]);
    };
    $tests['secret in allowed slots is discarded'] = function () {
        $r=Esito::sanifica(['state'=>'SYNTHETIC_PASSWORD','mode'=>'SYNTHETIC_USER','transport'=>['http_status'=>'SYNTHETIC_TOKEN']]);
        safe($r); check($r['state']==='unknown' && $r['transport']['http_status']===null);
    };
    $tests['objects cannot execute magic serialization'] = function () {
        $value = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed { throw new \RuntimeException('must not run'); }
        };
        check(Esito::sanifica($value)['state']==='unknown');
    };
    foreach (['<accepted>true</accepted>', '', '<html>Login</html>'] as $i=>$body) {
        $tests['HTTP 200 never proves acceptance '.$i] = function () use ($body) {
            check(Esito::daHttp(200,$body,'send')['accepted']===false);
        };
    }
    foreach (['<SOAP:Fault>SYNTHETIC_PASSWORD</SOAP:Fault>', '<error>bad</error>'] as $i=>$body) {
        $tests['negative response '.$i] = function () use ($body) {
            $r=Esito::daHttp(200,$body,'send'); safe($r); check($r['state']==='uncertain' && !$r['ok']);
        };
    }
    foreach ([302,401,500] as $code) {
        $tests['HTTP failure '.$code] = function () use ($code) { check(Esito::daHttp($code,'','send')['state']==='uncertain'); };
    }
    $tests['verify is unknown, never accepted'] = function () { check(Esito::daHttp(200,'','verify')['state']==='uncertain'); };
    $tests['OFF does not contact provider'] = function () use ($service,$struttura,$date) {
        $GLOBALS['istat_enabled'] = false; $calls = Http::$calls;
        try {
            $r = $service->send($struttura, '<xml/>', $date, $date);
            check($r['state'] === 'disabled' && !$r['accepted']); safe($r);
            check(Http::$calls === $calls);
        } finally { $GLOBALS['istat_enabled'] = true; }
    };
    $tests['no fabricated receipt with ON or OFF'] = function () use ($service,$struttura,$date) {
        foreach ([true,false] as $sim) {
            $GLOBALS['istat_enabled']=$sim;
            $r=$service->receipt($struttura,$date);
            check(!isset($r['receipt_binary']) && !$r['accepted']);
        }
    };
    $GLOBALS['istat_enabled']=true;
    $tests['legacy success is not official acceptance'] = function () {
        $legacy=['raw'=>['soap'=>'SYNTHETIC_PASSWORD'],'detail'=>'SYNTHETIC_TOKEN']; $copy=$legacy;
        $r=Esito::storico('success',$legacy); safe($r);
        check($r['state']==='unknown' && $legacy===$copy);
        check(Esito::storico('success',['simulated'=>true])['state']==='simulation');
    };
    $tests['legacy error remains a failure without rewriting history'] = function () {
        $legacy=['detail'=>'SYNTHETIC_PASSWORD']; $copy=$legacy;
        $r=Esito::storico('error',$legacy); safe($r);
        check($r['state']==='historical_error' && !$r['accepted'] && !$r['ok']);
        check($legacy===$copy && Esito::sanifica($r)===$r);
    };
    $tests['simulation takes precedence over legacy error'] = function () {
        foreach ([['simulated'=>true], ['state'=>'simulation']] as $legacy) {
            $r=Esito::storico('error',$legacy);
            check($r['state']==='simulation' && !$r['accepted']);
        }
    };
    $tests['accepted supplied by caller is not trusted'] = function () { check(Esito::sanifica(['state'=>'accepted','accepted'=>true])['state']==='unknown'); };
    $tests['redirects disabled'] = function () { check(Http::$redirectsBlocked); };
    $tests['projection is idempotent'] = function () { $r=Esito::crea('sent','send',200); check(Esito::sanifica($r)===$r); };
    foreach ($tests as $name=>$test) { $test(); echo 'PASS '.$name.PHP_EOL; }
    echo count($tests).' safety checks passed (in-memory doubles; no application bootstrap)'.PHP_EOL;
}
