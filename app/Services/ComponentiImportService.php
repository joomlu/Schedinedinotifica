<?php

namespace App\Services;

use App\Exceptions\ComponentiImportException;
use App\Support\Componenti\ContrattoImportazioneComponentiV1;
use App\Support\Componenti\DatiComponenteNormalizzati;
use DateTimeImmutable;

class ComponentiImportService
{
    public const FORMATO_CSV = 'csv';
    public const FORMATO_TXT = 'txt';

    public const DELIMITATORE_CSV = ';';
    public const DELIMITATORE_TXT = "\t";

    public const MAX_BYTES = 5242880;
    public const MAX_RIGHE = 1000;
    public const BATCH_TTL_MINUTES = 30;

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
        return [self::FORMATO_CSV, self::FORMATO_TXT];
    }

    public function delimitatorePerFormato(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => self::DELIMITATORE_CSV,
            self::FORMATO_TXT => self::DELIMITATORE_TXT,
        };
    }

    public function nomeFileTemplate(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => 'modello_import_componenti.csv',
            self::FORMATO_TXT => 'modello_import_componenti.txt',
        };
    }

    public function contentTypePerFormato(string $formato): string
    {
        return match ($this->normalizzaFormato($formato)) {
            self::FORMATO_CSV => 'text/csv; charset=UTF-8',
            self::FORMATO_TXT => 'text/plain; charset=UTF-8',
        };
    }

    public function templateExampleRow(): array
    {
        return [
            'Mario',
            'Rossi',
            'M',
            'Italiana',
            'Italia',
            '02/10/1980',
            'Emilia-Romagna',
            'Rimini',
            'Rimini',
            '47921',
            'Italia',
            'Emilia-Romagna',
            'RN',
            'Rimini',
            'Via',
            'Via Roma',
            '10',
            '47921',
        ];
    }

    /**
     * @return array{formato:string,delimitatore:string,headers:array<int, string>,rows:array<int, array<string, mixed>>,totale_righe:int,righe_valide:int,righe_in_errore:int,metadata:array<string, mixed>}
     */
    public function previewDaContenuto(string $contenuto, string $formato, ?callable $tipoAlloggiatoResolver = null): array
    {
        [$intestazioni, $righe] = $this->parsaFileDelimitato($contenuto, $this->delimitatorePerFormato($this->normalizzaFormato($formato)));

        return $this->analizzaImportazione($intestazioni, $righe, $formato, $tipoAlloggiatoResolver);
    }

    /**
     * @param array<int, string> $intestazioni
     * @param array<int, array<int, string|null>> $righe
     * @return array{formato:string,delimitatore:string,headers:array<int, string>,raw_headers:array<int, string>,raw_rows:array<int, array<int, string|null>>,rows:array<int, array<string, mixed>>,totale_righe:int,righe_valide:int,righe_in_errore:int,metadata:array<string, mixed>}
     */
    public function analizzaImportazione(array $intestazioni, array $righe, string $formato, ?callable $tipoAlloggiatoResolver = null): array
    {
        $formato = $this->normalizzaFormato($formato);
        $delimitatore = $this->delimitatorePerFormato($formato);

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

            foreach ($validationErrors as $key => $message) {
                $campo = $this->campoDaChiaveErrore((string) $key);
                $errori[] = [
                    'field' => $campo,
                    'label' => $this->etichettaDaCampo($campo),
                    'message' => $message,
                ];
            }

            $righePreview[] = [
                'row_number' => $numeroRiga,
                'status' => empty($errori) ? 'VALIDO' : 'ERRORE',
                'name' => (string) ($normalizzato->toArray()['name'] ?? ''),
                'surname' => (string) ($normalizzato->toArray()['surname'] ?? ''),
                'sex' => (string) ($normalizzato->toArray()['sex'] ?? ''),
                'date_nac' => $this->formatoDataPreview($normalizzato->toArray()['date_nac'] ?? null, (string) ($assoc['date_nac'] ?? '')),
                'country_nac' => (string) ($normalizzato->toArray()['country_nac'] ?? ''),
                'relationship' => (string) ($normalizzato->toArray()['relationship'] ?? ''),
                'relationship_label' => (string) ($defaultInfo['relationship_descrizione'] ?? ''),
                'exent' => (string) ($normalizzato->toArray()['exent'] ?? ''),
                'errors' => $errori,
                'data' => $normalizzato->toArray(),
            ];
        }

        return [
            'formato' => $formato,
            'delimitatore' => $delimitatore,
            'headers' => $this->headersTemplate(),
            'raw_headers' => array_values($intestazioni),
            'raw_rows' => array_values($righe),
            'rows' => $righePreview,
            'totale_righe' => count($righePreview),
            'righe_valide' => count(array_filter($righePreview, fn (array $row) => $row['status'] === 'VALIDO')),
            'righe_in_errore' => count(array_filter($righePreview, fn (array $row) => $row['status'] === 'ERRORE')),
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

        $validRows = array_values(array_filter($analisi['rows'], fn (array $row) => $row['status'] === 'VALIDO'));
        $invalidRows = array_values(array_filter($analisi['rows'], fn (array $row) => $row['status'] !== 'VALIDO'));
        $payloads = array_map(function (array $row) use ($schedinaId, $strutturaId) {
            return $this->buildPersistableComponentPayload($row['data'] ?? [], $schedinaId, $strutturaId);
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
    private function parsaFileDelimitato(string $contenuto, string $delimitatore): array
    {
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

    /**
     * @param array<int, string|null> $intestazioni
     * @return array<int, string>
     */
    private function validaEMappeIntestazioni(array $intestazioni): array
    {
        $attese = [];
        foreach ($this->colonneTemplate() as $campo => $etichetta) {
            $attese[$this->normalizzaIntestazione($etichetta)] = $campo;
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

        foreach ($attese as $normalizzata => $campo) {
            if (!isset($trovate[$normalizzata])) {
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

        if (($batch['schedina_id'] ?? null) !== $schedinaId) {
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
            'schedina_id' => $schedinaId,
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