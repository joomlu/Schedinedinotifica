<?php

namespace Tests\Unit;

use App\Support\Componenti\DatiComponenteNormalizzati;
use PHPUnit\Framework\TestCase;

class DatiComponenteNormalizzatiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(function ($value) {
            $dataset = [
                ['id' => 1, 'nome' => 'Italia', 'cittadinanza' => 'Italiana', 'codice_iso2' => 'IT', 'is_italia' => true],
                ['id' => 2, 'nome' => 'Stati Uniti', 'cittadinanza' => 'Statunitense', 'codice_iso2' => 'US', 'is_italia' => false],
                ['id' => 3, 'nome' => 'Polonia', 'cittadinanza' => 'Polacca', 'codice_iso2' => 'PL', 'is_italia' => false],
            ];

            if ($value === null || $value === '') {
                return null;
            }

            if (is_numeric($value)) {
                foreach ($dataset as $row) {
                    if ((int) $row['id'] === (int) $value) {
                        return $row;
                    }
                }

                return null;
            }

            $normalized = strtoupper(trim((string) $value));
            foreach ($dataset as $row) {
                if ($normalized === strtoupper($row['nome'])
                    || $normalized === strtoupper((string) $row['cittadinanza'])
                    || $normalized === strtoupper((string) $row['codice_iso2'])) {
                    return $row;
                }
            }

            return null;
        });
    }

    protected function tearDown(): void
    {
        DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(null);

        parent::tearDown();
    }

    public function test_componente_italia_completo_e_valido(): void
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

    public function test_componente_estero_senza_provincia_e_comune_nascita_e_valido(): void
    {
        $rows = [
            [
                'name' => 'John',
                'surname' => 'Doe',
                'sex' => 'M',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Statunitense',
                'country_nac' => 'Stati Uniti',
                'date_nac' => '2001-10-02',
            ],
        ];

        $normalizzate = DatiComponenteNormalizzati::normalizzaRighe($rows);

        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($normalizzate));
    }

    public function test_country_nac_codice_iso2_it_viene_risolto_a_italia(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'IT',
            'province_nac' => 'RN',
            'comune_nac' => 'Rimini',
            'date_nac' => '2001-10-02',
        ]]);

        $this->assertSame('Italia', $rows[0]['country_nac']);
        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($rows));
    }

    public function test_country_nac_id_geo_italia_viene_risolto_a_italia(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => '1',
            'province_nac' => 'RN',
            'comune_nac' => 'Rimini',
            'date_nac' => '2001-10-02',
        ]]);

        $this->assertSame('Italia', $rows[0]['country_nac']);
        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($rows));
    }

    public function test_country_nac_id_geo_estero_viene_risolto_a_estero(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'John',
            'surname' => 'Doe',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Statunitense',
            'country_nac' => '2',
            'date_nac' => '2001-10-02',
        ]]);

        $this->assertSame('Stati Uniti', $rows[0]['country_nac']);
        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($rows));
    }

    public function test_country_nac_id_geo_inesistente_genera_errore(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => '999',
            'date_nac' => '2001-10-02',
        ]]);

        $errori = DatiComponenteNormalizzati::validaRighe($rows);

        $this->assertArrayHasKey('componenti.0.country_nac', $errori);
    }

    public function test_country_nac_stringa_estera_valida_viene_risolta(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Anna',
            'surname' => 'Novak',
            'sex' => 'F',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Polacca',
            'country_nac' => 'PL',
            'date_nac' => '1999-01-05',
        ]]);

        $this->assertSame('Polonia', $rows[0]['country_nac']);
        $this->assertSame([], DatiComponenteNormalizzati::validaRighe($rows));
    }

    public function test_country_nac_sconosciuto_genera_errore_e_non_viene_trattato_come_estero(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italy',
            'date_nac' => '2001-10-02',
        ]]);

        $errori = DatiComponenteNormalizzati::validaRighe($rows);

        $this->assertArrayHasKey('componenti.0.country_nac', $errori);
        $this->assertArrayNotHasKey('componenti.0.province_nac', $errori);
        $this->assertArrayNotHasKey('componenti.0.comune_nac', $errori);
    }

    public function test_country_nac_cittadinanza_italiana_non_permette_bypass_dei_campi_italia(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italiana',
            'date_nac' => '2001-10-02',
        ]]);

        $this->assertSame('Italia', $rows[0]['country_nac']);

        $errori = DatiComponenteNormalizzati::validaRighe($rows);

        $this->assertArrayHasKey('componenti.0.province_nac', $errori);
        $this->assertArrayHasKey('componenti.0.comune_nac', $errori);
    }

    public function test_componente_estero_senza_residenza_e_valido(): void
    {
        $rows = [
            [
                'name' => 'Anna',
                'surname' => 'Novak',
                'sex' => 'F',
                'relationship' => 'MEMBRO GRUPPO',
                'exent' => 'NO',
                'city_nac' => 'Polacca',
                'country_nac' => 'Polonia',
                'date_nac' => '1999-01-05',
            ],
        ];

        $this->assertSame([], DatiComponenteNormalizzati::validaRighe(
            DatiComponenteNormalizzati::normalizzaRighe($rows)
        ));
    }

    public function test_componente_italia_senza_residenza_e_valido(): void
    {
        $rows = [
            [
                'name' => 'Lucia',
                'surname' => 'Bianchi',
                'sex' => 'F',
                'relationship' => 'FAMILIARE',
                'exent' => 'NO',
                'city_nac' => 'Italiana',
                'country_nac' => 'Italia',
                'regione_nac' => 'Lazio',
                'province_nac' => 'RM',
                'comune_nac' => 'Roma',
                'date_nac' => '2000-03-12',
            ],
        ];

        $this->assertSame([], DatiComponenteNormalizzati::validaRighe(
            DatiComponenteNormalizzati::normalizzaRighe($rows)
        ));
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

    public function test_nato_in_italia_senza_provincia_nascita_e_invalido(): void
    {
        $rows = [[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italia',
            'comune_nac' => 'Rimini',
            'date_nac' => '2001-10-02',
        ]];

        $errori = DatiComponenteNormalizzati::validaRighe(DatiComponenteNormalizzati::normalizzaRighe($rows));

        $this->assertArrayHasKey('componenti.0.province_nac', $errori);
    }

    public function test_nato_in_italia_senza_comune_nascita_e_invalido(): void
    {
        $rows = [[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italia',
            'province_nac' => 'RN',
            'date_nac' => '2001-10-02',
        ]];

        $errori = DatiComponenteNormalizzati::validaRighe(DatiComponenteNormalizzati::normalizzaRighe($rows));

        $this->assertArrayHasKey('componenti.0.comune_nac', $errori);
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

    public function test_data_nascita_con_anno_due_cifre_usa_secolo_coerente(): void
    {
        $this->assertSame('1986-09-12', DatiComponenteNormalizzati::normalizzaData('12/09/86'));

        $now = new \DateTimeImmutable('now');
        $currentYear = (int) $now->format('Y');
        $futureShort = sprintf('%02d', ($currentYear + 1) % 100);
        $pastShort = sprintf('%02d', ($currentYear - 1) % 100);

        $this->assertSame(sprintf('%04d-01-02', 1900 + (int) $futureShort), DatiComponenteNormalizzati::normalizzaData(sprintf('02/01/%s', $futureShort)));
        $this->assertSame(sprintf('%04d-01-02', 2000 + (int) $pastShort), DatiComponenteNormalizzati::normalizzaData(sprintf('02/01/%s', $pastShort)));
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

    public function test_exent_no_e_valido(): void
    {
        $rows = [[
            'name' => 'Giulia',
            'surname' => 'Neri',
            'sex' => 'F',
            'relationship' => 'FAMILIARE',
            'exent' => 'NO',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italia',
            'province_nac' => 'BO',
            'comune_nac' => 'Bologna',
            'date_nac' => '2002-05-10',
        ]];

        $this->assertSame([], DatiComponenteNormalizzati::validaRighe(
            DatiComponenteNormalizzati::normalizzaRighe($rows)
        ));
    }

    public function test_dati_opzionali_presenti_vengono_preservati(): void
    {
        $rows = DatiComponenteNormalizzati::normalizzaRighe([[
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'relationship' => 'FAMILIARE',
            'exent' => '400',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italia',
            'regione_nac' => 'Emilia-Romagna',
            'province_nac' => 'RN',
            'comune_nac' => 'Rimini',
            'cap_nac' => '47921',
            'date_nac' => '2001-10-02',
            'country' => 'Italia',
            'regione' => 'Emilia-Romagna',
            'province' => 'RN',
            'city' => 'Rimini',
            'typeaway' => 'Via',
            'address' => 'Roma',
            'number' => '10',
            'cap' => '47921',
        ]]);

        $this->assertSame('Emilia-Romagna', $rows[0]['regione_nac']);
        $this->assertSame('47921', $rows[0]['cap_nac']);
        $this->assertSame('Italia', $rows[0]['country']);
        $this->assertSame('Via', $rows[0]['typeaway']);
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
