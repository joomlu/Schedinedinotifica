<?php

namespace Tests\Unit;

use App\Exceptions\ComponentiImportException;
use App\Services\ComponentiImportService;
use App\Support\Componenti\DatiComponenteNormalizzati;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ComponentiImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(function ($value) {
            $dataset = [
                ['id' => 1, 'nome' => 'Italia', 'cittadinanza' => 'Italiana', 'codice_iso2' => 'IT', 'is_italia' => true],
                ['id' => 2, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false],
                ['id' => 3, 'nome' => 'Stati Uniti', 'cittadinanza' => 'Statunitense', 'codice_iso2' => 'US', 'is_italia' => false],
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

            $needle = strtoupper(trim((string) $value));
            foreach ($dataset as $row) {
                if ($needle === strtoupper($row['nome'])
                    || $needle === strtoupper((string) $row['cittadinanza'])
                    || $needle === strtoupper((string) $row['codice_iso2'])) {
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

    public function test_headers_template_corrette_e_senza_colonne_tech(): void
    {
        $service = new ComponentiImportService();

        $headers = $service->headersTemplate();

        $this->assertSame([
            'Nome',
            'Cognome',
            'Sesso',
            'Cittadinanza',
            'Nazione nascita',
            'Data di nascita',
            'Provincia nascita',
            'Citta nascita',
            'Regione nascita',
            'CAP nascita',
            'Nazione residenza',
            'Regione residenza',
            'Provincia residenza',
            'Citta residenza',
            'Tipo via',
            'Strada',
            'Num',
            'CAP residenza',
        ], array_values($headers));

        $this->assertArrayNotHasKey('relationship', $headers);
        $this->assertArrayNotHasKey('exent', $headers);
        $this->assertNotContains('Tipo alloggiato', $headers);
        $this->assertNotContains('Esente', $headers);
        $this->assertNotContains('schedina_id', $headers);
        $this->assertNotContains('componenti_id', $headers);
        $this->assertNotContains('codice Questura', array_map('strtolower', $headers));
    }

    public function test_mapping_header_to_field_e_definitivo(): void
    {
        $service = new ComponentiImportService();
        $mapping = array_flip($service->mappingIntestazioni());

        $this->assertSame('name', $mapping['Nome']);
        $this->assertSame('surname', $mapping['Cognome']);
        $this->assertSame('country_nac', $mapping['Nazione nascita']);
        $this->assertSame('date_nac', $mapping['Data di nascita']);
        $this->assertSame('city', $mapping['Citta residenza']);
    }

    public function test_ordine_colonne_differente_viene_gestito(): void
    {
        $service = new ComponentiImportService();
        $shuffled = [
            'Cognome', 'Nome', 'Sesso', 'Cittadinanza', 'Nazione nascita', 'Data di nascita', 'Provincia nascita',
            'Citta nascita', 'Regione nascita', 'CAP nascita', 'Nazione residenza', 'Regione residenza',
            'Provincia residenza', 'Citta residenza', 'Tipo via', 'Strada', 'Num', 'CAP residenza',
        ];

        $csv = $this->buildDelimitedFile($shuffled, [[
            'Rossi', 'Mario', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertCount(1, $preview['rows']);
        $this->assertSame('Mario', $preview['rows'][0]['name']);
        $this->assertSame('Rossi', $preview['rows'][0]['surname']);
    }

    public function test_csv_con_colonna_extra_diventa_errore_di_riga(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921', 'EXTRA',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertStringContainsString('attese 18, ricevute 19', $preview['rows'][0]['errors'][0]['message']);
        $this->assertSame('', $preview['rows'][0]['name']);
        $this->assertSame([], $preview['rows'][0]['data']);
    }

    public function test_csv_con_colonna_mancante_diventa_errore_di_riga(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertStringContainsString('attese 18, ricevute 17', $preview['rows'][0]['errors'][0]['message']);
        $this->assertSame('', $preview['rows'][0]['surname']);
    }

    public function test_txt_con_colonna_extra_diventa_errore_di_riga(): void
    {
        $service = new ComponentiImportService();
        $txt = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921', 'EXTRA',
        ]], "\t");

        $preview = $service->previewDaContenuto($txt, 'txt', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertStringContainsString('attese 18, ricevute 19', $preview['rows'][0]['errors'][0]['message']);
    }

    public function test_txt_con_colonna_mancante_diventa_errore_di_riga(): void
    {
        $service = new ComponentiImportService();
        $txt = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10',
        ]], "\t");

        $preview = $service->previewDaContenuto($txt, 'txt', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertStringContainsString('attese 18, ricevute 17', $preview['rows'][0]['errors'][0]['message']);
    }

    public function test_riga_malformata_tra_righe_valide_non_contamina_le_successive(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [
            [
                'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ],
            [
                'Giulia', 'Neri', 'F', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921', 'EXTRA',
            ],
            [
                'Anna', 'Verdi', 'F', 'Statunitense', 'Stati Uniti', '02/10/1980', '', '', '', '', '', '', '', '', '', '', '', '',
            ],
        ]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('VALIDO', $preview['rows'][0]['status']);
        $this->assertSame('ERRORE', $preview['rows'][1]['status']);
        $this->assertSame('VALIDO', $preview['rows'][2]['status']);
        $this->assertSame(2, $preview['rows'][0]['row_number']);
        $this->assertSame(3, $preview['rows'][1]['row_number']);
        $this->assertSame(4, $preview['rows'][2]['row_number']);
        $this->assertSame('Anna', $preview['rows'][2]['name']);
    }

    public function test_header_mancante_genera_errore(): void
    {
        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('mancante');

        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile([
            'Nome', 'Cognome', 'Sesso', 'Cittadinanza', 'Nazione nascita', 'Data di nascita', 'Provincia nascita',
            'Citta nascita', 'Regione nascita', 'CAP nascita', 'Nazione residenza', 'Regione residenza',
            'Provincia residenza', 'Citta residenza', 'Tipo via', 'Strada', 'Num',
        ], []);

        $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_header_duplicato_genera_errore(): void
    {
        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('duplicato');

        $service = new ComponentiImportService();
        $headers = $service->headersTemplate();
        $headers[1] = 'Nome';

        $csv = $this->buildDelimitedFile($headers, []);

        $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_header_sconosciuto_genera_errore(): void
    {
        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('sconosciuta');

        $service = new ComponentiImportService();
        $headers = $service->headersTemplate();
        $headers[] = 'Colonna fantasma';

        $csv = $this->buildDelimitedFile($headers, []);

        $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_riga_vuota_viene_ignorata(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [
            array_fill(0, 18, ''),
            [
                'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ],
        ]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertCount(1, $preview['rows']);
    }

    public function test_trim_valori_e_utf8_vengono_preservati(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            '  José  ', '  Núñez ', ' M ', ' Italiana ', ' Italia ', ' 02/10/1980 ', ' RN ', ' Rimini ', ' Emilia-Romagna ', ' 47921 ',
            ' Italia ', ' Emilia-Romagna ', ' RN ', ' Rimini ', ' Via ', ' Via Roma ', ' 10 ', ' 47921 ',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('José', $preview['rows'][0]['name']);
        $this->assertSame('Núñez', $preview['rows'][0]['surname']);
        $this->assertSame('M', $preview['rows'][0]['sex']);
    }

    public function test_csv_quoted_field_viene_parsato_correttamente(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma; centro', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('Via Roma; centro', $preview['rows'][0]['data']['address']);
    }

    public function test_data_giorno_mese_anno_valida_viene_normalizzata(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('1980-10-02', $preview['rows'][0]['data']['date_nac']);
        $this->assertSame('02/10/1980', $preview['rows'][0]['date_nac']);
        $this->assertSame('VALIDO', $preview['rows'][0]['status']);
    }

    public function test_data_invalida_genera_errore(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '12-31-1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertNotEmpty(array_filter($preview['rows'][0]['errors'], fn ($error) => $error['field'] === 'date_nac'));
    }

    public function test_data_impossibile_genera_errore(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '31/02/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertNotEmpty(array_filter($preview['rows'][0]['errors'], fn ($error) => $error['field'] === 'date_nac'));
    }

    public function test_italia_valida_e_estero_valido(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [
            [
                'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ],
            [
                'John', 'Doe', 'M', 'Statunitense', 'Stati Uniti', '02/10/1980', '', '', '', '', '', '', '', '', '', '', '', '',
            ],
        ]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('VALIDO', $preview['rows'][0]['status']);
        $this->assertSame('VALIDO', $preview['rows'][1]['status']);
    }

    public function test_italia_senza_provincia_o_comune_restituisce_errore(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', '', '', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertNotEmpty(array_filter($preview['rows'][0]['errors'], fn ($error) => $error['field'] === 'province_nac'));
    }

    public function test_nazione_sconosciuta_genera_errore(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Atlantide', '02/10/1980', '', '', '', '', '', '', '', '', '', '', '', '',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertNotEmpty(array_filter($preview['rows'][0]['errors'], fn ($error) => $error['field'] === 'country_nac'));
    }

    public function test_default_tipo_alloggiato_e_default_esente_vengono_applicati(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame('MEMBRO GRUPPO', $preview['rows'][0]['relationship_label']);
        $this->assertSame('MEMBRO GRUPPO', $preview['rows'][0]['relationship']);
        $this->assertSame('NO', $preview['rows'][0]['exent']);
    }

    public function test_mancanza_codice_20_fa_fallire_il_default(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('codice 20');

        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $service->previewDaContenuto($csv, 'csv', fn () => [
            ['codice' => '16', 'descrizione' => 'OSPITE SINGOLO'],
            ['codice' => '17', 'descrizione' => 'CAPO FAMIGLIA'],
            ['codice' => '18', 'descrizione' => 'CAPO GRUPPO'],
            ['codice' => '19', 'descrizione' => 'FAMILIARE'],
        ]);
    }

    public function test_piu_righe_valide_e_mix_valide_invalide(): void
    {
        $service = new ComponentiImportService();
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [
            [
                'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ],
            [
                'Giulia', 'Neri', 'F', 'Italiana', 'Italia', '31/02/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ],
        ]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        $this->assertCount(2, $preview['rows']);
        $this->assertSame('VALIDO', $preview['rows'][0]['status']);
        $this->assertSame('ERRORE', $preview['rows'][1]['status']);
        $this->assertSame(2, $preview['rows'][0]['row_number']);
        $this->assertSame(3, $preview['rows'][1]['row_number']);
    }

    public function test_limite_massimo_righe_viene_applicato(): void
    {
        $service = new ComponentiImportService();
        $headers = $service->headersTemplate();
        $rows = [];
        for ($i = 0; $i < ComponentiImportService::MAX_RIGHE + 1; $i++) {
            $rows[] = [
                'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
                'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
            ];
        }

        $csv = $this->buildDelimitedFile($headers, $rows);

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('Numero massimo righe superato');

        $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_formato_file_non_consentito_genera_errore(): void
    {
        $this->expectException(ComponentiImportException::class);

        $service = new ComponentiImportService();
        $service->previewDaContenuto('abc', 'xls', fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_txt_produce_lo_stesso_contratto_del_csv(): void
    {
        $service = new ComponentiImportService();
        $rows = [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]];

        $csv = $this->buildDelimitedFile($service->headersTemplate(), $rows, ';');
        $txt = $this->buildDelimitedFile($service->headersTemplate(), $rows, "\t");

        $csvPreview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());
        $txtPreview = $service->previewDaContenuto($txt, 'txt', fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame($csvPreview['rows'][0]['data'], $txtPreview['rows'][0]['data']);
    }

    public function test_nessun_id_interno_e_nessuna_colonna_tecnica_nel_modello(): void
    {
        $service = new ComponentiImportService();
        $headers = $service->headersTemplate();

        $this->assertNotContains('id', array_map('strtolower', $headers));
        $this->assertNotContains('schedina_id', array_map('strtolower', $headers));
        $this->assertNotContains('struttura_id', array_map('strtolower', $headers));
        $this->assertNotContains('customer_id', array_map('strtolower', $headers));
        $this->assertNotContains('questura_exported_at', array_map('strtolower', $headers));
        $this->assertNotContains('istat_exported_at', array_map('strtolower', $headers));
    }

    public function test_prepara_conferma_batch_pending_puo_procedere(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);

        $result = $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->tipoAlloggiatoFixture());

        $this->assertSame(1, $result['valid_count']);
        $this->assertSame(0, $result['invalid_count']);
        $this->assertCount(1, $result['payloads']);
        $this->assertSame(77, $result['payloads'][0]['schedina_id']);
        $this->assertSame(10, $result['payloads'][0]['struttura_id']);
        $this->assertSame('NO', $result['payloads'][0]['exent']);
    }

    public function test_prepara_conferma_batch_confirmed_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);
        $batch['status'] = 'confirmed';
        $batch['confirmed_at'] = time();

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('pending');

        $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_scaduto_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);
        $batch['expires_at'] = time() - 10;

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('scaduto');

        $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_user_diverso_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('utente');

        $service->preparaConfermaBatch($batch, 77, 10, 999, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_struttura_diversa_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('struttura');

        $service->preparaConfermaBatch($batch, 77, 999, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_schedina_diversa_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('schedina');

        $service->preparaConfermaBatch($batch, 999, 10, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_senza_token_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);
        $batch['token'] = '';

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('Batch non valido');

        $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_con_payload_mancante_viene_rifiutato(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);
        unset($batch['raw_rows']);

        $this->expectException(ComponentiImportException::class);
        $this->expectExceptionMessage('Payload batch non valido');

        $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->tipoAlloggiatoFixture());
    }

    public function test_prepara_conferma_batch_con_codice_20_mancante_fallisce(): void
    {
        $service = new ComponentiImportService();
        $batch = $this->buildValidBatch($service);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('codice 20');

        $service->preparaConfermaBatch($batch, 77, 10, 501, fn () => [
            ['codice' => '16', 'descrizione' => 'OSPITE SINGOLO'],
            ['codice' => '17', 'descrizione' => 'CAPO FAMIGLIA'],
            ['codice' => '18', 'descrizione' => 'CAPO GRUPPO'],
            ['codice' => '19', 'descrizione' => 'FAMILIARE'],
        ]);
    }

    private function tipoAlloggiatoFixture(): array
    {
        return [
            ['codice' => '16', 'descrizione' => 'OSPITE SINGOLO'],
            ['codice' => '17', 'descrizione' => 'CAPO FAMIGLIA'],
            ['codice' => '18', 'descrizione' => 'CAPO GRUPPO'],
            ['codice' => '19', 'descrizione' => 'FAMILIARE'],
            ['codice' => '20', 'descrizione' => 'MEMBRO GRUPPO'],
        ];
    }

    private function buildValidBatch(ComponentiImportService $service): array
    {
        $csv = $this->buildDelimitedFile($service->headersTemplate(), [[
            'Mario', 'Rossi', 'M', 'Italiana', 'Italia', '02/10/1980', 'RN', 'Rimini', 'Emilia-Romagna', '47921',
            'Italia', 'Emilia-Romagna', 'RN', 'Rimini', 'Via', 'Via Roma', '10', '47921',
        ]]);

        $preview = $service->previewDaContenuto($csv, 'csv', fn () => $this->tipoAlloggiatoFixture());

        return [
            'token' => 'batch-test-001',
            'user_id' => 501,
            'struttura_id' => 10,
            'schedina_id' => 77,
            'formato' => 'csv',
            'raw_headers' => $preview['raw_headers'],
            'raw_rows' => $preview['raw_rows'],
            'created_at' => time(),
            'expires_at' => time() + 3600,
            'confirmed_at' => null,
            'status' => 'pending',
        ];
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    private function buildDelimitedFile(array $headers, array $rows, string $delimiter = ';'): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers, $delimiter);
        foreach ($rows as $row) {
            fputcsv($stream, $row, $delimiter);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return (string) $content;
    }
}