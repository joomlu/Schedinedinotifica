<?php

namespace App\Services;

use App\Exceptions\ComponentiImportException;
use App\Support\Componenti\ContrattoImportazioneComponentiV1;
use App\Support\Componenti\DatiComponenteNormalizzati;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Options as XlsxReaderOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Throwable;

class ComponentiImportService
{
    public const FORMATO_CSV = 'csv';
    public const FORMATO_TXT = 'txt';
    public const FORMATO_XLSX = 'xlsx';

    public const DELIMITATORE_CSV = ';';
    public const DELIMITATORE_TXT = "\t";

    public const MAX_BYTES = 5242880;
    public const MAX_RIGHE = 1000;
    public const BATCH_TTL_MINUTES = 30;
    private const WORKSHEET_MODELLO_COMPONENTI = 'Componenti';

    public function headersTemplate(): array
    {
        return ContrattoImportazioneComponentiV1::headersTemplate();
    }

    public function colonneTemplate(): array
    {
        return ContrattoImportazioneComponentiV1::colonneTemplate();
    }

    public function mappingIntestazioni(): array
    {
        return $this->colonneTemplate();
    }

    public function formatiSupportati(): array
    {
        return [self::FORMATO_CSV, self::FORMATO_TXT, self::FORMATO_XLSX];
    }

