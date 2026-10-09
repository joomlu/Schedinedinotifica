<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Services\TassaDiSoggiornoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaEtaCsvTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function config(): TassaDiSoggiorno
    {
        return new TassaDiSoggiorno(['tassa_soggiorno' => '1.50', 'giorni_massimo' => 6,
            'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
    }

    private function persona(string $nascita = '1980-01-01', int $notti = 10, string $codice = 'NO'): Schedina
    {
        return new Schedina(['name' => 'Persona sintetica', 'surname' => 'Verifica', 'arrive' => '2026-06-10',
            'departure' => Carbon::parse('2026-06-10')->addDays($notti)->toDateString(),
            'oa_date_nac' => $nascita, 'exent' => $codice]);
    }

    private function catalogo()
    {
        return collect(['400', '405', '410', '415', '420', '425', '430', '440', '777'])->map(
            fn ($codice) => new TassaEsenzione(['codice' => $codice, 'descrizione' => 'Motivo sintetico '.$codice, 'attivo' => true])
        );
    }

    private function dettaglio(Schedina $persona, $componenti = null, $config = null): array
    {
        return (new TassaDiSoggiornoService)->dettaglioSchedina($persona, $componenti ?? collect(),
            $config ?? $this->config(), $this->catalogo(), new Struttura(['citta' => 'Bellaria-Igea Marina', 'tipologia_generale' => 'Alberghiera', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']));
    }

    public function test_eta_per_notte_e_compleanno_incluso(): void
    {
        foreach (['2010-06-13' => 0, '1980-06-13' => 6, '2008-06-13' => 2,
            '2008-06-10' => 5, '2008-06-16' => 0] as $nascita => $tassate) {
            $r = $this->dettaglio($this->persona($nascita, 6));
            $this->assertSame($tassate, $r['righe'][0]['notti_tassate']);
            $this->assertEquals($tassate * 1.5, $r['totale']);
            $this->assertSame(6, array_sum(array_column($r['righe'][0]['segmenti'], 'notti_imponibili')));
        }
    }

    public function test_eta_storica_indipendente_dal_clock_anche_per_componenti(): void
    {
        try {
            $p = $this->persona('2008-06-13');
            $c = collect([new Componenti(['name' => 'Componente sintetico', 'date_nac' => '2008-06-13', 'exent' => 'NO'])]);
            Carbon::setTestNow('2025-01-01');
            $prima = $this->dettaglio($p, $c);
            Carbon::setTestNow('2030-01-01');
            $dopo = $this->dettaglio($p, $c);
            $this->assertSame($prima, $dopo);
            $this->assertEquals(6, $dopo['totale']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_codice_400_non_persistente_dopo_compleanno_e_altra_esenzione_prevale(): void
    {
        $this->assertEquals(3, $this->dettaglio($this->persona('2008-06-13', 10, '400'))['totale']);
        $this->assertEquals(0, $this->dettaglio($this->persona('2008-06-13', 10, '410'))['totale']);
    }

    public function test_ripartizione_777_imponibili_ed_esenti(): void
    {
        $s = new TassaDiSoggiornoService;
        foreach ([1, 5, 6, 7, 10, 20] as $notti) {
            foreach (['NO', '410'] as $codice) {
                $p = $this->persona('1980-01-01', $notti, $codice);
                $rows = $s->exportRows($this->dettaglio($p), $s->parseDate($p->arrive), $s->parseDate($p->departure));
                $this->assertSame($notti, array_sum(array_column($rows, 'pernottamenti')));
                $oltre = array_values(array_filter($rows, fn ($r) => (string) $r['tipo'] === '777'));
                $this->assertCount($notti > 6 ? 1 : 0, $oltre);
                if ($oltre) {
                    $this->assertSame($notti - 6, $oltre[0]['pernottamenti']);
                    $this->assertEquals(0, $oltre[0]['tariffa']);
                }
            }
        }
    }

    public function test_interazione_eta_e_777_senza_perdita_o_duplicazione(): void
    {
        $s = new TassaDiSoggiornoService;
        $p = $this->persona('2008-06-13');
        $r = $this->dettaglio($p);
        $rows = $s->exportRows($r, $s->parseDate($p->arrive), $s->parseDate($p->departure));
        $this->assertSame(['400', 0, 777], array_column($rows, 'tipo'));
        $this->assertSame([4, 2, 4], array_column($rows, 'pernottamenti'));
        $this->assertEquals(3, $r['totale']);
        $this->assertSame(10, array_sum(array_column($rows, 'pernottamenti')));
    }

    private function preparaHttp(): Struttura
    {
        $a = $this->structureFor(null);
        $a->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        TassaDiSoggiorno::create(array_merge($this->config()->getAttributes(), ['struttura_id' => $a->id]));
        foreach ($this->catalogo() as $e) {
            TassaEsenzione::create(array_merge($e->getAttributes(), ['struttura_id' => $a->id]));
        }

        return $a;
    }

    public function test_download_reale_codici_e_quantita_777(): void
    {
        $a = $this->preparaHttp();
        foreach (['NO', '400', '405', '410', '415', '420', '425', '430', '440', '777'] as $codice) {
            $p = $this->persona($codice === '400' ? '2010-01-01' : '1980-01-01', 10, $codice);
            Schedina::forceCreate(array_merge($p->getAttributes(), ['struttura_id' => $a->id, 'is_arrive' => 0]));
        }
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk()->getContent();
        $lines = explode("\n", $csv);
        $this->assertSame('01/06/2026;30/06/2026;', array_shift($lines));
        $this->assertCount(20, $lines);
        $codici = [];
        foreach ($lines as $line) {
            $r = explode(';', $line);
            $this->assertCount(9, $r); // Otto campi e delimitatore finale previsto negli esempi PDF.
            $this->assertSame('', $r[8]);
            $this->assertSame($r[0] === '777' ? '4' : '6', $r[6]);
            $this->assertSame(in_array($r[0], ['0'], true) ? '1.5' : '0', $r[7]);
            $codici[] = $r[0];
        }
        foreach (['400', '405', '410', '415', '420', '425', '430', '440', '777'] as $codice) {
            $this->assertContains($codice, $codici);
        }
        $this->assertSame(9, TassaEsenzione::count());
    }

    public function test_download_interazione_eta_e_riepilogo(): void
    {
        $a = $this->preparaHttp();
        Schedina::forceCreate(array_merge($this->persona('2008-06-13')->getAttributes(), ['struttura_id' => $a->id, 'is_arrive' => 0]));
        $lines = explode("\n", $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk()->getContent());
        $this->assertCount(4, $lines);
        $this->assertSame(['4', '2', '4'], array_map(fn ($line) => explode(';', $line)[6], array_slice($lines, 1)));
        $this->get('/tassa_di_soggiorno/rapporto/controllo?mese=6&anno=2026')->assertOk();
    }

    public function test_csv_isolato_per_tenant_e_anonimo(): void
    {
        $a = $this->preparaHttp();
        $b = $this->structureFor(null);
        Schedina::withoutGlobalScopes()->forceCreate(array_merge($this->persona()->getAttributes(), ['struttura_id' => $b->id, 'is_arrive' => 0]));
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026&sid='.$b->id)->assertOk()->getContent();
        $this->assertSame('01/06/2026;30/06/2026;', $csv);
        $this->put('/tassa_esenzioni/'.TassaEsenzione::first()->id, ['codice' => '999', 'descrizione' => 'Tentativo sintetico'])->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get('/tassa_di_soggiorno/rapporto/csv')->assertRedirect('/login');
    }

    public function test_periodo_e_limite_diverso_restano_invariati(): void
    {
        $p = $this->persona();
        $p->arrive = '2026-05-31';
        $p->departure = '2026-06-03';
        $this->assertSame(2, $this->dettaglio($p)['righe'][0]['notti_periodo']);
        $config = $this->config();
        $config->giorni_massimo = 10;
        $r = (new TassaDiSoggiornoService)->dettaglioSchedina($this->persona(), collect(), $config, $this->catalogo(), new Struttura(['citta' => 'Altro Comune']));
        $this->assertSame(10, $r['righe'][0]['notti_tassate']);
        $this->assertSame(0, $r['righe'][0]['notti_oltre_max']);
    }
}
