<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaVersionamentoTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function hotel()
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));

        return $s;
    }

    private function ospite($s, string $arrivo, string $partenza)
    {
        return Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Versionamento sintetico', 'arrive' => $arrivo, 'departure' => $partenza, 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
    }

    private function profiliSintetici(float $tariffa = 3, int $cap = 6): void
    {
        // Il profilo 2027 è esclusivamente una fixture architetturale, non normativa.
        $service = new class($tariffa, $cap) extends TassaDiSoggiornoService
        {
            public function __construct(private float $tariffa, private int $cap) {}

            protected function profiliFiscaliBellaria(): array
            {
                $profili = parent::profiliFiscaliBellaria();
                $nuovo = $profili[0];
                $nuovo['valida_dal'] = '2027-01-01';
                $nuovo['valida_al'] = '2027-12-31';
                $nuovo['inizio'] = '2027-06-01';
                $nuovo['fine'] = '2027-09-30';
                $nuovo['regola_versione'] = 'SINTETICO-2027';
                $nuovo['regola_fonte'] = 'Fixture: nessuna certificazione normativa';
                $nuovo['tariffe'][3] = (string) $this->tariffa;
                $nuovo['giorni_massimo'] = $this->cap;

                return [$profili[0], $nuovo];
            }
        };
        $this->app->instance(TassaDiSoggiornoService::class, $service);
    }

    public function test_movimento_2027_non_eredita_profilo_2026_da_filtro(): void
    {
        $s = $this->hotel();
        $p = $this->ospite($s, '2027-06-10', '2027-06-13');
        $q = '?data_da=2026-12-01&data_a=2027-06-30';
        $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable()->assertJsonValidationErrors('regola_non_disponibile');
        foreach (['rapporto', 'rapporto/csv', 'rapporto/controllo', 'rapporto/controllo/csv', 'rapporto/controllo/stampa'] as $route) {
            $this->getJson('/tassa_di_soggiorno/'.$route.$q)->assertUnprocessable()->assertJsonValidationErrors('regola_non_disponibile');
        }
        $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-12-01', 'data_a' => '2027-06-30'])->assertUnprocessable();
        $this->assertSame(0, TassaExport::count());
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null);
    }

    public function test_intervallo_cross_year_vuoto_o_con_soli_movimenti_coperti(): void
    {
        $s = $this->hotel();
        $q = '?data_da=2026-12-01&data_a=2027-06-30';
        $this->get('/tassa_di_soggiorno/rapporto'.$q)->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->get('/tassa_di_soggiorno/rapporto/csv'.$q)->assertOk();
        $p = $this->ospite($s, '2026-12-10', '2026-12-13');
        $this->get('/tassa_di_soggiorno/rapporto'.$q)->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
        $this->get('/tassa_di_soggiorno/rapporto/controllo'.$q)->assertOk();
        $this->ospite($s, '2026-06-10', '2026-06-13');
        $qCoperto = '?data_da=2026-06-01&data_a=2027-06-01';
        $this->get('/tassa_di_soggiorno/rapporto'.$qCoperto)->assertOk()->assertViewHas('totalePeriodo', 4.5);
        $this->get('/tassa_di_soggiorno/rapporto/csv'.$qCoperto)->assertOk();
        $this->get('/tassa_di_soggiorno/rapporto/controllo'.$qCoperto)->assertOk()->assertViewHas('summary', fn ($d) => $d['totale_tassa'] === 4.5);
    }

    public function test_report_multiversione_rettifica_e_storico_usano_profilo_per_movimento(): void
    {
        $this->profiliSintetici();
        $s = $this->hotel();
        $p26 = $this->ospite($s, '2026-06-10', '2026-06-13');
        $p27 = $this->ospite($s, '2027-05-31', '2027-06-03');
        $data = ['data_da' => '2026-06-01', 'data_a' => '2027-06-01'];
        $q = '?'.http_build_query($data);
        $this->get('/schedine/'.$p26->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 4.5);
        $this->get('/schedine/'.$p27->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 6);
        $this->get('/tassa_di_soggiorno/rapporto'.$q)->assertOk()->assertViewHas('totalePeriodo', 10.5);
        $this->get('/tassa_di_soggiorno/rapporto/controllo'.$q)->assertOk()->assertViewHas('summary', fn ($d) => $d['totale_tassa'] === 10.5);
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$q)->assertOk()->getContent();
        $this->post('/tassa_di_soggiorno/rapporto/consolida', $data)->assertRedirect();
        $e = TassaExport::firstOrFail();
        $this->assertSame('bellaria-alberghi-2026-v1', $e->snapshot['calcoli'][$p26->id]['configurazione']['regola_versione']);
        $this->assertSame('SINTETICO-2027', $e->snapshot['calcoli'][$p27->id]['configurazione']['regola_versione']);
        $this->assertSame($csv, $e->snapshot['csv']);
        $snapshot = $e->snapshot;
        $this->profiliSintetici(4);
        $this->post('/tassa_di_soggiorno/rapporto/consolida', $data)->assertRedirect();
        $secondo = TassaExport::orderByDesc('id')->firstOrFail();
        $this->assertSame($e->id, $secondo->precedente_id);
        $this->assertSame(2, $secondo->versione);
        $this->assertSame($snapshot, $e->fresh()->snapshot);
        $this->get('/tassa_di_soggiorno/rapporto?export_id='.$e->id)->assertOk()->assertViewHas('totalePeriodo', 10.5);
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$e->id)->assertOk()->getContent());
        $this->get('/schedine/'.$p27->id.'/tassa/print?export_id='.$e->id)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 6);
    }

    public function test_cap_diverso_e_esclusivamente_sintetico_nel_dominio(): void
    {
        $this->profiliSintetici(3, 4);
        $s = $this->hotel();
        $p = $this->ospite($s, '2027-06-10', '2027-06-20');
        $service = app(TassaDiSoggiornoService::class);
        $cfg = $service->configurazioneAutomatica($s, null, $service->parseDate($p->arrive));
        $d = $service->dettaglioSchedina($p, collect(), $cfg, collect(), $s);
        $this->assertEquals(12, $d['totale']);
        $this->assertSame(6, $d['righe'][0]['notti_oltre_max']);
    }

    public function test_failure_upload_preserva_precedente_e_rimuove_solo_file_nuovo(): void
    {
        Storage::fake('local');
        $s = $this->hotel();
        foreach ([false, true] as $conPrecedente) {
            if ($conPrecedente) {
                TassaDiSoggiorno::withoutEvents(function () use ($s) {
                    $path = 'tassa-ricevute/'.$s->id.'/precedente.png';
                    Storage::disk('local')->put($path, 'Immagine sintetica precedente');
                    TassaDiSoggiorno::create(['struttura_id' => $s->id, 'ricevuta_immagine' => $path]);
                });
            }
            $files = Storage::disk('local')->allFiles();
            $prima = TassaDiSoggiorno::first()?->getAttributes();
            $dispatcher = TassaDiSoggiorno::getEventDispatcher();
            $temporaneo = clone $dispatcher;
            TassaDiSoggiorno::setEventDispatcher($temporaneo);
            TassaDiSoggiorno::saved(fn () => throw new \RuntimeException('Failure sintetico dopo scrittura DB'));
            try {
                $this->putJson('/tassa_di_soggiorno', ['ricevuta_foto' => UploadedFile::fake()->image('nuova.png')])->assertStatus(500);
            } finally {
                TassaDiSoggiorno::setEventDispatcher($dispatcher);
            }
            $this->assertSame($files, Storage::disk('local')->allFiles());
            $this->assertSame($prima, TassaDiSoggiorno::first()?->getAttributes());
        }
    }
}
