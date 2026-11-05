<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\TassaDiSoggiorno;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaRiepilogoEsenzioniAuditTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    private function soggiorno(string $nascita, string $esenzione = 'NO'): Schedina
    {
        $struttura = $this->structureFor(null);
        $struttura->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
        $this->actingAs($this->actor('struttura_user', null, $struttura->id));
        TassaDiSoggiorno::create(['struttura_id' => $struttura->id, 'tassa_soggiorno' => '1.50', 'giorni_massimo' => 6,
            'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);

        return Schedina::forceCreate(['struttura_id' => $struttura->id, 'scheda' => 1, 'name' => 'Ospite sintetico', 'surname' => 'Audit',
            'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => $nascita, 'exent' => $esenzione, 'is_arrive' => 0]);
    }

    public function test_compleanno_parziale_riconcilia_esenzioni_imponibili_e_oltre_limite(): void
    {
        $this->soggiorno('2008-06-13');
        // 10–13 giugno esenti, 14–15 imponibili, 16–19 oltre il tetto: 4 + 2 + 4 = 10.
        $response = $this->get('/tassa_di_soggiorno/rapporto/controllo?mese=6&anno=2026')->assertOk();
        $response->assertViewHas('summary', fn ($s) => $s['totale_notti_imponibili'] === 2 && $s['totale_tassa'] == 3);
        $response->assertViewHas('esenzioniSummary', fn ($s) => $s->count() === 1 && (string) $s->first()['codice'] === '400'
            && $s->first()['notti_periodo'] === 4 && $s->first()['quantita'] === 1);
        $response->assertViewHas('epilogo', fn ($s) => $s['totale_notti_oltre_max'] === 4);
        $response->assertViewHas('reconciliationErrors', []);
    }

    public function test_esenzione_totale_non_riconta_le_notti_777_tra_le_notti_del_motivo(): void
    {
        $this->soggiorno('2010-01-01', '400');
        $response = $this->get('/tassa_di_soggiorno/rapporto/controllo?mese=6&anno=2026')->assertOk();
        $response->assertViewHas('esenzioniSummary', fn ($s) => $s->count() === 1 && $s->first()['notti_periodo'] === 6);
        $response->assertViewHas('epilogo', fn ($s) => $s['totale_notti_oltre_max'] === 4);
        $response->assertViewHas('summary', fn ($s) => $s['totale_tassa'] == 0);
    }

    public function test_stampa_per_arrivo_non_dichiara_incassi_o_riversamento_mensile(): void
    {
        $schedina = $this->soggiorno('1980-01-01');
        $schedina->update(['arrive' => '2026-06-28', 'departure' => '2026-07-08']);
        $this->get('/tassa_di_soggiorno/rapporto/controllo/stampa?mese=6&anno=2026')->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['totale_tassa'] == 9)
            ->assertSee('Inclusione per data di arrivo')
            ->assertDontSee('Totale mensile da versare al Comune')
            ->assertSee('Non attesta incassi o versamenti al Comune');
        $this->get('/tassa_di_soggiorno/rapporto/controllo?mese=7&anno=2026')->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['totale_tassa'] == 0);
    }

    public function test_stampa_mostra_profilo_effettivo_senza_record_legacy(): void
    {
        $this->soggiorno('1980-01-01');
        TassaDiSoggiorno::firstOrFail()->delete();
        $this->get('/tassa_di_soggiorno/rapporto/controllo/stampa?mese=6&anno=2026')->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['totale_tassa'] == 9)
            ->assertViewHas('config', fn ($c) => $c && (float) $c->tassa_soggiorno === 1.5 && $c->giorni_massimo === 6)
            ->assertSee('Aliquota: 1,50');
    }

    public function test_nascita_assente_non_viene_classificata_come_adulto_pagante(): void
    {
        foreach ([['titolare', 'NO'], ['componente', 'NO'], ['titolare', '400'], ['componente', '400']] as [$tipo, $codice]) {
            $p = $this->soggiorno('1980-01-01', $tipo === 'titolare' ? $codice : 'NO');
            if ($tipo === 'titolare') {
                $p->update(['oa_date_nac' => null]);
            } else {
                \App\Models\Componenti::forceCreate(['struttura_id' => $p->struttura_id, 'schedina_id' => $p->id,
                    'name' => 'Componente sintetico', 'surname' => 'Audit', 'date_nac' => null, 'exent' => $codice]);
            }
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertUnprocessable()->assertJsonValidationErrors('nascita');
            $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable()->assertJsonValidationErrors('nascita');
            $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertUnprocessable();
            $this->assertSame(0, \App\Models\TassaExport::count());
        }
    }

    public function test_residente_bellaria_non_viene_addebitato_senza_raccordo_fiscale(): void
    {
        foreach (['titolare', 'componente'] as $tipo) {
            $p = $this->soggiorno('1980-01-01');
            if ($tipo === 'titolare') {
                $p->update(['or_country' => 'ITALIA', 'or_city' => 'BELLARIA-IGEA MARINA']);
            } else {
                \App\Models\Componenti::forceCreate(['struttura_id' => $p->struttura_id, 'schedina_id' => $p->id,
                    'name' => 'Residente sintetico', 'surname' => 'Audit', 'date_nac' => '1980-01-01',
                    'country' => 'Italia', 'city' => 'Bellaria Igea Marina', 'exent' => 'NO']);
            }
            // Il residente non è soggetto passivo (art.3); non inventare un codice StayTour.
            $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable()->assertJsonValidationErrors('residenza');
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertUnprocessable()->assertJsonValidationErrors('residenza');
            $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertUnprocessable();
            $this->assertSame(0, \App\Models\TassaExport::count());
        }
    }

    public function test_omonimo_estero_e_residente_fuori_stagione_non_vengono_bloccati(): void
    {
        $p = $this->soggiorno('1980-01-01');
        $p->update(['or_country' => 'FRANCIA', 'or_city' => 'Bellaria Igea Marina']);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9);
        $p->update(['or_country' => 'ITALIA', 'arrive' => '2026-05-10', 'departure' => '2026-05-12']);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
    }

    public function test_snapshot_consolidato_resta_immutato_dopo_correzione_residenza(): void
    {
        $p = $this->soggiorno('1980-01-01');
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk()->getContent();
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $export = \App\Models\TassaExport::firstOrFail();
        $p->update(['or_country' => 'ITALIA', 'or_city' => 'Bellaria-Igea Marina']);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertUnprocessable();
        $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->assertContent($csv);
        $this->get('/tassa_di_soggiorno/rapporto?export_id='.$export->id)->assertOk()->assertViewHas('totalePeriodo', 9);
        $this->assertSame($export->sha256, $export->fresh()->sha256);
        $this->assertSame(1, \App\Models\TassaExport::count());
    }

    public function test_esenzione_esplicita_indipendente_dall_eta_non_viene_rimossa(): void
    {
        $p = $this->soggiorno('1980-01-01', '410');
        $p->update(['oa_date_nac' => null]);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()
            ->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
    }
}
