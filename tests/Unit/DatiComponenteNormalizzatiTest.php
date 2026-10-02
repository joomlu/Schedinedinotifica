<?php

namespace Tests\Unit;

use App\Support\Componenti\DatiComponenteNormalizzati;
use PHPUnit\Framework\TestCase;

class DatiComponenteNormalizzatiTest extends TestCase
{
    public function test_componente_valido_non_genera_errori(): void
    {
        $rows = [
            [
                'name' => 'Mario',
                'surname' => 'Rossi',
                'sex' => 'M',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Italiana',
                'country_nac' => 'Italia',
                'regione_nac' => 'Emilia-Romagna',
                'province_nac' => 'RN',
                'comune_nac' => 'Rimini',
                'date_nac' => '2001-10-02',
                'country' => 'Italia',
                'regione' => 'Emilia-Romagna',
                'province' => 'RN',
                'city' => 'Rimini',
                'typeaway' => 'Via',
                'address' => 'Roma',
                'number' => '10',
                'cap' => '47921',
            ],
        ];

        $normalizzate = DatiComponenteNormalizzati::normalizzaRighe($rows);
        $this->assertCount(1, $normalizzate);
        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($normalizzate));
    }

    public function test_normalizzazione_trim_stringhe(): void
    {
        $rows = [
            [
                'name' => '  Mario  ',
                'surname' => '  Rossi ',
                'sex' => ' M ',
            ],
        ];

        $normalizzate = DatiComponenteNormalizzati::normalizzaRighe($rows);

        $this->assertSame('Mario', $normalizzate[0]['name']);
        $this->assertSame('Rossi', $normalizzate[0]['surname']);
        $this->assertSame('M', $normalizzate[0]['sex']);
    }

    public function test_riga_completamente_vuota_viene_scartata(): void
    {
        $rows = [
            [
                'name' => ' ',
                'surname' => '',
                'sex' => null,
                'relationship' => 'FAMILIARE',
            ],
        ];

        $normalizzate = DatiComponenteNormalizzati::normalizzaRighe($rows);
        $this->assertSame([], $normalizzate);
    }

    public function test_campo_obbligatorio_mancante_produce_errore(): void
    {
        $rows = [
            [
                'name' => 'Mario',
                'surname' => '',
                'sex' => 'M',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Italiana',
                'country_nac' => 'Italia',
                'regione_nac' => 'Emilia-Romagna',
                'province_nac' => 'RN',
                'comune_nac' => 'Rimini',
                'date_nac' => '2001-10-02',
                'country' => 'Italia',
                'regione' => 'Emilia-Romagna',
                'province' => 'RN',
                'city' => 'Rimini',
                'typeaway' => 'Via',
                'address' => 'Roma',
                'number' => '10',
                'cap' => '47921',
            ],
        ];

        $errori = DatiComponenteNormalizzati::validaRighe(DatiComponenteNormalizzati::normalizzaRighe($rows));

        $this->assertArrayHasKey('componenti.0.surname', $errori);
    }

    public function test_sex_non_valido_con_controllo_stretto(): void
    {
        $rows = [
            [
                'name' => 'Mario',
                'surname' => 'Rossi',
                'sex' => 'X',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Italiana',
                'country_nac' => 'Italia',
                'regione_nac' => 'Emilia-Romagna',
                'province_nac' => 'RN',
                'comune_nac' => 'Rimini',
                'date_nac' => '2001-10-02',
                'country' => 'Italia',
                'regione' => 'Emilia-Romagna',
                'province' => 'RN',
                'city' => 'Rimini',
                'typeaway' => 'Via',
                'address' => 'Roma',
                'number' => '10',
                'cap' => '47921',
            ],
        ];

        $errori = DatiComponenteNormalizzati::validaRighe(
            DatiComponenteNormalizzati::normalizzaRighe($rows),
            true
        );

        $this->assertArrayHasKey('componenti.0.sex', $errori);
    }

    public function test_data_nascita_valida_viene_normalizzata(): void
    {
        $this->assertSame('2026-10-02', DatiComponenteNormalizzati::normalizzaData('02/10/2026'));
    }

    public function test_data_nascita_non_valida_restituisce_null(): void
    {
        $this->assertNull(DatiComponenteNormalizzati::normalizzaData('not-a-date'));
    }

    public function test_cap_nac_e_facoltativo(): void
    {
        $rows = [
            [
                'name' => 'Mario',
                'surname' => 'Rossi',
                'sex' => 'M',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Italiana',
                'country_nac' => 'Italia',
                'regione_nac' => 'Emilia-Romagna',
                'province_nac' => 'RN',
                'comune_nac' => 'Rimini',
                'date_nac' => '2001-10-02',
                'country' => 'Italia',
                'regione' => 'Emilia-Romagna',
                'province' => 'RN',
                'city' => 'Rimini',
                'typeaway' => 'Via',
                'address' => 'Roma',
                'number' => '10',
                'cap' => '47921',
            ],
        ];

        $errori = DatiComponenteNormalizzati::validaRighe(DatiComponenteNormalizzati::normalizzaRighe($rows));
        $this->assertSame([], $errori);
    }

    public function test_relationship_viene_preservato(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([
            ['name' => 'Mario', 'surname' => 'Rossi', 'sex' => 'M', 'relationship' => 'MEMBRO GRUPPO'],
        ]);

        $this->assertSame('MEMBRO GRUPPO', $rows[0]['relationship']);
    }

    public function test_exent_viene_preservato(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([
            ['name' => 'Mario', 'surname' => 'Rossi', 'sex' => 'M', 'exent' => '400'],
        ]);

        $this->assertSame('400', $rows[0]['exent']);
    }

    public function test_id_tecnico_attraversa_il_contratto_senza_essere_rimosso(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([
            ['id' => '17', 'name' => ' Mario ', 'surname' => ' Rossi ', 'sex' => ' M '],
        ]);

        $this->assertSame('17', $rows[0]['id']);
        $this->assertSame('Mario', $rows[0]['name']);
    }
}
