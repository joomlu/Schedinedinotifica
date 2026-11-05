<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaPeriodoComunaleTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    private function hotel(): Struttura
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'data_apertura' => '2026-01-01', 'data_chiusura' => '2026-12-31']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));

        return $s;
    }

    public function test_apertura_e_date_salvate_non_condizionano_periodo_o_calcolo(): void
    {
        $s = $this->hotel();
        $c = TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-01-01', 'fine' => '2026-12-31', 'max_age_children' => 17, 'min_age_adult' => 18, 'note' => 'Da conservare']);
        $originale = $c->fresh()->getAttributes();
        foreach ([['Annuale', '2026-01-01', '2026-12-31'], ['Stagionale', '2026-03-01', '2026-11-15']] as [$tipo, $dal, $al]) {
            $s->update(['tipo_apertura' => $tipo, 'data_apertura' => $dal, 'data_chiusura' => $al]);
            $strutturaPrima = $s->fresh()->getAttributes();
            $vista = $this->get('/tassa_di_soggiorno?anno_fiscale=2026')->assertOk()
                ->assertSee('Profilo comunale di Bellaria-Igea Marina — anno 2026')
                ->assertSee('Periodo di applicazione dell’imposta')->assertSee('Dal 01/06/2026 al 30/09/2026')
                ->assertDontSee('derivato dal profilo')->assertDontSee('consenso-riallineamento')
                ->assertViewHas('riallineamentoToken', null)
                ->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'automatica' && $d['configurazione']->inizio->toDateString() === $d['profilo']['inizio']);
            $this->assertSame('2026-01-01', $vista->viewData('confronto')['attuali']['inizio']);
            foreach ([['2026-05-30', '2026-06-01', 0], ['2026-06-01', '2026-06-02', 1], ['2026-05-31', '2026-06-03', 2], ['2026-09-30', '2026-10-01', 1], ['2026-09-29', '2026-10-02', 2], ['2026-10-01', '2026-10-03', 0]] as [$arrivo, $partenza, $notti]) {
                $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Sintetico', 'surname' => 'Periodo', 'arrive' => $arrivo, 'departure' => $partenza, 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
                $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['righe'][0]['notti_tassate'] === $notti && $d['totale'] == 1.5 * $notti);
            }
            $this->assertSame($originale, $c->fresh()->getAttributes());
            $this->assertSame($strutturaPrima, $s->fresh()->getAttributes());
        }
        // Anche sole date salvate o date incomplete non diventano una regola fiscale.
        $c->update(['tassa_soggiorno' => null, 'giorni_massimo' => null, 'max_age_children' => null, 'min_age_adult' => null, 'fine' => null]);
        $prima = $c->fresh()->getAttributes();
        $this->get('/tassa_di_soggiorno?anno_fiscale=2026')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'automatica');
        $this->assertSame($prima, $c->fresh()->getAttributes());
        $this->assertSame(0, TassaExport::count());
    }

    public function test_anno_mancante_blocca_e_profilo_successivo_usa_date_proprie(): void
    {
        $s = $this->hotel();
        $this->get('/tassa_di_soggiorno?anno_fiscale=2027')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'regola_non_disponibile' && $d['configurazione'] === null)->assertViewHas('riallineamentoToken', null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Sintetico', 'surname' => 'Anno', 'arrive' => '2026-12-31', 'departure' => '2027-01-02', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->getJson('/schedine/'.$p->id.'/tassa/anteprima')->assertUnprocessable()->assertJsonValidationErrors('regola_non_disponibile');
    }

    public function test_profilo_successivo_usa_date_proprie_in_vista_e_calcolo(): void
    {
        $s = $this->hotel();
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Sintetico', 'surname' => 'Anno', 'arrive' => '2027-04-09', 'departure' => '2027-04-11', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->app->instance(TassaDiSoggiornoService::class, new class extends TassaDiSoggiornoService {
            protected function profiliFiscaliBellaria(): array
            {
                $a = parent::profiliFiscaliBellaria()[0];
                $b = $a;
                $b['valida_dal'] = '2027-01-01';
                $b['valida_al'] = '2027-12-31';
                $b['inizio'] = '2027-04-10';
                $b['fine'] = '2027-04-20';
                $b['regola_versione'] = 'SINTETICO-2027-PERIODO';
                $b['regola_fonte'] = 'Fixture, nessuna fonte normativa';

                return [$a, $b];
            }
        });
        $this->get('/tassa_di_soggiorno?anno_fiscale=2027')->assertOk()->assertSee('Dal 10/04/2027 al 20/04/2027')->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'automatica');
        foreach ([['2027-04-09', '2027-04-11', 1], ['2027-04-20', '2027-04-22', 1], ['2027-06-01', '2027-06-03', 0]] as [$arrivo, $partenza, $notti]) {
            $p->update(['arrive' => $arrivo, 'departure' => $partenza]);
            $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 1.5 * $notti);
        }
        $this->assertSame(0, TassaDiSoggiorno::count());
        $this->assertSame(0, TassaExport::count());
    }
}
