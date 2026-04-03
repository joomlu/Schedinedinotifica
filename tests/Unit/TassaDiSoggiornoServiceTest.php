<?php

namespace Tests\Unit;

use App\Models\Schedina;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TassaDiSoggiornoServiceTest extends TestCase
{
    public function test_automatic_child_exemption_uses_configured_municipal_code_when_available(): void
    {
        $service = new TassaDiSoggiornoService();

        $schedina = new Schedina([
            'name' => 'Giulia',
            'surname' => 'Bianchi',
            'arrive' => '2026-03-11',
            'departure' => '2026-03-29',
            'oa_date_nac' => '2014-03-15',
            'exent' => null,
        ]);

        $config = new TassaDiSoggiorno([
            'max_age_children' => 17,
            'min_age_adult' => 18,
            'giorni_massimo' => 6,
            'tassa_soggiorno' => '1.5',
        ]);

        $esenzioneMinori = new TassaEsenzione([
            'codice' => '400',
            'descrizione' => 'Minori fino al compimento del 18° anno di età',
            'attivo' => true,
        ]);

        $dettaglio = $service->dettaglioSchedina(
            $schedina,
            new Collection(),
            $config,
            collect([$esenzioneMinori])
        );

        $this->assertSame('400', $dettaglio['righe'][0]['codice']);
    }

    public function test_exemption_codes_never_pay_and_still_generate_777_after_max_days(): void
    {
        $service = new TassaDiSoggiornoService();

        $schedina = new Schedina([
            'name' => 'Giulia',
            'surname' => 'Bianchi',
            'arrive' => '2026-03-11',
            'departure' => '2026-03-20',
            'oa_date_nac' => '2014-03-15',
            'exent' => '400',
        ]);

        $config = new TassaDiSoggiorno([
            'max_age_children' => 17,
            'min_age_adult' => 18,
            'giorni_massimo' => 6,
            'tassa_soggiorno' => '1.5',
        ]);

        $esenzioneMinori = new TassaEsenzione([
            'codice' => '400',
            'descrizione' => 'Minori fino al compimento del 18° anno di età',
            'attivo' => true,
        ]);

        $dettaglio = $service->dettaglioSchedina(
            $schedina,
            new Collection(),
            $config,
            collect([$esenzioneMinori])
        );

        $rows = $service->exportRows(
            $dettaglio,
            $service->parseDate('2026-03-11'),
            $service->parseDate('2026-03-20')
        );

        $this->assertSame('400', $dettaglio['righe'][0]['codice']);
        $this->assertSame(6, $dettaglio['righe'][0]['notti_imponibili']);
        $this->assertSame(0, $dettaglio['righe'][0]['notti_tassate']);
        $this->assertSame(3, $dettaglio['righe'][0]['notti_oltre_max']);
        $this->assertSame(0.0, $dettaglio['righe'][0]['aliquota']);
        $this->assertSame(0.0, $dettaglio['righe'][0]['subtotale']);

        $this->assertCount(2, $rows);
        $this->assertSame('400', $rows[0]['tipo']);
        $this->assertSame(6, $rows[0]['pernottamenti']);
        $this->assertSame(0.0, $rows[0]['tariffa']);
        $this->assertSame(777, $rows[1]['tipo']);
        $this->assertSame(3, $rows[1]['pernottamenti']);
        $this->assertSame(0, $rows[1]['tariffa']);
    }
}
