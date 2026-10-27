<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaTransmission, Schedina, Struttura};
use App\Services\{QuesturaProfilo, QuesturaTxtExportService, QuesturaWebService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\{QuesturaPreviewRequests, StrutturaFixtures};
use Tests\TestCase;

class AppartamentoSoapDouble extends \SoapClient
{
    public array $calls = [];
    public string $scenario = 'ok';
    public function __construct() {}
    public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
    {
        $this->calls[] = [$name, $args[0]];
        if ($name === 'GenerateToken') {
            return (object) ['GenerateTokenResult' => (object) ['token' => 'TOKEN-SINTETICO', 'expires' => date(DATE_ATOM, time() + 3600)], 'result' => (object) ['esito' => true]];
        }
        if (str_ends_with($name, '_Send') && $this->scenario === 'timeout') { throw new \RuntimeException('timeout sintetico'); }
        if ($name === 'Authentication_Test') { return (object) [$name.'Result' => (object) ['esito' => true]]; }
        if ($this->scenario === 'rejected') { return (object) [$name.'Result' => (object) ['esito' => false]]; }
        if ($this->scenario === 'malformed') { return (object) ['HTTP' => 200]; }
        $rows = count($args[0]['ElencoSchedine']['string']);
        $details = [];
        for ($i = 0; $i < $rows; $i++) { $details[] = (object) ['esito' => $this->scenario !== 'partial' || $i === 0, 'ErroreCod' => '12']; }

        return (object) [$name.'Result' => (object) ['esito' => true], 'result' => (object) ['SchedineValide' => $this->scenario === 'partial' ? 1 : $rows, 'Dettaglio' => (object) ['EsitoOperazioneServizio' => $details]]];
    }
}

class QuesturaAppartamentoTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures, QuesturaPreviewRequests;

    private function service(\SoapClient $double): QuesturaWebService
    {
        return new class($double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        };
    }

    private function source(): array
    {
        config(['questura.enabled' => true]);
        $s = $this->structureFor(null);
        $s->update(['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => 0, 'questura_username' => 'UTENTE-SINTETICO', 'questura_password' => 'PASSWORD-SINTETICA', 'questura_wskey' => 'WSKEY-SINTETICA']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        GeoNazione::firstOrCreate(['codice_iso2' => 'FR'], ['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'is_italia' => false]);
        $nation = GeoNazione::where('codice_iso2', 'FR')->value('id');
        $stay = Schedina::create(['struttura_id' => $s->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16', 'cant_people' => 1,
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(), 'surname' => 'Esempio', 'name' => 'Élodie', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => $nation, 'oa_city_nac' => 'Francia', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => $nation]);
        $double = new AppartamentoSoapDouble;
        $this->app->instance(QuesturaWebService::class, $this->service($double));

        return [$s, $stay, $double, ['dal' => today()->toDateString(), 'al' => today()->toDateString()]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('circuiti')]
    public function test_metodi_individuali_parametro_intero_zero_e_righe_standard(string $capo, ?string $componente): void
    {
        [$s, $stay, $double, $period] = $this->source();
        if ($componente !== null) {
            $stay->update(['relationship' => $capo, 'cant_people' => 2]);
            \App\Models\Componenti::create(['struttura_id' => $s->id, 'schedina_id' => $stay->id, 'relationship' => $componente, 'surname' => 'Esempio', 'name' => 'Componente', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => $stay->oa_country, 'city_nac' => 'Francia']);
        }
        $txt = app(QuesturaTxtExportService::class)->buildTxtPerSchedina($stay->fresh());
        foreach (explode("\r\n", $txt) as $row) { $this->assertSame(168, mb_strlen($row, 'UTF-8')); }
        $this->questuraPost('/questura/ws/verify', $period)->assertSessionHasNoErrors()->assertRedirect();
        $this->questuraPost('/questura/ws/send', $period)->assertSessionHasNoErrors()->assertRedirect();
        $calls = array_values(array_filter($double->calls, fn ($c) => str_starts_with($c[0], 'GestioneAppartamenti_')));
        $this->assertSame(['GestioneAppartamenti_Test', 'GestioneAppartamenti_Send'], array_column($calls, 0));
        foreach ($calls as $call) {
            $this->assertSame(0, $call[1]['IdAppartamento']);
            $this->assertSame(['string' => explode("\r\n", $txt)], $call[1]['ElencoSchedine']);
        }
        $send = QuesturaTransmission::where('mode', 'send')->sole();
        $this->assertSame('sent', $send->status);
        $this->assertSame(0, $send->payload['questura_context']['id_appartamento']);
        $this->assertSame(0, $send->questura_context['id_appartamento']);
        $this->assertFalse($send->result['accepted']);
        $this->assertSame(0, (int) $stay->fresh()->questura_send_count);
        $this->questuraPost('/questura/ws/send', $period)->assertRedirect();
        $this->assertCount(1, array_filter($double->calls, fn ($c) => $c[0] === 'GestioneAppartamenti_Send'));
        $this->assertSame(1, DB::table('questura_send_reservations')->count());
    }

    public static function circuiti(): array
    {
        return [['16', null], ['17', '19'], ['18', '20']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('contestiPersistiti')]
    public function test_contesto_json_riordinato_confronta_rigorosamente_i_valori(string $variante, bool $ammesso): void
    {
        [$s, , $double, $period] = $this->source();
        $this->questuraPost('/questura/ws/verify', $period)->assertSessionHasNoErrors();
        $verify = QuesturaTransmission::where('mode', 'verify')->sole();
        $original = $verify->questura_context;
        $context = ['profilo' => $original['profilo'], 'utenza_sha256' => $original['utenza_sha256'], 'id_appartamento' => $original['id_appartamento']];
        if ($variante === 'tipo_id') { $context['id_appartamento'] = '0'; }
        if ($variante === 'chiave_assente') { unset($context['utenza_sha256']); }
        if ($variante === 'chiave_estranea') { $context['estranea'] = true; }
        // Simula la riserializzazione JSON del DB senza cambiare il destinatario.
        DB::table('questura_transmissions')->where('id', $verify->id)->update(['questura_context' => json_encode($context)]);
        $response = $this->questuraPost('/questura/ws/send', $period);
        if ($ammesso) { $response->assertSessionHasNoErrors(); }
        else { $response->assertSessionHasErrors('questura_ws'); }
        $this->assertCount($ammesso ? 1 : 0, array_filter($double->calls, fn ($c) => $c[0] === 'GestioneAppartamenti_Send'));
    }

    public static function contestiPersistiti(): array
    {
        return [['riordinato', true], ['tipo_id', false], ['chiave_assente', false], ['chiave_estranea', false]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cambiConfigurazione')]
    public function test_cambio_id_profilo_o_utenza_impone_un_nuovo_test(array $change): void
    {
            [$s, , $double, $period] = $this->source();
            $this->questuraPost('/questura/ws/verify', $period)->assertSessionHasNoErrors();
            $s->update($change);
            $this->questuraPost('/questura/ws/send', $period)->assertSessionHasErrors('questura_ws');
            $this->assertCount(0, array_filter($double->calls, fn ($c) => str_ends_with($c[0], 'Send')));
            $this->assertSame(0, QuesturaTransmission::where('struttura_id', $s->id)->where('mode', 'send')->count());
    }

    public static function cambiConfigurazione(): array
    {
        return [[['questura_appartamento_id' => 4]], [['questura_profilo' => 'standard']], [['questura_username' => 'ALTRO-UTENTE']]];
    }

    public function test_id_assente_invalido_non_attiva_il_client_e_standard_non_lo_richiede(): void
    {
        config(['questura.enabled' => true]);
        foreach ([null, '', '-1', '1.5', '1e2', '2147483648', 'x'] as $id) {
            $double = new AppartamentoSoapDouble;
            $s = new Struttura(['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => $id]);
            $this->assertSame('technical_error', $this->service($double)->send($s, 'RIGA')['state']);
            $this->assertSame([], $double->calls);
        }
        foreach (['0', '000000', '000004', '2147483647'] as $id) {
            $s = new Struttura(['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => $id]);
            $this->assertSame((int) $id, app(QuesturaProfilo::class)->context($s)['id_appartamento']);
        }
        $double = new AppartamentoSoapDouble;
        $this->service($double)->send(new Struttura(['questura_appartamento_id' => 4]), str_repeat('X', 168));
        $this->assertSame('Send', $double->calls[2][0]);
        $this->assertArrayNotHasKey('IdAppartamento', $double->calls[2][1]);
    }

    public function test_rifiuti_parziali_timeout_non_diventano_accettazione(): void
    {
        config(['questura.enabled' => true]);
        foreach (['rejected' => 'rejected', 'partial' => 'partial', 'timeout' => 'uncertain', 'malformed' => 'uncertain'] as $scenario => $state) {
            $double = new AppartamentoSoapDouble;
            $double->scenario = $scenario;
            $result = $this->service($double)->send(new Struttura(['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => 4]), str_repeat('X', 168)."\r\n".str_repeat('Y', 168));
            $this->assertSame($state, $result['state']);
            $this->assertFalse($result['accepted']);
            $this->assertCount(3, $double->calls);
        }
    }

    public function test_serializzazione_soap_da_wsdl_ufficiale_in_memoria_senza_rete(): void
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        config(['questura.enabled' => true]);
        foreach (['000000', '000004'] as $id) {
            $client = new QuesturaOfflineSoapClient;
            $ws = $this->service($client);
            $s = new Struttura(['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => $id]);
            $this->assertSame('unknown', $ws->verify($s, str_repeat('X', 168))['state']);
            $this->assertSame('sent', $ws->send($s, str_repeat('X', 168))['state']);
            foreach ([2 => 'GestioneAppartamenti_Test', 5 => 'GestioneAppartamenti_Send'] as $index => $method) {
                $doc = new \DOMDocument;
                $this->assertTrue($doc->loadXML($client->requests[$index]));
                $xpath = new \DOMXPath($doc);
                $xpath->registerNamespace('a', 'AlloggiatiService');
                $this->assertSame(1, $xpath->query('//a:'.$method)->length);
                $this->assertSame((string) (int) $id, $xpath->evaluate('string(//a:IdAppartamento)'));
                $this->assertSame(str_repeat('X', 168), $xpath->evaluate('string(//a:ElencoSchedine/a:string)'));
            }
        }
    }

    public function test_configurazione_salvata_con_zeri_permessi_e_isolamento(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($admin));
        $before = $foreign->getRawOriginal();
        $user = $this->actor('struttura_user', $owner->id, $s->id);
        $user->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($user)->put('/struttura?sid='.$s->id, ['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => '000004'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(4, (int) $s->fresh()->questura_appartamento_id);
        $this->get('/struttura?sid='.$s->id)->assertOk()->assertSee('ID appartamento Alloggiati Web');
        $this->put('/struttura?sid='.$s->id, ['questura_appartamento_id' => '0'])->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $s->fresh()->questura_appartamento_id);
        $this->put('/struttura?sid='.$s->id, ['questura_appartamento_id' => null])->assertSessionHasErrors('questura_appartamento_id');
        foreach (['-1', '1e2', '1.5', '2147483648', 'abc'] as $invalid) {
            $this->put('/struttura?sid='.$s->id, ['questura_appartamento_id' => $invalid])->assertSessionHasErrors('questura_appartamento_id');
            $this->assertSame(0, (int) $s->fresh()->questura_appartamento_id);
        }
        $this->put('/struttura?sid='.$foreign->id, ['questura_profilo' => 'appartamenti', 'questura_appartamento_id' => '4'])->assertForbidden();
        $this->assertSame($before, $foreign->fresh()->getRawOriginal());
        $user->update(['ruolo_operativo' => 'reception']);
        $this->actingAs($user->fresh())->put('/struttura?sid='.$s->id, ['questura_profilo' => 'standard'])->assertForbidden();
    }
}
