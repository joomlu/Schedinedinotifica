<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, QuesturaTransmission, Schedina, Struttura};
use App\Services\{QuesturaTxtExportService, QuesturaWebService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaTestSendSnapshotTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    #[\PHPUnit\Framework\Attributes\DataProvider('scenarios')]
    public function test_send_usa_solo_lo_snapshot_testato_o_blocca_se_la_sorgente_cambia(bool $changed): void
    {
        require_once __DIR__.'/QuesturaWsContractTest.php';
        $structure = $this->structureFor(null);
        $structure->update(['questura_username' => 'fixture', 'questura_password' => 'fixture-password', 'questura_wskey' => 'fixture-key']);
        $this->actingAs($this->actor('struttura_user', null, $structure->id));
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $source = Schedina::create([
            'struttura_id' => $structure->id, 'circuito' => 'schedina', 'scheda' => 'SINTETICA', 'relationship' => '16',
            'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(),
            'surname' => 'Esempio', 'name' => 'Prima verifica', 'sex' => 'F', 'oa_date_nac' => '1980-02-29',
            'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => '777',
        ]);
        $double = new QuesturaSoapDouble();
        $this->app->instance(QuesturaWebService::class, new class($double) extends QuesturaWebService {
            public function __construct(private \SoapClient $double) {}
            protected function isSimulation(Struttura $s): bool { return false; }
            protected function makeClient(): \SoapClient { return $this->double; }
        });
        $period = ['dal' => today()->toDateString(), 'al' => today()->toDateString()];
        $original = (new QuesturaTxtExportService())->buildTxtPerSchedina($source);
        $this->post('/questura/ws/verify', $period)->assertRedirect();
        $verify = QuesturaTransmission::where('mode', 'verify')->firstOrFail();
        $tests = array_values(array_filter($double->calls, fn ($call) => $call[0] === 'Test'));
        $this->assertCount(1, $tests);
        $this->assertSame(['string' => [$original]], $tests[0][1]['ElencoSchedine']);
        $this->assertSame('unknown', $verify->status);
        $this->assertFalse($verify->result['accepted']);
        $this->assertSame(hash('sha256', $original), $verify->sha256);
        $this->assertSame(0, DB::table('questura_send_reservations')->count());

        if ($changed) { $source->update(['name' => 'Dopo verifica']); }
        $this->post('/questura/ws/send', $period)->assertRedirect();
        $sends = array_values(array_filter($double->calls, fn ($call) => $call[0] === 'Send'));
        $this->assertLessThanOrEqual(1, count($sends));
        $this->assertTrue(Schedina::whereKey($source->id)->exists());
        $this->assertSame($verify->sha256, $verify->fresh()->sha256);

        // Entrambe le soluzioni sono ammesse: bloccare dopo una modifica
        // oppure inviare lo snapshot già testato, senza cambiare i suoi byte.
        if ($sends === []) {
            $this->assertTrue($changed, 'Il controllo senza modifiche deve percorrere il ciclo locale Test/Send.');
            $this->assertSame(0, QuesturaTransmission::where('mode', 'send')->count());
            return;
        }
        $send = QuesturaTransmission::where('mode', 'send')->firstOrFail();
        $sentBytes = implode("\r\n", $sends[0][1]['ElencoSchedine']['string']);
        $this->assertSame('sent', $send->status);
        $this->assertFalse($send->result['accepted']);
        $this->assertSame(hash('sha256', $sentBytes), $send->sha256);
        $this->assertSame($sentBytes, base64_decode($send->payload['txt_base64'], true));
        $this->assertSame(1, DB::table('questura_send_reservations')->where('schedina_id', $source->id)->count());
        // Solo digest sintetici nel diff del fallimento, nessun payload personale.
        $this->assertSame($verify->sha256, $send->sha256,
            'Il primo Send ha trasmesso byte diversi da quelli del precedente Test dello stesso elenco/tenant. Occorre bloccare o usare lo snapshot testato.');
    }

    public static function scenarios(): array
    {
        return ['sorgente invariata' => [false], 'modifica dopo Test' => [true]];
    }
}
