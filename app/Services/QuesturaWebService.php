<?php

namespace App\Services;

use App\Models\TipoAlloggiato;
use App\Models\TipoDocumento;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use SoapClient;
use SoapFault;
use Throwable;

class QuesturaWebService
{
    private const WSDL = 'https://alloggiatiweb.poliziadistato.it/service/service.asmx?WSDL';

    public function credentialsStatus(Struttura $struttura): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'configured' => true,
                'simulation' => true,
                'missing' => [],
            ];
        }

        return [
            'configured' => filled($struttura->questura_username) && filled($struttura->questura_password) && filled($struttura->questura_wskey),
            'simulation' => false,
            'missing' => array_values(array_filter([
                blank($struttura->questura_username) ? 'username' : null,
                blank($struttura->questura_password) ? 'password' : null,
                blank($struttura->questura_wskey) ? 'WSKEY' : null,
            ])),
        ];
    }

    public function verify(Struttura $struttura, string $txt): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'ok' => true,
                'mode' => 'test',
                'response_code' => 'SIM-TEST-OK',
                'message' => 'Simulazione Questura: verifica completata con esito positivo.',
                'detail' => 'Nessun errore bloccante rilevato nel tracciato TXT demo.',
                'raw' => ['simulation' => true, 'bytes' => strlen($txt)],
                'context' => ['simulation' => true],
                'simulated' => true,
            ];
        }

        $client = $this->makeClient();
        $token = $this->generateToken($client, $struttura);
        $auth = $this->authenticationTest($client, $token);
        if (!$auth['ok']) {
            return $auth + ['stage' => 'authentication'];
        }

        $response = $this->call($client, 'Test', [
            'token' => $token,
            'ElencoSchedine' => $txt,
        ]);

        return $this->normalizeWsResponse('test', $response, ['token' => $token]);
    }

    public function send(Struttura $struttura, string $txt): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'ok' => true,
                'mode' => 'send',
                'response_code' => 'SIM-SEND-OK',
                'message' => 'Simulazione Questura: invio completato con esito positivo.',
                'detail' => 'Trasmissione demo registrata. Puoi scaricare la ricevuta simulata.',
                'raw' => ['simulation' => true, 'bytes' => strlen($txt)],
                'context' => ['simulation' => true],
                'simulated' => true,
            ];
        }

        $client = $this->makeClient();
        $token = $this->generateToken($client, $struttura);
        $auth = $this->authenticationTest($client, $token);
        if (!$auth['ok']) {
            return $auth + ['stage' => 'authentication'];
        }

        $response = $this->call($client, 'Send', [
            'token' => $token,
            'ElencoSchedine' => $txt,
        ]);

        return $this->normalizeWsResponse('send', $response, ['token' => $token]);
    }

    public function receipt(Struttura $struttura, Carbon $date): array
    {
        if ($this->isSimulation($struttura)) {
            $pdf = $this->fakeReceiptPdf($struttura, $date);
            return [
                'ok' => true,
                'mode' => 'receipt',
                'response_code' => 'SIM-RICEVUTA-OK',
                'message' => 'Simulazione Questura: ricevuta demo generata.',
                'detail' => 'PDF demo disponibile per test del circuito.',
                'raw' => ['simulation' => true, 'date' => $date->toDateString()],
                'context' => ['simulation' => true],
                'simulated' => true,
                'receipt_binary' => $pdf,
                'receipt_size' => strlen($pdf),
            ];
        }

        $client = $this->makeClient();
        $token = $this->generateToken($client, $struttura);
        $auth = $this->authenticationTest($client, $token);
        if (!$auth['ok']) {
            return $auth + ['stage' => 'authentication'];
        }

        $response = $this->call($client, 'Ricevuta', [
            'Utente' => (string) $struttura->questura_username,
            'token' => $token,
            'Data' => $date->format('Y-m-d\T00:00:00'),
        ]);

        $normalized = $this->normalizeWsResponse('receipt', $response, ['token' => $token, 'date' => $date->toDateString()]);
        $pdf = $this->extractReceiptBinary($response);
        if ($pdf !== null) {
            $normalized['receipt_binary'] = $pdf;
            $normalized['receipt_size'] = strlen($pdf);
        }

        return $normalized;
    }

    public function downloadReferenceTables(Struttura $struttura): array
    {
        if ($this->isSimulation($struttura)) {
            $tables = $this->fakeReferenceTables();
            $sync = $this->syncReferenceTables($tables);

            return [
                'ok' => true,
                'mode' => 'tables',
                'response_code' => 'SIM-TABELLE-OK',
                'message' => 'Simulazione Questura: tabelle ufficiali demo generate.',
                'detail' => 'Snapshot CSV disponibile per controllo e confronto con i codici del sistema.',
                'tables' => $tables,
                'sync' => $sync,
                'simulated' => true,
            ];
        }

        $client = $this->makeClient();
        $token = $this->generateToken($client, $struttura);
        $auth = $this->authenticationTest($client, $token);
        if (!$auth['ok']) {
            return $auth + ['stage' => 'authentication'];
        }

        $tableMap = [
            'luoghi' => 'Luoghi',
            'tipi_documento' => 'Tipi_Documento',
            'tipi_alloggiato' => 'Tipi_Alloggiato',
            'tipo_errore' => 'TipoErrore',
        ];

        $tables = [];
        foreach ($tableMap as $slug => $tipo) {
            $response = $this->call($client, 'Tabella', [
                'Utente' => (string) $struttura->questura_username,
                'token' => $token,
                'tipo' => $tipo,
            ]);

            $normalized = $this->normalizeWsResponse('tables', $response, ['token' => $token, 'tipo' => $tipo]);
            if (!($normalized['ok'] ?? false)) {
                return $normalized + ['stage' => 'tables', 'table' => $tipo];
            }

            $csv = $this->extractTableCsv($response);
            if ($csv === null || trim($csv) === '') {
                return [
                    'ok' => false,
                    'mode' => 'tables',
                    'response_code' => $normalized['response_code'] ?? 'CSV-VUOTO',
                    'message' => 'Download tabelle Questura non riuscito.',
                    'detail' => 'La tabella ' . $tipo . ' non ha restituito un CSV valido.',
                    'raw' => $normalized['raw'] ?? null,
                    'context' => ['token' => $token, 'tipo' => $tipo],
                ];
            }

            $tables[] = [
                'slug' => $slug,
                'type' => $tipo,
                'filename' => 'questura_' . $slug . '.csv',
                'csv' => $csv,
            ];
        }

        $sync = $this->syncReferenceTables($tables);

        return [
            'ok' => true,
            'mode' => 'tables',
            'response_code' => 'TABELLE-OK',
            'message' => 'Tabelle ufficiali Questura scaricate correttamente.',
            'detail' => 'Snapshot CSV disponibile per confronto con le codifiche del sistema.',
            'tables' => $tables,
            'sync' => $sync,
            'simulated' => false,
        ];
    }

    private function isSimulation(Struttura $struttura): bool
    {
        return (bool) ($struttura->questura_ws_simulazione ?? false);
    }

    private function makeClient(): SoapClient
    {
        if (!class_exists(SoapClient::class)) {
            throw new \RuntimeException('Estensione SOAP non disponibile sul server PHP.');
        }

        return new SoapClient(self::WSDL, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        ]);
    }

    private function generateToken(SoapClient $client, Struttura $struttura): string
    {
        $response = $this->call($client, 'GenerateToken', [
            'Utente' => (string) $struttura->questura_username,
            'Password' => (string) $struttura->questura_password,
            'WsKey' => (string) $struttura->questura_wskey,
        ]);

        $token = $this->extractScalar($response, ['GenerateTokenResult', 'generateTokenResult', 'token']);
        if (!is_string($token) || trim($token) === '') {
            throw new \RuntimeException('GenerateToken non ha restituito un token valido.');
        }

        return trim($token);
    }

    private function authenticationTest(SoapClient $client, string $token): array
    {
        $response = $this->call($client, 'Authentication_Test', [
            'token' => $token,
        ]);

        return $this->normalizeWsResponse('authentication', $response, ['token' => $token]);
    }

    private function call(SoapClient $client, string $method, array $params): mixed
    {
        try {
            return $client->__soapCall($method, [$params]);
        } catch (SoapFault $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        } catch (Throwable $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    private function normalizeWsResponse(string $mode, mixed $response, array $context = []): array
    {
        $raw = $this->normalizeValue($response);
        $flat = $this->flattenStrings($raw);
        $joined = mb_strtolower(implode(' | ', $flat));

        $responseCode = $this->findFirstMatch($raw, ['codice', 'code', 'esito', 'resultcode', 'returncode']);
        $message = $this->findFirstMatch($raw, ['messaggio', 'message', 'descrizione', 'description', 'resultmessage']);
        $detail = $this->findFirstMatch($raw, ['dettaglio', 'detail', 'details', 'errore']);

        $ok = !str_contains($joined, 'errore')
            && !str_contains($joined, 'error')
            && !str_contains($joined, 'ko')
            && !str_contains($joined, 'non valido')
            && !str_contains($joined, 'fallit')
            && ($message === null || !preg_match('/errore|error|ko|fallit/i', (string) $message));

        if ($mode === 'authentication' && $responseCode !== null) {
            $ok = $ok && !preg_match('/^0$/', (string) $responseCode);
        }

        return [
            'ok' => $ok,
            'mode' => $mode,
            'response_code' => $responseCode,
            'message' => $message ?: ($ok ? 'Operazione completata.' : 'Operazione non completata.'),
            'detail' => $detail,
            'raw' => $raw,
            'context' => $context,
        ];
    }

    private function extractTableCsv(mixed $response): ?string
    {
        $csv = $this->extractScalar($response, ['CSV', 'Csv', 'csv']);
        if (is_string($csv) && trim($csv) !== '') {
            return str_replace(["\r\n", "\r"], "\n", $csv);
        }

        foreach ($this->flattenStrings($this->normalizeValue($response)) as $value) {
            $trimmed = trim($value);
            if ($trimmed !== '' && str_contains($trimmed, ';')) {
                return str_replace(["\r\n", "\r"], "\n", $trimmed);
            }
        }

        return null;
    }

    private function extractReceiptBinary(mixed $response): ?string
    {
        $raw = $this->normalizeValue($response);
        foreach ($this->flattenStrings($raw) as $value) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }
            $decoded = base64_decode($trimmed, true);
            if ($decoded !== false && str_starts_with($decoded, '%PDF')) {
                return $decoded;
            }
        }

        return null;
    }

    private function fakeReceiptPdf(Struttura $struttura, Carbon $date): string
    {
        $lines = [
            'Ricevuta Questura - Simulazione',
            'Struttura: ' . ($struttura->nome_struttura ?: 'Struttura'),
            'Data riferimento: ' . $date->format('d/m/Y'),
            'Esito: OK DEMO',
            'Questa ricevuta e\' stata generata in modalita\' simulazione.',
        ];
        $text = implode("\\n", $lines);
        $stream = "BT\n/F1 12 Tf\n50 760 Td\n14 TL\n";
        foreach (explode("\n", $text) as $i => $line) {
            if ($i > 0) {
                $stream .= "T*\n";
            }
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= '(' . $escaped . ") Tj\n";
        }
        $stream .= "ET";
        $len = strlen($stream);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $offsets[] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "5 0 obj\n<< /Length $len >>\nstream\n$stream\nendstream\nendobj\n";
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }

    private function extractScalar(mixed $value, array $preferredKeys = []): mixed
    {
        $normalized = $this->normalizeValue($value);
        foreach ($preferredKeys as $key) {
            $found = $this->findKey($normalized, $key);
            if ($found !== null) {
                return $found;
            }
        }

        if (is_scalar($normalized) || $normalized === null) {
            return $normalized;
        }

        $flat = $this->flattenStrings($normalized);
        return $flat[0] ?? null;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }
            return $normalized;
        }

        return $value;
    }

    private function fakeReferenceTables(): array
    {
        return [
            [
                'slug' => 'luoghi',
                'type' => 'Luoghi',
                'filename' => 'questura_luoghi.csv',
                'csv' => "CODICE;DESCRIZIONE\nH501;ROMA\nF205;MILANO\nZ110;FRANCIA\nZ129;ROMANIA",
            ],
            [
                'slug' => 'tipi_documento',
                'type' => 'Tipi_Documento',
                'filename' => 'questura_tipi_documento.csv',
                'csv' => $this->buildCsvFromModel(TipoDocumento::query()->orderBy('codice')->get()),
            ],
            [
                'slug' => 'tipi_alloggiato',
                'type' => 'Tipi_Alloggiato',
                'filename' => 'questura_tipi_alloggiato.csv',
                'csv' => $this->buildCsvFromModel(TipoAlloggiato::query()->orderBy('codice')->get()),
            ],
            [
                'slug' => 'tipo_errore',
                'type' => 'TipoErrore',
                'filename' => 'questura_tipo_errore.csv',
                'csv' => "CODICE;DESCRIZIONE\n0;Nessun errore\n999;Simulazione interna",
            ],
        ];
    }

    private function buildCsvFromModel(Collection $rows): string
    {
        $lines = ['CODICE;DESCRIZIONE'];
        foreach ($rows as $row) {
            $lines[] = trim((string) $row->codice) . ';' . trim((string) $row->descrizione);
        }

        return implode("\n", $lines);
    }

    private function syncReferenceTables(array $tables): array
    {
        $sync = [
            'tipo_documento' => 0,
            'tipo_alloggiato' => 0,
        ];

        foreach ($tables as $table) {
            if (($table['slug'] ?? null) === 'tipi_documento') {
                $sync['tipo_documento'] = $this->syncCatalogCsv(TipoDocumento::class, (string) ($table['csv'] ?? ''));
            }

            if (($table['slug'] ?? null) === 'tipi_alloggiato') {
                $sync['tipo_alloggiato'] = $this->syncCatalogCsv(TipoAlloggiato::class, (string) ($table['csv'] ?? ''));
            }
        }

        return $sync;
    }

    private function syncCatalogCsv(string $modelClass, string $csv): int
    {
        $rows = $this->parseCsvRows($csv);
        $count = 0;

        foreach ($rows as $row) {
            $codice = trim((string) ($row['codice'] ?? $row['id'] ?? $row['tipoalloggiato'] ?? $row['tipodocumento'] ?? ''));
            $descrizione = trim((string) ($row['descrizione'] ?? $row['descr'] ?? $row['denominazione'] ?? $row['tipo'] ?? ''));

            if ($codice === '' || $descrizione === '') {
                continue;
            }

            $modelClass::query()->updateOrCreate(
                ['codice' => $codice],
                ['descrizione' => $descrizione, 'locked' => true]
            );
            $count++;
        }

        return $count;
    }

    private function parseCsvRows(string $csv): array
    {
        $lines = preg_split('/\n+/', trim($csv)) ?: [];
        if (count($lines) < 2) {
            return [];
        }

        $header = str_getcsv(array_shift($lines), ';');
        $header = array_map(function ($value) {
            $value = mb_strtolower(trim((string) $value));
            $value = str_replace([' ', '-'], '_', $value);
            return preg_replace('/[^a-z0-9_]+/u', '', $value) ?: '';
        }, $header);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line, ';');
            $assoc = [];
            foreach ($header as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = $values[$index] ?? null;
            }
            $rows[] = $assoc;
        }

        return $rows;
    }

    private function flattenStrings(mixed $value): array
    {
        if (is_scalar($value) || $value === null) {
            return [$value === null ? '' : (string) $value];
        }

        $out = [];
        foreach ((array) $value as $item) {
            array_push($out, ...$this->flattenStrings($item));
        }
        return $out;
    }

    private function findFirstMatch(mixed $value, array $needles): ?string
    {
        $normalized = $this->normalizeValue($value);
        foreach ($needles as $needle) {
            $found = $this->findByNeedle($normalized, mb_strtolower($needle));
            if ($found !== null && $found !== '') {
                return (string) $found;
            }
        }

        return null;
    }

    private function findByNeedle(mixed $value, string $needle): mixed
    {
        if (!is_array($value)) {
            return null;
        }

        foreach ($value as $key => $item) {
            if (is_string($key) && mb_strtolower($key) === $needle) {
                return is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item;
            }
            if (is_array($item)) {
                $found = $this->findByNeedle($item, $needle);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function findKey(mixed $value, string $key): mixed
    {
        if (!is_array($value)) {
            return null;
        }

        foreach ($value as $currentKey => $item) {
            if ((string) $currentKey === $key) {
                return $item;
            }
            if (is_array($item)) {
                $found = $this->findKey($item, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
