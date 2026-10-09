<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaCompletenessTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function contesto(): Struttura
    {
        $a = $this->structureFor(null);
        $a->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        TassaDiSoggiorno::create(['struttura_id' => $a->id, 'tassa_soggiorno' => '1.50', 'giorni_massimo' => 6,
            'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
        foreach (['400', '405', '410', '415', '420', '425', '430', '440', '450'] as $code) {
            TassaEsenzione::create(['struttura_id' => $a->id, 'codice' => $code, 'descrizione' => 'Motivo sintetico '.$code, 'attivo' => true]);
        }

        return $a;
    }

    private function persona(Struttura $a, int $notti = 10, string $code = 'NO', string $birth = '1980-01-01', string $arrival = '2026-06-10'): Schedina
    {
        return Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Persona sintetica', 'surname' => 'Audit', 'scheda' => 1,
            'arrive' => $arrival, 'departure' => Carbon::parse($arrival)->addDays($notti)->toDateString(),
            'oa_date_nac' => $birth, 'exent' => $code, 'is_arrive' => 0]);
    }

    private function query(): string
    {
        return '?data_da=2026-01-01&data_a=2026-12-31';
    }

    public function test_scenari_calcolo_ricevuta_report_csv_conservano_quantita_e_importi(): void
    {
        $a = $this->contesto();
        $scenari = [[10, 'NO', '2008-06-13', '2026-06-10', 3, 10], [3, 'NO', '1980-01-01', '2026-05-31', 3, 2], [10, 'NO', '1980-01-01', '2026-06-28', 9, 10]];
        foreach ([1, 6, 7, 10, 20] as $n) {
            $scenari[] = [$n, 'NO', '1980-01-01', '2026-06-10', min(6, $n) * 1.5, $n];
        }
        foreach (['400', '405', '410', '415', '420', '425', '430', '440'] as $code) {
            $scenari[] = [10, $code, $code === '400' ? '2010-01-01' : '1980-01-01', '2026-06-10', 0, 10];
        }
        $total = 0;
        $notti = 0;
        foreach ($scenari as [$n, $code, $birth, $arrival, $tax, $represented]) {
            $p = $this->persona($a, $n, $code, $birth, $arrival);
            $d = (new TassaDiSoggiornoService)->dettaglioSchedina($p, collect(), TassaDiSoggiorno::first(), TassaEsenzione::all(), $a);
            $this->assertEquals($tax, $d['totale']);
            $this->assertEquals($tax, array_sum(array_column($d['righe'], 'subtotale')));
            $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($data) => $data['totale'] == $tax)->assertSee('data-tassa-totale="'.$tax.'"', false);
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($data) => $data['totale'] == $tax);
            $total += $tax;
            $notti += $represented;
        }
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertOk()->getContent();
        $rows = array_map(fn ($line) => str_getcsv($line, ';', '"', ''), array_slice(explode("\n", $csv), 1));
        $this->assertSame('01/01/2026;31/12/2026;', explode("\n", $csv)[0]);
        $this->assertSame($notti, array_sum(array_map(fn ($r) => (int) $r[6], $rows)));
        $this->assertEquals($total, array_sum(array_map(fn ($r) => $r[6] * $r[7], $rows)));
        foreach ($rows as $r) {
            $this->assertSame($r[2], $r[1]);
        }
        $this->get('/tassa_di_soggiorno/rapporto'.$this->query())->assertOk()->assertViewHas('totalePeriodo', $total);
        $this->get('/tassa_di_soggiorno/rapporto/controllo'.$this->query())->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['totale_tassa'] == $total)
            ->assertViewHas('reconciliationErrors', []);
        $this->get('/tassa_di_soggiorno/rapporto/controllo/stampa'.$this->query())->assertOk()->assertSee('01/01/2026')->assertSee('31/12/2026');
    }

    public function test_clock_non_cambia_csv_storico_e_intervalli_validati(): void
    {
        $a = $this->contesto();
        $this->persona($a, 10, 'NO', '2008-06-13');
        try {
            Carbon::setTestNow('2025-01-01');
            $prima = $this->get('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertOk()->getContent();
            Carbon::setTestNow('2030-01-01');
            $dopo = $this->get('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertOk()->getContent();
            $this->assertSame($prima, $dopo);
        } finally {
            Carbon::setTestNow();
        }
        foreach (['data_da=2026-02-30&data_a=2026-03-01', 'data_da=2026-06-10&data_a=2026-06-01', 'data_da=2026-06-01'] as $q) {
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?'.$q)->assertUnprocessable();
        }
        $this->getJson('/tassa_di_soggiorno/rapporto?data_da=2025-01-01&data_a=2026-12-31')->assertUnprocessable();
    }

    public function test_versione_consolidata_immutabile_dopo_modifiche_e_isolata_per_tenant(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        $r = $this->post('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-06-01', 'data_a' => '2026-06-30'])->assertRedirect();
        $url = $r->headers->get('Location');
        $prima = $this->get($url)->assertOk()->getContent();
        $e = TassaExport::first();
        $this->assertSame(1, $e->versione);
        $this->assertSame(hash('sha256', $prima), $e->sha256);
        $this->assertStringNotContainsString('Persona sintetica', $e->getRawOriginal('snapshot'));
        $p->update(['name' => 'Persona sintetica modificata', 'exent' => '410', 'departure' => '2026-06-22']);
        $a->update(['classificazione' => '4 stelle']);
        TassaDiSoggiorno::first()->update(['tassa_soggiorno' => 2.5]);
        $this->assertSame($prima, $this->get($url)->assertOk()->getContent());
        $this->get('/schedine/'.$p->id.'/tassa/print?export_id='.$e->id)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9)->assertDontSee('Persona sintetica modificata');
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-06-01', 'data_a' => '2026-06-30'])->assertRedirect();
        $secondo = TassaExport::orderByDesc('id')->first();
        $this->assertSame(2, $secondo->versione);
        $this->assertSame($e->id, $secondo->precedente_id);
        $this->assertNotSame($e->sha256, $secondo->sha256);
        $this->get('/tassa_di_soggiorno/rapporto?export_id='.$e->id)->assertOk()->assertViewHas('totalePeriodo', 9);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get($url)->assertNotFound();
        $this->get('/schedine/'.$p->id.'/tassa/print?export_id='.$e->id)->assertNotFound();
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertNotFound();
        $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026&sid='.$a->id)->assertOk()->assertDontSee('Persona sintetica');
        $this->actingAs($this->actor('proprietario'));
        $this->get($url)->assertRedirect(route('strutture.seleziona.index'));
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertNotFound();
        $this->get('/schedine/'.$p->id.'/tassa/print?export_id='.$e->id)->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->get($url)->assertRedirect('/login');
    }

    public function test_periodo_fiscale_non_limita_registrazione_o_modifica_del_soggiorno(): void
    {
        $a = $this->contesto();
        $a->update(['tipo_apertura' => 'Annuale']);
        foreach ([['2026-02-10', '2026-02-13', 3, 0, 0], ['2026-05-10', '2026-05-13', 3, 0, 0], ['2026-07-10', '2026-07-13', 3, 3, 4.5], ['2026-11-10', '2026-11-13', 3, 0, 0], ['2026-05-31', '2026-06-03', 3, 2, 3]] as [$arrivo, $partenza, $notti, $pertinenti, $totale]) {
            $payload = array_replace($this->payloadSchedina(), ['arrive' => $arrivo, 'departure' => $partenza, 'relationship' => 'OSPITE SINGOLO', 'exent' => 'NO', 'oa_date_nac' => '1980-01-01']);
            $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $p = Schedina::orderByDesc('id')->firstOrFail();
            $this->assertSame($arrivo, $p->arrive);
            $this->assertSame($partenza, $p->departure);
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', function ($d) use ($notti, $pertinenti, $totale) {
                return ! isset($d['errore']) && $d['totale'] == $totale && $d['righe'][0]['notti_totali'] === $notti && $d['righe'][0]['notti_periodo'] === $pertinenti && $d['righe'][0]['notti_oltre_max'] === 0;
            });
            $this->put(route('schedina.update', $p->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == $totale);
            $this->assertSame($arrivo, $p->fresh()->arrive);
            $this->assertSame($partenza, $p->fresh()->departure);
            $dal = Carbon::parse($arrivo);
            $al = Carbon::parse($partenza);
            foreach ([new \App\Services\QuesturaTxtExportService, new \App\Services\IstatTabellaAService] as $servizio) {
                $soggiorno = $servizio->schedinePerPeriodo($a->id, $dal, $al)->firstWhere('id', $p->id);
                $this->assertNotNull($soggiorno);
                $this->assertSame($arrivo, $soggiorno->arrive);
                $this->assertSame($partenza, $soggiorno->departure);
                $this->assertEquals($notti, $dal->diffInDays(Carbon::parse($soggiorno->departure)));
            }
        }
    }

    public function test_configurazione_fiscale_invalida_non_blocca_schedina_ne_inventa_zero(): void
    {
        $this->contesto();
        foreach (['incoerente', 'incompleta', 'mancante'] as $caso) {
            $config = TassaDiSoggiorno::first();
            if ($caso === 'incoerente') {
                $config->update(['inizio' => '2026-03-01', 'fine' => '2026-10-01']);
            } elseif ($caso === 'incompleta') {
                $config->update(['tassa_soggiorno' => null]);
            } else {
                $config->delete();
                // Senza record fiscale un profilo certificato ora è derivabile: per il caso non disponibile manca la fonte classificatoria.
                \App\Models\Struttura::whereKey($config->struttura_id)->update(['classificazione' => null, 'classificazione_id' => null]);
            }
            $this->get('/schedine/nuova')->assertOk()->assertSee('il calcolo della Tassa non è disponibile');
            $payload = array_replace($this->payloadSchedina(), ['relationship' => 'OSPITE SINGOLO', 'exent' => 'NO']);
            $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $p = Schedina::orderByDesc('id')->firstOrFail();
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null && $d['righe'] === [] && isset($d['errore']))->assertSee('il calcolo della Tassa non è disponibile');
            $this->put(route('schedina.update', $p->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $this->get('/schedine')->assertOk()->assertSee('Calcolo non disponibile');
            $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable();
        }
    }

    public function test_zero_movimenti_con_configurazione_valida_non_implica_chiusura(): void
    {
        $a = $this->contesto();
        $a->update(['tipo_apertura' => 'Annuale']);
        // Esiste attività in giugno: luglio vuoto non attesta la chiusura della struttura.
        $this->persona($a, 3);
        foreach ([1, 5, 20, 31] as $giorni) {
            $fine = Carbon::parse('2026-07-01')->addDays($giorni - 1)->toDateString();
            $query = '?data_da=2026-07-01&data_a='.$fine;
            $this->get('/tassa_di_soggiorno/rapporto'.$query)->assertOk()
                ->assertViewHas('totalePeriodo', 0)->assertViewHas('righe', fn ($righe) => $righe->total() === 0)
                ->assertSee('Nessun dato per il periodo selezionato.');
            $this->get('/tassa_di_soggiorno/rapporto/controllo'.$query)->assertOk()
                ->assertViewHas('summary', fn ($summary) => $summary['totale_tassa'] == 0);
            $csv = '01/07/2026;'.Carbon::parse($fine)->format('d/m/Y').';';
            $this->get('/tassa_di_soggiorno/rapporto/csv'.$query)->assertOk()->assertContent($csv);
            $this->post('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-07-01', 'data_a' => $fine])->assertRedirect();
            $snapshot = TassaExport::orderByDesc('id')->firstOrFail()->snapshot;
            $this->assertSame([], $snapshot['movimenti']);
            $this->assertSame([], $snapshot['calcoli']);
            $this->assertSame($csv, $snapshot['csv']);
            $this->assertSame('Annuale', $a->fresh()->tipo_apertura);
        }
        // Apertura/chiusura già configurate restano dati separati: non vengono dedotte dai movimenti.
        $a->update(['tipo_apertura' => 'Stagionale', 'data_apertura' => '2026-06-01', 'data_chiusura' => '2026-06-30']);
        $prima = $a->fresh()->getAttributes();
        $this->get('/tassa_di_soggiorno/rapporto?data_da=2026-07-01&data_a=2026-07-31')->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->assertSame($prima, $a->fresh()->getAttributes());
    }

    public function test_configurazione_invalida_blocca_fisco_anche_senza_movimenti(): void
    {
        $a = $this->contesto();
        $this->persona($a, 3);
        $config = TassaDiSoggiorno::firstOrFail();
        $valida = ['tassa_soggiorno' => '1.50', 'giorni_massimo' => 6, 'inizio' => '2026-06-01', 'fine' => '2026-09-30'];
        foreach ([['inizio' => '2026-03-01', 'fine' => '2026-10-01'], ['inizio' => null], ['fine' => null], ['tassa_soggiorno' => null], ['tassa_soggiorno' => '2.50'], ['giorni_massimo' => null], ['giorni_massimo' => 10]] as $errore) {
            $config->update(array_replace($valida, $errore));
            foreach ([6, 7] as $mese) {
                $dal = sprintf('2026-%02d-01', $mese);
                $al = Carbon::parse($dal)->endOfMonth()->toDateString();
                $query = '?data_da='.$dal.'&data_a='.$al;
                foreach (['', '/controllo', '/csv', '/controllo/csv', '/controllo/stampa'] as $percorso) {
                    $this->getJson('/tassa_di_soggiorno/rapporto'.$percorso.$query)->assertUnprocessable();
                }
                $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => $dal, 'data_a' => $al])->assertUnprocessable();
                $this->assertSame(0, TassaExport::count());
            }
        }
        $config->delete();
        // Nessuna regola automatica può essere derivata quando manca la classificazione.
        $a->update(['classificazione' => null, 'classificazione_id' => null]);
        foreach ([6, 7] as $mese) {
            $query = '?mese='.$mese.'&anno=2026';
            $this->getJson('/tassa_di_soggiorno/rapporto'.$query)->assertUnprocessable();
            $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$query)->assertUnprocessable();
            $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['mese' => $mese, 'anno' => 2026])->assertUnprocessable();
        }
        $this->assertSame(0, TassaExport::count());
    }

    public function test_snapshot_valido_vuoto_resta_consultabile_dopo_configurazione_invalida(): void
    {
        $this->contesto();
        $periodo = ['data_da' => '2026-07-01', 'data_a' => '2026-07-31'];
        $this->post('/tassa_di_soggiorno/rapporto/consolida', $periodo)->assertRedirect();
        $export = TassaExport::firstOrFail();
        $csv = $export->snapshot['csv'];
        TassaDiSoggiorno::firstOrFail()->update(['inizio' => '2026-03-01', 'fine' => '2026-10-01']);
        $this->get('/tassa_di_soggiorno/rapporto?export_id='.$export->id)->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->get('/tassa_di_soggiorno/export/'.$export->id)->assertOk()->assertContent($csv);
        $this->getJson('/tassa_di_soggiorno/rapporto?'.http_build_query($periodo))->assertUnprocessable();
        $this->assertSame(1, TassaExport::count());
    }

    private function verificaCategoriaNonCertificata(?string $tipo, ?string $classe): void
    {
        $a = $this->contesto();
        $a->update(['tipologia_struttura' => $tipo, 'classificazione' => $classe, 'classificazione_id' => null]);
        $payload = array_replace($this->payloadSchedina(), ['relationship' => 'OSPITE SINGOLO', 'exent' => 'NO', 'oa_date_nac' => '1980-01-01', 'arrive' => '2026-06-10', 'departure' => '2026-06-20']);
        $this->get('/schedine/nuova')->assertOk();
        $this->post('/schedine', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $p = Schedina::orderByDesc('id')->firstOrFail();
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertSee($classe === null || $classe === '' ? 'Classificazione della struttura non configurata nei Dati struttura.' : 'Categoria della struttura non ancora configurata/certificata')->assertDontSee('Totale tassa da pagare')->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null && $d['righe'] === [] && isset($d['errore']));
        $this->put('/schedine/'.$p->id, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/schedine')->assertOk()->assertSee('Calcolo non disponibile');
        foreach (['/schedine/'.$p->id.'/tassa/print', '/tassa_di_soggiorno/rapporto', '/tassa_di_soggiorno/rapporto/controllo', '/tassa_di_soggiorno/rapporto/csv', '/tassa_di_soggiorno/rapporto/controllo/csv', '/tassa_di_soggiorno/rapporto/controllo/stampa'] as $url) {
            $this->getJson($url.$this->query())->assertUnprocessable()->assertJsonValidationErrors('categoria_tassa_non_certificata');
        }
        // Anche un intervallo senza movimenti deve rifiutare la categoria, non produrre un CSV vuoto valido.
        foreach (['/tassa_di_soggiorno/rapporto', '/tassa_di_soggiorno/rapporto/controllo', '/tassa_di_soggiorno/rapporto/csv'] as $url) {
            $this->getJson($url.'?data_da=2026-07-01&data_a=2026-07-31')->assertUnprocessable()->assertJsonValidationErrors('categoria_tassa_non_certificata');
        }
        $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['data_da' => '2026-06-01', 'data_a' => '2026-06-30'])->assertUnprocessable()->assertJsonValidationErrors('categoria_tassa_non_certificata');
        $this->assertSame(0, TassaExport::count());
        $this->assertSame('1.50', TassaDiSoggiorno::first()->tassa_soggiorno);
        foreach ([new \App\Services\QuesturaTxtExportService, new \App\Services\IstatTabellaAService] as $service) {
            $actual = $service->schedinePerPeriodo($a->id, Carbon::parse('2026-06-10'), Carbon::parse('2026-06-20'))->firstWhere('id', $p->id);
            $this->assertNotNull($actual);
            $this->assertSame('2026-06-10', $actual->arrive);
            $this->assertSame('2026-06-20', $actual->departure);
        }
    }

    public function test_rta_non_certificata_non_blocca_schedina_ma_blocca_tutti_i_percorsi_fiscali(): void
    {
        $this->verificaCategoriaNonCertificata('RTA', '3 stelle');
    }

    public function test_villaggio_non_certificato_non_blocca_schedina_ma_blocca_tutti_i_percorsi_fiscali(): void
    {
        $this->verificaCategoriaNonCertificata('Villaggio turistico', '3 stelle');
    }

    public function test_categoria_nulla_vuota_sconosciuta_o_ambigua_fallisce_chiusa(): void
    {
        foreach ([null, '', 'Categoria sconosciuta', '3 o 4 stelle'] as $classe) {
            $this->verificaCategoriaNonCertificata('Albergo', $classe);
        }
    }

    public function test_hotel_da_una_a_cinque_stelle_conservano_tariffe_e_valori_fiscali(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        foreach ([1 => 1, 2 => 1, 3 => 1.5, 4 => 2.5, 5 => 2.5] as $stelle => $tariffa) {
            $a->update(['classificazione' => $stelle.' '.($stelle === 1 ? 'stella' : 'stelle')]);
            TassaDiSoggiorno::first()->update(['tassa_soggiorno' => $tariffa]);
            $regola = (new TassaDiSoggiornoService)->validaCategoriaBellaria($a);
            $this->assertEquals($tariffa, $regola['tassa_soggiorno']);
            $this->assertSame(6, $regola['giorni_massimo']);
            $totale = 6 * $tariffa;
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => ! isset($d['errore']) && $d['totale'] == $totale);
            $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == $totale);
            $this->get('/tassa_di_soggiorno/rapporto'.$this->query())->assertOk()->assertViewHas('totalePeriodo', $totale);
            $this->get('/tassa_di_soggiorno/rapporto/controllo'.$this->query())->assertOk()->assertViewHas('reconciliationErrors', []);
            $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertOk()->getContent();
            $rows = array_map(fn ($r) => str_getcsv($r, ';', '"', ''), array_slice(explode("\n", $csv), 1));
            $this->assertSame(['0', '777'], array_column($rows, 0));
            $this->assertSame(['6', '4'], array_column($rows, 6));
            $this->assertEquals($tariffa, $rows[0][7]);
            $this->assertEquals($totale, array_sum(array_map(fn ($r) => $r[6] * $r[7], $rows)));
        }
    }

    private function payloadSchedina(): array
    {
        return [
            'customer_privacy_consent' => '1',
            'name' => 'QA',
            'surname' => 'Schedina',
            'sex' => 'M',
            'arrive' => '2026-03-15',
            'departure' => '2026-03-17',
            'cant_people' => '1',
            'room' => '1',
            'beds' => '1',
            'oa_country' => 'ITALIA',
            'oa_region' => 'Lazio',
            'oa_prov' => 'RM',
            'oa_city' => 'Roma',
            'oa_city_nac' => 'ITALIANA',
            'oa_date_nac' => '1990-01-01',
            'or_country' => 'ITALIA',
            'or_region' => 'Lazio',
            'or_prov' => 'RM',
            'or_city' => 'Roma',
            'or_cap' => '00100',
            'or_typeaway' => 'Via',
            'or_address' => 'Via Test',
            'or_num' => '10',
            'or_doctype' => "CARTA DI IDENTITA'",
            'or_doc' => 'QATEST',
            'or_published_date' => '2025-01-01',
            'or_expire' => '2030-01-01',
            'or_published' => 'Comune di Roma',
            'or_published_country' => 'ITALIA',
            'or_published_city' => 'Roma',
        ];
    }

    public function test_esenzioni_codificate_attraversano_salvataggio_http_e_tutti_gli_output(): void
    {
        $a = $this->contesto();
        foreach (['400', '405', '410', '415', '420', '425', '430', '440'] as $code) {
            $payload = array_replace($this->payloadSchedina(), ['exent' => $code, 'relationship' => 'OSPITE SINGOLO',
                'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => $code === '400' ? '2010-01-01' : '1980-01-01']);
            $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $p = Schedina::orderByDesc('id')->firstOrFail();
            $this->assertSame($code, $p->exent);
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] == 0);
            $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 0);
        }
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertOk()->getContent();
        $records = array_map(fn ($line) => str_getcsv($line, ';', '"', ''), array_slice(explode("\n", $csv), 1));
        foreach (['400', '405', '410', '415', '420', '425', '430', '440'] as $code) {
            $this->assertContains($code, array_column($records, 0));
        }
        $this->assertSame(80, array_sum(array_map(fn ($r) => (int) $r[6], $records)));
        $this->assertEquals(0, array_sum(array_map(fn ($r) => $r[6] * $r[7], $records)));
    }

    public function test_componenti_salvati_da_http_e_777_rifiutato_come_causa_personale(): void
    {
        $a = $this->contesto();
        \App\Models\GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $payload = array_replace($this->payloadSchedina(), ['exent' => 'NO', 'relationship' => 'CAPO FAMIGLIA', 'cant_people' => 2,
            'arrive' => '2026-06-10', 'departure' => '2026-06-20']);
        $payload['componenti'] = [['name' => 'Componente sintetico', 'surname' => 'Audit', 'sex' => 'F', 'relationship' => 'FAMILIARE',
            'exent' => '410', 'date_nac' => '1980-01-01', 'country_nac' => 'FRANCIA', 'city_nac' => 'FRANCESE', 'country' => 'FRANCIA', 'city' => 'Parigi']];
        $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $p = Schedina::orderByDesc('id')->first();
        $this->assertSame('410', $p->componenti->first()->exent);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9);
        $payload['componenti'][0]['exent'] = '777';
        $this->postJson(route('schedina.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('componenti.0.exent');
        $this->assertSame(1, Schedina::count());
    }

    public function test_catalogo_ui_legacy_bloccato_e_regole_alberghi_documentate(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertSee('value="405"', false)->assertDontSee('value="777"', false);
        foreach ([1 => 1, 2 => 1, 3 => 1.5, 4 => 2.5, 5 => 2.5] as $stelle => $tariffa) {
            $s = new Struttura(['tipologia_struttura' => 'Albergo', 'classificazione' => $stelle.' '.($stelle === 1 ? 'stella' : 'stelle')]);
            $regola = (new TassaDiSoggiornoService)->regolaAlbergoBellaria($s);
            $this->assertEquals($tariffa, $regola['tassa_soggiorno']);
            $this->assertSame(6, $regola['giorni_massimo']);
        }
        $this->assertNull((new TassaDiSoggiornoService)->regolaAlbergoBellaria(new Struttura(['tipologia_struttura' => 'RTA', 'classificazione' => '3 stelle'])));
        $p->update(['exent' => 'Si']);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertUnprocessable();
        $this->assertSame('Si', $p->fresh()->exent);
    }

    public function test_configurazione_stagionale_legacy_e_nascita_futura_bloccano_senza_scritture(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        $a->update(['tipologia_struttura' => 'Albergo', 'classificazione' => '2 stelle']);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertUnprocessable();
        $a->update(['classificazione' => '3 stelle']);
        TassaDiSoggiorno::first()->update(['inizio' => '2026-03-01', 'fine' => '2026-10-01']);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertUnprocessable();
        $this->assertSame('03-01', TassaDiSoggiorno::first()->inizio->format('m-d'));
        TassaDiSoggiorno::first()->update(['inizio' => '2026-06-01', 'fine' => '2026-09-30']);
        TassaDiSoggiorno::first()->update(['giorni_massimo' => 10]);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertUnprocessable();
        TassaDiSoggiorno::first()->update(['giorni_massimo' => 6]);
        $p->update(['oa_date_nac' => '2030-01-01']);
        $this->getJson('/tassa_di_soggiorno/rapporto/csv'.$this->query())->assertUnprocessable();
        $this->assertSame('2030-01-01', $p->fresh()->oa_date_nac);
    }

    public function test_configurazione_http_immagine_e_rimozione_opzionale(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        $foto = \Illuminate\Http\UploadedFile::fake()->createWithContent('panorama-sintetico.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aSu0AAAAASUVORK5CYII='));
        $this->put(route('tassa_di_soggiorno.update'), ['ricevuta_foto' => $foto])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertStringStartsWith('tassa-ricevute/'.$a->id.'/', TassaDiSoggiorno::first()->ricevuta_immagine);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertSee('class="ricevuta-immagine"', false);
        $this->put(route('tassa_di_soggiorno.update'), ['ricevuta_senza_immagine' => 1])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull(TassaDiSoggiorno::first()->ricevuta_immagine);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertDontSee('class="ricevuta-immagine"', false);
    }

    public function test_famiglia_loghi_e_immagine_opzionale(): void
    {
        $a = $this->contesto();
        $p = $this->persona($a);
        Componenti::forceCreate(['schedina_id' => $p->id, 'struttura_id' => $a->id, 'name' => 'Componente sintetico', 'date_nac' => '2008-06-13', 'exent' => 'NO']);
        $r = $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 12);
        $r->assertDontSee('alt="Logo struttura"', false)->assertDontSee('class="ricevuta-immagine"', false)->assertSee('Schedine di Notifica - Tanggo Platform');
        $a->update(['logo' => 'storage/sintetico.png', 'logo_citta' => 'storage/comune-sintetico.png']);
        TassaDiSoggiorno::first()->update(['ricevuta_immagine' => 'storage/tassa-ricevute/sintetico.png']);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertSee('storage/sintetico.png')->assertSee('class="ricevuta-immagine"', false);
    }
}
