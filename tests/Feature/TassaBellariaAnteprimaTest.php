<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaAnteprimaTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function hotel(): Struttura
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));

        return $s;
    }

    private function soggiorno(Struttura $s, string $arrivo = '2026-06-10', string $partenza = '2026-06-13', string $nascita = '1980-01-01'): Schedina
    {
        return Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Anteprima sintetica', 'surname' => 'Fixture', 'arrive' => $arrivo, 'departure' => $partenza, 'oa_date_nac' => $nascita, 'exent' => 'NO', 'is_arrive' => 0]);
    }

    public function test_anteprima_non_dipende_dall_anno_della_pagina_configurazione(): void
    {
        $s = $this->hotel();
        $p = $this->soggiorno($s);
        $this->get('/tassa_di_soggiorno?anno_fiscale=2027')->assertOk()->assertSee('Anteprima ricevuta')->assertSee('/schedine/'.$p->id.'/tassa/anteprima');
    }

    public function test_zero_fuori_periodo_e_soggiorni_parziali_hanno_documenti_coerenti(): void
    {
        $s = $this->hotel();
        foreach ([['2026-05-10', '2026-05-13', 0, 0], ['2026-10-01', '2026-10-04', 0, 0], ['2026-05-31', '2026-06-03', 3, 2], ['2026-09-29', '2026-10-02', 3, 2]] as [$dal, $al, $totale, $notti]) {
            $p = $this->soggiorno($s, $dal, $al);
            $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == $totale && $d['righe'][0]['notti_tassate'] === $notti)->assertSee('Anteprima');
            $ricevuta = $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == $totale);
            if ($totale === 0) {
                $ricevuta->assertSee('Soggiorno fuori dal periodo di applicazione dell’imposta di soggiorno');
            }
            $q = '?data_da='.$dal.'&data_a='.$dal;
            $this->get('/tassa_di_soggiorno/rapporto'.$q)->assertOk()->assertViewHas('totalePeriodo', $totale);
            $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$q)->assertOk()->getContent();
            $righe = array_filter(explode("\n", $csv));
            $sum = 0;
            foreach (array_slice($righe, 1) as $riga) {
                $r = str_getcsv($riga, ';', '"', '');
                $sum += (float) $r[6] * (float) $r[7];
            }
            $this->assertEquals($totale, $sum);
        }
        $this->assertSame(0, TassaDiSoggiorno::count());
        $this->assertSame(0, TassaExport::count());
    }

    public function test_zero_esenzione_e_limite_raggiunto_non_sono_fuori_periodo(): void
    {
        $s = $this->hotel();
        foreach ([['2026-06-10', '2026-06-13', '2015-01-01', 0], ['2026-06-10', '2026-06-20', '2008-06-16', 4]] as [$dal, $al, $nascita, $oltre]) {
            $p = $this->soggiorno($s, $dal, $al, $nascita);
            $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0 && $d['righe'][0]['notti_oltre_max'] === $oltre)->assertSee('Minori')->assertDontSee('Soggiorno fuori dal periodo');
        }
    }

    public function test_documento_valido_non_scrive_e_errori_precisi_non_producono_ricevute(): void
    {
        $s = $this->hotel();
        $p = $this->soggiorno($s);
        $prima = $p->fresh()->getAttributes();
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertDontSee('Configura prima la tassa')->assertSee('Anteprima ricevuta');
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertSee('Anteprima del calcolo')->assertDontSee('Data emissione');
        $this->assertSame($prima, $p->fresh()->getAttributes());
        $this->assertSame(0, TassaExport::count());
        $c = TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-03-01', 'fine' => '2026-10-01']);
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertStatus(422)->assertSee('Configurazione fiscale')->assertDontSee('id="ricevuta-tassa-card"', false);
        $this->getJson('/schedine/'.$p->id.'/tassa/anteprima')->assertUnprocessable();
        $c->update(['inizio' => '2026-06-01', 'fine' => '2026-09-30']);
        $p->update(['arrive' => '2026-12-31', 'departure' => '2027-01-03']);
        foreach (['anteprima', 'print'] as $route) {
            $this->getJson('/schedine/'.$p->id.'/tassa/'.$route)->assertUnprocessable()->assertJsonValidationErrors('regola_non_disponibile');
        }
        $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-12-01', 'data_a' => '2027-01-03'])->assertUnprocessable();
        $this->assertSame(0, TassaExport::count());
    }

    public function test_profili_sintetici_cross_year_contatore_unico_tariffe_e_snapshot(): void
    {
        $service = new class extends TassaDiSoggiornoService
        {
            protected function profiliFiscaliBellaria(): array
            {
                $a = parent::profiliFiscaliBellaria()[0];
                $a['regola_versione'] = 'SINTETICO-2026-INVERNALE';
                $a['regola_fonte'] = 'Fixture, nessuna fonte normativa';
                $a['inizio'] = '2026-12-01';
                $a['fine'] = '2026-12-31';
                $b = $a;
                $b['valida_dal'] = '2027-01-01';
                $b['valida_al'] = '2027-12-31';
                $b['inizio'] = '2027-01-01';
                $b['fine'] = '2027-01-31';
                $b['regola_versione'] = 'SINTETICO-2027-INVERNALE';
                $b['regola_fonte'] = 'Fixture, nessuna fonte normativa';
                $b['tariffe'][3] = '3.00';

                return [$a, $b];
            }
        };
        $this->app->instance(TassaDiSoggiornoService::class, $service);
        $s = $this->hotel();
        $p = $this->soggiorno($s, '2026-12-28', '2027-01-07');
        $q = '?data_da=2026-12-01&data_a=2027-01-07';
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 12 && $d['righe'][0]['notti_tassate'] === 6 && $d['righe'][0]['notti_oltre_max'] === 4);
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertSee('Variabile');
        $this->get('/tassa_di_soggiorno/rapporto'.$q)->assertOk()->assertViewHas('totalePeriodo', 12);
        $this->get('/tassa_di_soggiorno/rapporto/controllo'.$q)->assertOk()->assertViewHas('summary', fn ($d) => $d['totale_tassa'] == 12);
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$q)->assertOk()->getContent();
        $rows = array_map(fn ($r) => str_getcsv($r, ';', '"', ''), array_slice(explode("\n", $csv), 1));
        $this->assertSame(['4', '2', '4'], array_column($rows, 6));
        $this->assertSame([1.5, 3.0, 0.0], array_map('floatval', array_column($rows, 7)));
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-12-01', 'data_a' => '2027-01-07'])->assertRedirect();
        $e = TassaExport::firstOrFail();
        $saved = $e->getAttributes();
        $this->app->instance(TassaDiSoggiornoService::class, new TassaDiSoggiornoService);
        $this->get('/schedine/'.$p->id.'/tassa/anteprima?export_id='.$e->id)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 12);
        $this->assertSame($saved, $e->fresh()->getAttributes());
    }

    public function test_zero_per_limite_esplicitamente_nullo_solo_nel_profilo_sintetico(): void
    {
        // Fixture architetturale: non è il cap6 del profilo reale Bellaria.
        $this->app->instance(TassaDiSoggiornoService::class, new class extends TassaDiSoggiornoService
        {
            protected function profiliFiscaliBellaria(): array
            {
                $profilo = parent::profiliFiscaliBellaria()[0];
                $profilo['giorni_massimo'] = 0;
                $profilo['regola_versione'] = 'SINTETICO-LIMITE-ZERO';
                $profilo['regola_fonte'] = 'Fixture, nessuna fonte normativa';

                return [$profilo];
            }
        });
        $s = $this->hotel();
        $p = $this->soggiorno($s);
        foreach (['anteprima', 'print'] as $route) {
            $this->get('/schedine/'.$p->id.'/tassa/'.$route)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0 && $d['righe'][0]['notti_oltre_max'] === 3)->assertSee('Limite massimo di notti imponibili raggiunto')->assertDontSee('Soggiorno fuori dal periodo');
        }
        $this->assertSame(0, TassaExport::count());
    }

    public function test_date_incomplete_non_sono_zero_e_soggiorno_senza_notti_non_e_limite(): void
    {
        $s = $this->hotel();
        $p = $this->soggiorno($s, '2026-06-10', '2026-06-10');
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertSee('Soggiorno senza pernottamenti imponibili')->assertDontSee('Limite massimo di notti imponibili raggiunto');
        $p->update(['arrive' => '2026-12-31', 'departure' => '2027-01-01']);
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
        $p->update(['arrive' => '2026-06-10']);
        foreach ([null, '2026-06-09'] as $partenza) {
            $p->update(['departure' => $partenza]);
            $this->getJson('/schedine/'.$p->id.'/tassa/anteprima')->assertUnprocessable()->assertJsonValidationErrors('date_soggiorno');
            $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertUnprocessable();
            $this->assertSame(0, TassaExport::count());
        }
    }

    public function test_anteprima_tenant_estraneo_rifiutata(): void
    {
        $a = $this->hotel();
        $p = $this->soggiorno($a);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get('/schedine/'.$p->id.'/tassa/anteprima')->assertNotFound();
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertNotFound();
    }
}
