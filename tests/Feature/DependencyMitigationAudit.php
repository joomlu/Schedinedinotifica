<?php
namespace Tests\Feature;

use App\Models\Struttura;
use App\Services\IstatWebService;
use App\Services\QuesturaWebService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DependencyMitigationAudit extends TestCase
{
    public function test_flag_enti_impediscono_trasporti_anche_senza_credenziali(): void
    {
        config(['istat.enabled' => false, 'questura.enabled' => false]);
        Http::preventStrayRequests();
        $s = new Struttura;
        $result = app(IstatWebService::class)->send($s, '<dato-sintetico/>', now(), now());
        $this->assertSame('disabled', $result['state']);
        try {
            app(QuesturaWebService::class)->assertTransportEnabled();
            $this->fail('Trasporto Questura non bloccato');
        } catch (\App\Exceptions\QuesturaTransportDisabledException $error) {
            $this->assertNotEmpty($error->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_debug_disattivato_non_espone_tracce_e_mail_resta_intercettata(): void
    {
        config(['app.debug' => false, 'mail.default' => 'array']);
        Mail::fake();
        $response = $this->withSession(['lang' => '../../lingua-sintetica'])->get('/checkin/token-sintetico-inesistente');
        $response->assertNotFound()->assertDontSee('vendor/laravel')->assertDontSee('Stack trace');
        $this->assertFalse(config('app.debug'));
        $this->assertSame('it', app()->getLocale());
        $this->assertFalse(class_exists(\Symfony\Component\HttpClient\NoPrivateNetworkHttpClient::class));
        $this->assertSame('array', config('mail.default'));
        Mail::assertNothingSent();
    }
}
