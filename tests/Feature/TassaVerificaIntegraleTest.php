<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\TassaEsenzione;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaVerificaIntegraleTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    public function test_codice_450_confermato_disponibile_senza_record_legacy_e_riconciliato(): void
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'scheda' => 1, 'name' => 'Familiare sintetico', 'surname' => 'Audit',
            'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => '1980-01-01', 'exent' => '450', 'is_arrive' => 0]);
        $this->get('/tassa_di_soggiorno')->assertOk()->assertViewHas('esenzioni', fn ($e) => $e->contains(fn ($v) => (string) $v->codice === '450' && $v->richiede_nota));
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk()->getContent();
        $rows = array_map(fn ($r) => str_getcsv($r, ';', '"', ''), array_slice(explode("\n", trim($csv)), 1));
        $this->assertSame(['450', '777'], array_column($rows, 0));
        $this->assertSame(['6', '4'], array_column($rows, 6));
        $this->assertEquals(0, array_sum(array_map(fn ($r) => $r[6] * $r[7], $rows)));
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertSee('Familiari del gestore se anagraficamente conviventi');
        $this->get('/tassa_di_soggiorno/rapporto/controllo?mese=6&anno=2026')->assertOk()->assertViewHas('reconciliationErrors', [])
            ->assertViewHas('esenzioniSummary', fn ($e) => $e->count() === 1 && (string) $e->first()['codice'] === '450' && $e->first()['notti_periodo'] === 6)
            ->assertViewHas('epilogo', fn ($e) => $e['totale_notti_oltre_max'] === 4);
        $this->assertSame(0, TassaEsenzione::count());
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $export = TassaExport::firstOrFail();
        $p->update(['exent' => 'NO']);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9);
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->getContent());
        $this->get('/schedine/'.$p->id.'/tassa/print?export_id='.$export->id)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
    }

    public function test_date_impossibili_o_relative_non_diventano_nascite_valide(): void
    {
        $service = new TassaDiSoggiornoService;
        foreach (['2008-02-31', '31/02/2008', '2008-13-10', '2008-06-00', 'tomorrow', '18 years ago', '1980-01-01xyz'] as $date) {
            $this->assertNull($service->parseDate($date), $date);
        }
        foreach (['2008-02-29', '29/02/2008', '29-02-2008'] as $date) {
            $this->assertSame('2008-02-29', $service->parseDate($date)?->toDateString());
        }
    }

    public function test_nascita_impossibile_blocca_il_calcolo_per_titolare_e_componente(): void
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $service = new TassaDiSoggiornoService;
        foreach (['titolare', 'componente'] as $tipo) {
            $p = new Schedina(['name' => 'Sintetico', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-20',
                'oa_date_nac' => $tipo === 'titolare' ? '2008-02-31' : '1980-01-01', 'exent' => 'NO']);
            $componenti = $tipo === 'componente' ? collect([new \App\Models\Componenti(['name' => 'Sintetico', 'date_nac' => '2008-02-31', 'exent' => 'NO'])]) : collect();
            try {
                $config = $service->configurazioneAutomatica($s, null, $service->parseDate($p->arrive));
                $service->dettaglioSchedina($p, $componenti, $config, $service->catalogoBellaria($s, collect()), $s);
                $this->fail('La nascita impossibile deve impedire il calcolo.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('nascita', $exception->errors());
            }
        }
    }

    public function test_bozze_web_arrivi_e_cestino_non_vengono_ricontati_nello_storico(): void
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'scheda' => 1, 'name' => 'Sintetico', 'surname' => 'Audit',
            'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        foreach (['bozza', 'web', 'arrivi'] as $circuito) {
            $copy = $p->replicate();
            $copy->forceFill(['circuito' => $circuito, 'is_arrive' => $circuito === 'arrivi'])->save();
        }
        $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 9);
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $export = TassaExport::firstOrFail();
        $csv = $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->getContent();
        $this->delete('/schedine/'.$p->id)->assertRedirect();
        $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->getContent());
    }
}
