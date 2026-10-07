<?php

namespace Tests\Feature;

use App\Models\QuesturaExport;
use App\Models\QuesturaTransmission;
use App\Models\Struttura;
use App\Services\QuesturaWebService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaUxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['questura.enabled' => true]);
    }

    use RefreshDatabase, StrutturaFixtures;

    private function fixture(): Struttura
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));

        return $s;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('booleans')]
    public function test_stati_credenziali_fuori_input_group_e_segreti_assenti(bool $configured): void
    {
        $s = $this->fixture();
        if ($configured) {
            $s->update(['questura_password' => 'SEGRETO-UX-SINTETICO', 'questura_wskey' => 'WSKEY-UX-SINTETICO']);
        }
        $response = $this->get('/struttura')->assertOk();
        $html = $response->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $input = $xpath->query('//input[@name="questura_password"]')->item(0);
        $group = $input->parentNode;
        $this->assertStringContainsString('input-group', $group->getAttribute('class'));
        $this->assertSame(0, $xpath->query('.//small', $group)->length);
        $this->assertSame(1, $xpath->query('.//button[@data-password-toggle="questura_password"]', $group)->length);
        $status = $xpath->query('./small', $group->parentNode)->item(0);
        $this->assertNotNull($status);
        $this->assertSame($configured ? 'Configurata' : 'Non configurata', trim($status->textContent));
        $wskey = $xpath->query('//input[@name="questura_wskey"]')->item(0);
        $this->assertSame(trim($status->textContent), trim($xpath->query('./small', $wskey->parentNode)->item(0)->textContent));
        $this->assertSame('', $input->getAttribute('value'));
        $this->assertSame('', $wskey->getAttribute('value'));
        $this->assertStringNotContainsString('SEGRETO-UX-SINTETICO', $html);
        $this->assertStringNotContainsString('WSKEY-UX-SINTETICO', $html);
    }

    public static function booleans(): array
    {
        return [[false], [true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCredentials')]
    public function test_credenziale_non_decifrabile_renderizza_errore_nel_layout_e_storico(bool $debug, string $field): void
    {
        $s = $this->fixture();
        config(['app.debug' => $debug, 'logging.default' => 'single', 'logging.channels.single.path' => storage_path('logs/ux-fixture.log')]);
        $this->app->make('log')->forgetChannel('single');
        $s->update(['questura_username' => 'fixture-ux', 'questura_password' => 'PASSWORD-UX-SINTETICO', 'questura_wskey' => 'WSKEY-UX-SINTETICO']);
        DB::table('struttura')->where('id', $s->id)->update([$field => 'CIPHERTEXT-NON-VALIDO-UX']);
        $txt = 'TXT-SINTETICO-STORICO-UX';
        $path = 'questura/struttura_'.$s->id.'/ux.txt';
        Storage::disk('local')->put($path, $txt);
        $export = QuesturaExport::create(['struttura_id' => $s->id, 'dal' => today(), 'al' => today(), 'filename' => 'ux.txt', 'path' => $path, 'sha256' => hash('sha256', $txt), 'status' => 'generated']);
        $response = $this->get('/questura')->assertStatus(409)->assertViewIs('questura.index');
        $response->assertSee('Errore nel ciclo Questura')->assertSee('Verificare lo storico prima di ripetere l’operazione.');
        $response->assertSee('id="layout-wrapper"', false)->assertSee('id="questura-tab-storico-export"', false);
        $response->assertSee(route('questura.download.storico', ['id' => $export->id]), false);
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        foreach (['questura.ws.verify', 'questura.ws.send'] as $route) {
            $this->assertSame(1, $xpath->query('//form[@action="'.route($route).'"]//button[@disabled]')->length);
        }
        foreach ([$txt, base64_encode($txt), 'CIPHERTEXT-NON-VALIDO-UX', 'WSKEY-UX-SINTETICO', 'PASSWORD-UX-SINTETICO', 'DecryptException', 'Stack trace', 'SQLSTATE'] as $marker) {
            $response->assertDontSee($marker, false);
        }
        $this->get('/questura/download/storico/'.$export->id)->assertOk()->assertStreamedContent($txt);
        $this->assertSame(0, QuesturaTransmission::count());
        $this->assertSame(0, DB::table('questura_send_reservations')->count());
        $this->assertSame('CIPHERTEXT-NON-VALIDO-UX', DB::table('struttura')->where('id', $s->id)->value($field));
        $log = file_get_contents(storage_path('logs/ux-fixture.log'));
        $this->assertStringContainsString('QUESTURA_WORKFLOW_ERROR', $log);
        foreach ([$txt, base64_encode($txt), 'CIPHERTEXT-NON-VALIDO-UX', 'PASSWORD-UX-SINTETICO', 'WSKEY-UX-SINTETICO'] as $marker) {
            $this->assertStringNotContainsString($marker, $log);
        }
    }

    public static function invalidCredentials(): array
    {
        return [[false, 'questura_password'], [true, 'questura_password'], [false, 'questura_wskey'], [true, 'questura_wskey']];
    }

    public function test_causa_sintetica_della_schermata_testuale(): void
    {
        $s = $this->fixture();
        DB::table('struttura')->where('id', $s->id)->update(['questura_password' => 'CIPHERTEXT-NON-VALIDO-UX']);
        $this->expectException(\Illuminate\Contracts\Encryption\DecryptException::class);
        app(QuesturaWebService::class)->credentialsStatus($s->fresh());
    }

    public function test_errore_credenziali_non_aggira_auth_tenant_o_csrf(): void
    {
        $s = $this->fixture();
        DB::table('struttura')->where('id', $s->id)->update(['questura_password' => 'CIPHERTEXT-NON-VALIDO-UX']);
        $b = $this->structureFor(null);
        $this->get('/questura?struttura_id='.$b->id)->assertForbidden();
        $this->post('/questura/ws/send', ['_token' => 'errato'])->assertStatus(419);
        $this->app['auth']->forgetGuards();
        $this->get('/questura')->assertRedirect(route('login'));
        $this->assertSame(0, QuesturaTransmission::count());
    }

    public function test_errore_tecnico_inatteso_restando_500_e_minimizzato(): void
    {
        $this->fixture();
        config(['app.debug' => true]);
        $this->app->instance(QuesturaWebService::class, new class extends QuesturaWebService
        {
            public function credentialsStatus(Struttura $s): array
            {
                throw new \RuntimeException('SQLSTATE SEGRETO-UX-SINTETICO SNAPSHOT-UX-SINTETICO');
            }
        });
        $response = $this->get('/questura')->assertStatus(500);
        $response->assertSee('Errore nel ciclo Questura.');
        foreach (['SQLSTATE', 'SEGRETO-UX-SINTETICO', 'SNAPSHOT-UX-SINTETICO', 'RuntimeException'] as $marker) {
            $response->assertDontSee($marker, false);
        }
    }
}