    public function delimitatorePerFormato(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => self::DELIMITATORE_CSV,
            self::FORMATO_TXT => self::DELIMITATORE_TXT,
            self::FORMATO_XLSX => '',
        };
    }

    public function nomeFileTemplate(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => 'modello_componenti.csv',
            self::FORMATO_TXT => 'modello_componenti.txt',
            self::FORMATO_XLSX => 'modello_componenti.xlsx',
        };
    }

    public function contentTypePerFormato(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => 'text/csv; charset=UTF-8',
            self::FORMATO_TXT => 'text/plain; charset=UTF-8',
            self::FORMATO_XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }

    public function contenutoTemplateVuoto(string $formato): string
    {
        $formato = $this->normalizzaFormato($formato);

        if ($formato === self::FORMATO_XLSX) {
            return $this->contenutoTemplateVuotoXlsx();
        }

        $delimiter = $this->delimitatorePerFormato($formato);

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new ComponentiImportException('Impossibile generare il template di importazione.');
        }

        if ($formato === self::FORMATO_CSV) {
            fwrite($stream, "\xEF\xBB\xBF");
        }

        fputcsv($stream, $this->headersTemplate(), $delimiter);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return (string) $content;
    }

    private function contenutoTemplateVuotoXlsx(): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'componenti_tpl_xlsx_');
        if ($tmpFile === false) {
            throw new ComponentiImportException('Impossibile generare il template di importazione.');
        }

        $writer = new XlsxWriter();

        try {
            $writer->openToFile($tmpFile);
            $writer->getCurrentSheet()->setName(self::WORKSHEET_MODELLO_COMPONENTI);
            $writer->addRow(Row::fromValues($this->headersTemplate()));
            $writer->close();

            $content = file_get_contents($tmpFile);
            if ($content === false) {
                throw new ComponentiImportException('Impossibile generare il template di importazione.');
            }

            return $content;
        } catch (Throwable $exception) {
            if ($exception instanceof ComponentiImportException) {
                throw $exception;
            }

            throw new ComponentiImportException('Impossibile generare il template di importazione.');
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * @return array{formato:string,delimitatore:string,headers:array<int, string>,rows:array<int, array<string, mixed>>,totale_righe:int,righe_valide:int,righe_in_errore:int,metadata:array<string, mixed>}
     */
    public function previewDaContenuto(string $contenuto, string $formato, ?callable $tipoAlloggiatoResolver = null): array
    {
        $formato = $this->normalizzaFormato($formato);
        $delimitatoreRilevato = null;

        if ($formato === self::FORMATO_XLSX) {
            [$intestazioni, $righe] = $this->parsaFileXlsxDaContenuto($contenuto);
        } else {
            $delimitatoreRilevato = $this->rilevaDelimitatore($contenuto);
            [$intestazioni, $righe] = $this->parsaFileDelimitato($contenuto, $delimitatoreRilevato);
        }

        return $this->analizzaImportazione(
            $intestazioni,
            $righe,
            $formato,
            $tipoAlloggiatoResolver,
            $delimitatoreRilevato
        );
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string|null>>}
     */
    private function parsaFileXlsxDaContenuto(string $contenuto): array
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'componenti_import_xlsx_');
        if ($tmpFile === false) {
            throw new ComponentiImportException('Impossibile leggere il file XLSX.');
        }

        try {
            if (file_put_contents($tmpFile, $contenuto) === false) {
                throw new ComponentiImportException('Impossibile leggere il file XLSX.');
            }

            return $this->parsaFileXlsxDaPercorso($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string|null>>}
     */
    private function parsaFileXlsxDaPercorso(string $filePath): array
    {
        $options = new XlsxReaderOptions();
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $options->SHOULD_FORMAT_DATES = false;

        $reader = new XlsxReader($options);

        try {
            $reader->open($filePath);

            $sheetCount = 0;
            $headers = null;
            $rows = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetCount++;
                if ($sheetCount > 1) {
                    throw new ComponentiImportException('Il file XLSX deve contenere un solo foglio di lavoro.');
                }

                $excelRowNumber = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $excelRowNumber++;
                    $values = $this->valoriRigaXlsx($row->getCells(), $excelRowNumber);

                    if ($headers === null) {
                        $headers = $values;
                        continue;
                    }

                    $rows[] = $values;
                }
            }

            if ($sheetCount === 0) {
                throw new ComponentiImportException('Il file XLSX non contiene fogli di lavoro validi.');
            }

            if (!is_array($headers) || $headers === []) {
                throw new ComponentiImportException('Header mancante o file vuoto.');
            }

            return [$headers, $rows];
        } catch (ComponentiImportException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ComponentiImportException('File XLSX non valido o corrotto.');
        } finally {
            try {
                $reader->close();
            } catch (Throwable) {
            }
        }
    }

    /**
     * @param array<int, Cell> $cells
     * @return array<int, string|null>
     */
    private function valoriRigaXlsx(array $cells, int $excelRowNumber): array
    {
        $values = [];

        foreach ($cells as $index => $cell) {
            if ($cell instanceof FormulaCell) {
                $colonna = $index + 1;
                throw new ComponentiImportException('Il file XLSX contiene formule non supportate (riga ' . $excelRowNumber . ', colonna ' . $colonna . ').');
            }

            $values[] = $this->normalizzaValoreCellaXlsx($cell->getValue());
        }

        return $values;
    }

    private function normalizzaValoreCellaXlsx(bool|DateInterval|DateTimeInterface|float|int|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if ($value instanceof DateInterval) {
            return $value->format('%r%a');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return '';
            }

            $formatted = rtrim(rtrim(sprintf('%.15F', $value), '0'), '.');

            return $formatted === '-0' ? '0' : $formatted;
        }

        return (string) $value;
    }

    /**
     * @param array<int, string> $intestazioni
     * @param array<int, array<int, string|null>> $righe
     * @return array{formato:string,delimitatore:string,headers:array<int, string>,raw_headers:array<int, string>,raw_rows:array<int, array<int, string|null>>,rows:array<int, array<string, mixed>>,totale_righe:int,righe_valide:int,righe_in_errore:int,metadata:array<string, mixed>}
     */
    public function analizzaImportazione(array $intestazioni, array $righe, string $formato, ?callable $tipoAlloggiatoResolver = null, ?string $delimitatore = null): array
    {
        $formato = $this->normalizzaFormato($formato);
        $delimitatore = $delimitatore ?? $this->delimitatorePerFormato($formato);

        $mappaCampi = $this->validaEMappeIntestazioni($intestazioni);
        $righePreview = [];

        $defaultInfo = ContrattoImportazioneComponentiV1::metadataDefaultImport($tipoAlloggiatoResolver);

        foreach ($righe as $linea => $valori) {
            $numeroRiga = $linea + 2;
            if ($numeroRiga > self::MAX_RIGHE + 1) {
                throw new ComponentiImportException(
                    'Numero massimo righe superato.',
                    ['Il file non può superare ' . self::MAX_RIGHE . ' righe di dati.']
                );
            }

            if ($this->rigaVuota($valori)) {
                continue;
            }

            if (count($valori) !== count($intestazioni)) {
                $righePreview[] = [
                    'row_number' => $numeroRiga,
                    'status' => 'ERRORE',
                    'name' => '',
                    'surname' => '',
                    'sex' => '',
                    'date_nac' => '',
                    'country_nac' => '',
                    'relationship' => '',
                    'relationship_label' => (string) ($defaultInfo['relationship_descrizione'] ?? ''),
                    'exent' => '',
                    'errors' => [[
                        'field' => '_struct',
                        'label' => 'Struttura file',
                        'message' => 'Numero colonne non valido: attese ' . count($intestazioni) . ', ricevute ' . count($valori) . '.',
                    ]],
                    'data' => [],
                ];

                continue;
            }

            $assoc = $this->mappaRigaSuCampi($mappaCampi, $valori);
            $assoc = $this->normalizzaValoriRiga($assoc);
            $assoc = ContrattoImportazioneComponentiV1::applicaDefaultImport($assoc, $tipoAlloggiatoResolver);

            $errori = [];
            if (($assoc['date_nac'] ?? '') !== '') {
                $dataNormalizzata = $this->normalizzaDataGiornoMeseAnno((string) $assoc['date_nac']);
                if ($dataNormalizzata === null) {
                    $errori[] = [
                        'field' => 'date_nac',
                        'label' => 'Data di nascita',
                        'message' => 'Formato data non valido: usa GG/MM/AAAA.',
                    ];
                } else {
                    $assoc['date_nac'] = $dataNormalizzata;
                }
            }

            $normalizzato = DatiComponenteNormalizzati::daArray($assoc);
            $validationErrors = $normalizzato->erroriValidazione($numeroRiga - 1, true);
            $review = DatiComponenteNormalizzati::classificaRiga($assoc);

            foreach ($validationErrors as $key => $message) {
                $campo = $this->campoDaChiaveErrore((string) $key);
                $errori[] = [
                    'field' => $campo,
                    'label' => $this->etichettaDaCampo($campo),
                    'message' => $message,
                ];
            }

            $status = $review['status'];
            if ($status === DatiComponenteNormalizzati::STATO_COMPLETO && !empty($validationErrors)) {
                $status = DatiComponenteNormalizzati::STATO_DA_COMPLETARE;
            }
            if ($status === DatiComponenteNormalizzati::STATO_COMPLETO && empty($errori)) {
                $review['messaggio'] = 'Componente completo.';
            }
            if ($status !== DatiComponenteNormalizzati::STATO_COMPLETO && empty($errori) && !empty($review['messaggio'])) {
                $errori[] = [
                    'field' => $review['campo'] ?? '_review',
                    'label' => $review['campo'] ? $this->etichettaDaCampo($review['campo']) : 'Controllo',
                    'message' => $review['messaggio'],
                ];
            }

            $normalizzata = $normalizzato->toArray();
            $rowPayload = array_merge($normalizzata, $review['metadata'] ?? [], [
                '_review_status' => $status,
                '_review_message' => $review['messaggio'],
                '_review_field' => $review['campo'],
                '_review_proposed_value' => $review['proposta']['city_nac'] ?? null,
                '_review_requires_confirmation' => $status === DatiComponenteNormalizzati::STATO_DA_VERIFICARE,
            ]);

            $righePreview[] = [
                'row_number' => $numeroRiga,
                'status' => $status,
                'name' => (string) ($normalizzata['name'] ?? ''),
                'surname' => (string) ($normalizzata['surname'] ?? ''),
                'sex' => (string) ($normalizzata['sex'] ?? ''),
                'date_nac' => $this->formatoDataPreview($normalizzata['date_nac'] ?? null, (string) ($assoc['date_nac'] ?? '')),
                'country_nac' => (string) ($normalizzata['country_nac'] ?? ''),
                'relationship' => (string) ($normalizzata['relationship'] ?? ''),
                'relationship_label' => (string) ($defaultInfo['relationship_descrizione'] ?? ''),
                'exent' => (string) ($normalizzata['exent'] ?? ''),
                'errors' => $errori,
                'review_message' => $review['messaggio'],
                'review_field' => $review['campo'],
                'review_status' => $status,
                'review_proposed_value' => $review['proposta']['city_nac'] ?? null,
                'data' => $rowPayload,
            ];
        }

        $statusCounts = [
            DatiComponenteNormalizzati::STATO_COMPLETO => 0,
            DatiComponenteNormalizzati::STATO_DA_VERIFICARE => 0,
            DatiComponenteNormalizzati::STATO_DA_COMPLETARE => 0,
            DatiComponenteNormalizzati::STATO_NON_IMPORTABILE => 0,
        ];
        foreach ($righePreview as $row) {
            $statusCounts[$row['status']] = ($statusCounts[$row['status']] ?? 0) + 1;
        }

        return [
            'formato' => $formato,
            'delimitatore' => $delimitatore,
            'headers' => $this->headersTemplate(),
            'raw_headers' => array_values($intestazioni),
            'raw_rows' => array_values($righe),
            'rows' => $righePreview,
            'totale_righe' => count($righePreview),
            'righe_valide' => $statusCounts[DatiComponenteNormalizzati::STATO_COMPLETO] + $statusCounts[DatiComponenteNormalizzati::STATO_DA_VERIFICARE],
            'righe_in_errore' => $statusCounts[DatiComponenteNormalizzati::STATO_DA_COMPLETARE] + $statusCounts[DatiComponenteNormalizzati::STATO_NON_IMPORTABILE],
            'stati' => $statusCounts,
            'metadata' => [
                'default_relationship_codice' => $defaultInfo['relationship_codice'] ?? null,
                'default_relationship_descrizione' => $defaultInfo['relationship_descrizione'] ?? null,
                'default_exent' => $defaultInfo['exent'] ?? null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $batch
     * @return array{valid_rows: array<int, array<string, mixed>>, invalid_rows: array<int, array<string, mixed>>, payloads: array<int, array<string, mixed>>, valid_count:int, invalid_count:int}
     */
    public function preparaConfermaBatch(array $batch, int $schedinaId, int $strutturaId, int $userId, ?callable $tipoAlloggiatoResolver = null): array
    {
        $this->assertBatchCanBeConfirmed($batch, $schedinaId, $strutturaId, $userId);

        $analisi = $this->analizzaImportazione(
            (array) ($batch['raw_headers'] ?? []),
            (array) ($batch['raw_rows'] ?? []),
            (string) ($batch['formato'] ?? self::FORMATO_CSV),
            $tipoAlloggiatoResolver
        );

        $validRows = array_values(array_filter(
            $analisi['rows'],
            fn (array $row) => !in_array($row['status'] ?? '', [DatiComponenteNormalizzati::STATO_NON_IMPORTABILE], true)
        ));
        $invalidRows = array_values(array_filter(
            $analisi['rows'],
            fn (array $row) => in_array($row['status'] ?? '', [DatiComponenteNormalizzati::STATO_NON_IMPORTABILE], true)
        ));
        $payloads = array_map(function (array $row) use ($schedinaId, $strutturaId) {
            $payload = $this->buildPersistableComponentPayload($row['data'] ?? [], $schedinaId, $strutturaId);
            $payload['_review_status'] = $row['status'] ?? DatiComponenteNormalizzati::STATO_COMPLETO;
            $payload['_review_message'] = $row['review_message'] ?? null;
            $payload['_review_field'] = $row['review_field'] ?? null;
            $payload['_review_proposed_value'] = $row['review_proposed_value'] ?? null;
            $payload['_review_requires_confirmation'] = $payload['_review_status'] === DatiComponenteNormalizzati::STATO_DA_VERIFICARE;
            $payload['_review_confirmed'] = $payload['_review_status'] === DatiComponenteNormalizzati::STATO_COMPLETO;
            return $payload;
        }, $validRows);

        return [
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'payloads' => $payloads,
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
        ];
    }

    private function normalizzaFormato(string $formato): string
    {
        $formato = strtolower(trim($formato));
        if (!in_array($formato, $this->formatiSupportati(), true)) {
            throw new ComponentiImportException('Formato file non supportato.');
        }

        return $formato;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string|null>>}
     */
    private function parsaFileDelimitato(string $contenuto, ?string $delimitatore = null): array
    {
        $delimitatore = $delimitatore ?? $this->rilevaDelimitatore($contenuto);
        $contenuto = $this->rimuoviBom($contenuto);
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new ComponentiImportException('Impossibile inizializzare il parser del file.');
        }

        fwrite($stream, $contenuto);
        rewind($stream);

        $intestazioni = fgetcsv($stream, 0, $delimitatore);
        if ($intestazioni === false) {
            fclose($stream);
            throw new ComponentiImportException('Header mancante o file vuoto.');
        }

        $righe = [];
        while (($riga = fgetcsv($stream, 0, $delimitatore)) !== false) {
            $righe[] = $riga;
        }

        fclose($stream);

        return [$intestazioni, $righe];
    }

    private function rilevaDelimitatore(string $contenuto): string
    {
        $candidati = [';', ',', "\t"];
        $righe = preg_split('/\r\n|\n|\r/', $this->rimuoviBom($contenuto));
        $header = null;

        foreach ($righe as $riga) {
            if (trim((string) $riga) === '') {
                continue;
            }
            $header = $riga;
            break;
        }

        if ($header === null || trim((string) $header) === '') {
            return self::DELIMITATORE_CSV;
        }

        $attese = [];
        foreach (ContrattoImportazioneComponentiV1::aliasIntestazioni() as $campo => $etichette) {
            foreach ($etichette as $etichetta) {
                $attese[$this->normalizzaIntestazione((string) $etichetta)] = $campo;
            }
        }

        $migliore = [
            'delimiter' => self::DELIMITATORE_CSV,
            'match_count' => 0,
            'column_count' => 0,
        ];

        foreach ($candidati as $delimiter) {
            $valori = str_getcsv($header, $delimiter);
            if ($valori === false || $valori === []) {
                continue;
            }

            $matchCount = 0;
            foreach ($valori as $valore) {
                $normale = $this->normalizzaIntestazione((string) $valore);
                if ($normale !== '' && isset($attese[$normale])) {
                    $matchCount++;
                }
            }

            $score = $matchCount * 100 + count($valori);
            $isBetter = $matchCount > $migliore['match_count']
                || ($matchCount === $migliore['match_count'] && count($valori) > $migliore['column_count']);

            if ($isBetter) {
                $migliore = [
                    'delimiter' => $delimiter,
                    'match_count' => $matchCount,
                    'column_count' => count($valori),
                    'score' => $score,
                ];
            }
        }

        if ($migliore['match_count'] === 0) {
            throw new ComponentiImportException(
                'Impossibile rilevare il delimitatore del file: header non riconosciuto.',
                ['Supportati: ;, ,, TAB.']
            );
        }

        return (string) $migliore['delimiter'];
    }

    /**
     * @param array<int, string|null> $intestazioni
     * @return array<int, string>
     */
    private function validaEMappeIntestazioni(array $intestazioni): array
    {
        $attese = [];
        $campiRichiesti = [];

        foreach (ContrattoImportazioneComponentiV1::aliasIntestazioni() as $campo => $etichette) {
            $campiRichiesti[$campo] = array_values(array_unique(array_map(
                fn (string $etichetta): string => $this->normalizzaIntestazione($etichetta),
                $etichette
            )));

            foreach ($etichette as $etichetta) {
                $attese[$this->normalizzaIntestazione((string) $etichetta)] = $campo;
            }
        }

        $mappa = [];
        $trovate = [];
        $errori = [];

        foreach ($intestazioni as $indice => $intestazione) {
            $normalizzata = $this->normalizzaIntestazione((string) $intestazione);
            if ($normalizzata === '') {
                $errori[] = 'Colonna vuota trovata nell header.';
                continue;
            }

            if (isset($trovate[$normalizzata])) {
                $errori[] = 'Header duplicato: ' . trim((string) $intestazione) . '.';
                continue;
            }

            if (!array_key_exists($normalizzata, $attese)) {
                $errori[] = 'Colonna sconosciuta: ' . trim((string) $intestazione) . '.';
                continue;
            }

            $campo = $attese[$normalizzata];
            $mappa[$indice] = $campo;
            $trovate[$normalizzata] = true;
        }

        foreach ($campiRichiesti as $campo => $normalizzate) {
            $trovato = false;
            foreach ($normalizzate as $token) {
                if (isset($trovate[$token])) {
                    $trovato = true;
                    break;
                }
            }

            if (!$trovato) {
                $errori[] = 'Colonna obbligatoria mancante: ' . $this->etichettaDaCampo($campo) . '.';
            }
        }

        if ($errori !== []) {
            throw new ComponentiImportException(
                implode(' ', $errori),
                $errori
            );
        }

        return $mappa;
    }

    /**
     * @param array<int, string|null> $valori
     * @return array<string, string>
     */
    private function mappaRigaSuCampi(array $mappaCampi, array $valori): array
    {
        $assoc = [];
        foreach ($mappaCampi as $indice => $campo) {
            $assoc[$campo] = isset($valori[$indice]) ? (string) $valori[$indice] : '';
        }

        return $assoc;
    }

    /**
     * @param array<string, string> $assoc
     * @return array<string, string>
     */
    private function normalizzaValoriRiga(array $assoc): array
    {
        foreach ($assoc as $campo => $valore) {
            $assoc[$campo] = trim($valore);
        }

        return $assoc;
    }

    /**
     * @param array<int, string|null> $valori
     */
    private function rigaVuota(array $valori): bool
    {
        foreach ($valori as $valore) {
            if (trim((string) $valore) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizzaIntestazione(string $value): string
    {
        $value = trim($this->rimuoviBom($value));
        $value = mb_strtolower($value, 'UTF-8');
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private function rimuoviBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function normalizzaDataGiornoMeseAnno(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $data = DateTimeImmutable::createFromFormat('!d/m/Y', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$data || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return null;
        }

        if ($data->format('d/m/Y') !== $value) {
            return null;
        }

        return $data->format('Y-m-d');
    }

    private function formatoDataPreview(?string $normalizzata, string $originale): string
    {
        if ($normalizzata === null || $normalizzata === '') {
            return $originale;
        }

        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $normalizzata);
        return $data ? $data->format('d/m/Y') : $originale;
    }

    private function etichettaDaCampo(?string $campo): string
    {
        if ($campo === null) {
            return 'Campo sconosciuto';
        }

        return $this->colonneTemplate()[$campo] ?? $campo;
    }

    private function campoDaChiaveErrore(string $chiave): ?string
    {
        $pezzi = explode('.', $chiave);
        return $pezzi[2] ?? null;
    }

    /**
     * @param array<string, mixed> $batch
     */
    private function assertBatchCanBeConfirmed(array $batch, int $schedinaId, int $strutturaId, int $userId): void
    {
        $token = trim((string) ($batch['token'] ?? ''));
        if ($token === '') {
            throw new ComponentiImportException('Batch non valido.');
        }

        if (!is_array($batch['raw_headers'] ?? null) || !is_array($batch['raw_rows'] ?? null)) {
            throw new ComponentiImportException('Payload batch non valido.');
        }

        if (($batch['status'] ?? 'pending') !== 'pending') {
            throw new ComponentiImportException('Batch non in stato pending.');
        }

        $batchSchedinaId = $batch['schedina_id'] ?? null;
        $isNewSchedinaContext = $schedinaId === null || $schedinaId === 0;

        if ($isNewSchedinaContext) {
            if ($batchSchedinaId !== null && $batchSchedinaId !== 0 && $batchSchedinaId !== '') {
                throw new ComponentiImportException('Batch schedina non valido.');
            }
        } elseif ((int) $batchSchedinaId !== (int) $schedinaId) {
            throw new ComponentiImportException('Batch schedina non valido.');
        }

        if (($batch['struttura_id'] ?? null) !== $strutturaId) {
            throw new ComponentiImportException('Batch struttura non valido.');
        }

        if (($batch['user_id'] ?? null) !== $userId) {
            throw new ComponentiImportException('Batch utente non valido.');
        }

        $expiresAt = (int) ($batch['expires_at'] ?? 0);
        if ($expiresAt > 0 && $expiresAt < time()) {
            throw new ComponentiImportException('Batch scaduto.');
        }

        if (($batch['confirmed_at'] ?? null) !== null) {
            throw new ComponentiImportException('Batch già confermato.');
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function buildPersistableComponentPayload(array $row, int $schedinaId, int $strutturaId): array
    {
        return [
            'struttura_id' => $strutturaId,
            'schedina_id' => $schedinaId > 0 ? $schedinaId : null,
            'customer_id' => null,
            'name' => $row['name'] ?? null,
            'surname' => $row['surname'] ?? null,
            'sex' => $row['sex'] ?? null,
            'relationship' => $row['relationship'] ?? null,
            'exent' => $row['exent'] ?? null,
            'province_nac' => $row['province_nac'] ?? null,
            'city_nac' => $row['city_nac'] ?? null,
            'date_nac' => DatiComponenteNormalizzati::normalizzaData($row['date_nac'] ?? null),
            'cap_nac' => $row['cap_nac'] ?? null,
            'country_nac' => $row['country_nac'] ?? null,
            'regione_nac' => $row['regione_nac'] ?? null,
            'comune_nac' => $row['comune_nac'] ?? null,
            'country' => $row['country'] ?? null,
            'regione' => $row['regione'] ?? null,
            'typeaway' => $row['typeaway'] ?? null,
            'address' => $row['address'] ?? null,
            'number' => $row['number'] ?? null,
            'cap' => $row['cap'] ?? null,
            'province' => $row['province'] ?? null,
            'city' => $row['city'] ?? null,
        ];
    }
}