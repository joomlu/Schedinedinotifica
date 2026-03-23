<?php

namespace App\Services;

use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class IstatWebService
{
    private const DEFAULT_URL = 'https://ross1000.regione.emilia-romagna.it/ross1000/ws/checkinV2';

    public function __construct(
        private IstatTabellaAService $service,
    ) {
    }

    public function credentialsStatus(Struttura $struttura): array
    {
        if ($this->isSimulation($struttura)) {
            return ['configured' => true, 'simulation' => true, 'missing' => []];
        }

        return [
            'configured' => filled($struttura->istat_username) && filled($struttura->istat_password) && filled($struttura->istat_codice_struttura),
            'simulation' => false,
            'missing' => array_values(array_filter([
                blank($struttura->istat_username) ? 'username' : null,
                blank($struttura->istat_password) ? 'password' : null,
                blank($struttura->istat_codice_struttura) ? 'codice struttura Ross1000' : null,
            ])),
        ];
    }

    public function verify(Struttura $struttura, string $xml, Carbon $dal, Carbon $al): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'ok' => true,
                'mode' => 'verify',
                'response_code' => 'SIM-ISTAT-VERIFY-OK',
                'message' => 'Simulazione ISTAT: verifica completata con esito positivo.',
                'detail' => 'Il file XML demo e stato validato internamente senza chiamare il servizio reale.',
                'raw' => ['simulation' => true, 'bytes' => strlen($xml), 'dal' => $dal->toDateString(), 'al' => $al->toDateString()],
                'simulated' => true,
            ];
        }

        return $this->callRealService($struttura, $xml, 'verify', $dal, $al);
    }

    public function send(Struttura $struttura, string $xml, Carbon $dal, Carbon $al): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'ok' => true,
                'mode' => 'send',
                'response_code' => 'SIM-ISTAT-SEND-OK',
                'message' => 'Simulazione ISTAT: invio completato con esito positivo.',
                'detail' => 'Trasmissione demo registrata. Nessun dato e stato inviato al portale reale.',
                'raw' => ['simulation' => true, 'bytes' => strlen($xml), 'dal' => $dal->toDateString(), 'al' => $al->toDateString()],
                'simulated' => true,
            ];
        }

        return $this->callRealService($struttura, $xml, 'send', $dal, $al);
    }

    public function receipt(Struttura $struttura, Carbon $date): array
    {
        if ($this->isSimulation($struttura)) {
            $pdf = $this->fakeReceiptPdf($struttura, $date);
            return [
                'ok' => true,
                'mode' => 'receipt',
                'response_code' => 'SIM-ISTAT-RICEVUTA-OK',
                'message' => 'Simulazione ISTAT: ricevuta demo generata.',
                'detail' => 'PDF demo disponibile per test del circuito Tabella A Emilia-Romagna.',
                'receipt_binary' => $pdf,
                'simulated' => true,
            ];
        }

        throw new \RuntimeException('Recupero ricevuta reale da implementare dopo la prima prova con credenziali operative.');
    }

    private function callRealService(Struttura $struttura, string $xml, string $mode, Carbon $dal, Carbon $al): array
    {
        $soap = $this->service->buildSoapEnvelope($struttura, $xml, $mode);

        try {
            $response = Http::withBasicAuth((string) $struttura->istat_username, (string) $struttura->istat_password)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'Accept' => 'text/xml, application/xml, */*',
                    'SOAPAction' => 'inviaMovimentazione',
                ])
                ->timeout(30)
                ->send('POST', $struttura->istat_ws_url ?: self::DEFAULT_URL, ['body' => $soap]);
        } catch (Throwable $e) {
            throw new \RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }

        $body = $response->body();
        $ok = $response->successful() && !Str::contains(Str::lower($body), ['fault', 'errore', 'error']);

        return [
            'ok' => $ok,
            'mode' => $mode,
            'response_code' => (string) $response->status(),
            'message' => $ok ? 'Invio Ross1000 eseguito.' : 'Invio Ross1000 non riuscito.',
            'detail' => $body,
            'raw' => ['headers' => $response->headers(), 'body' => $body, 'soap' => $soap],
        ];
    }

    private function isSimulation(Struttura $struttura): bool
    {
        return (bool) ($struttura->istat_ws_simulazione ?? false);
    }

    private function fakeReceiptPdf(Struttura $struttura, Carbon $date): string
    {
        $lines = [
            'Ricevuta Tabella A Emilia-Romagna - Simulazione',
            'Struttura: ' . ($struttura->nome_struttura ?: 'Struttura'),
            'Data riferimento: ' . $date->format('d/m/Y'),
            'Esito: OK DEMO',
            'Nessun dato e stato inviato al servizio reale Ross1000.',
        ];
        $text = implode("\n", $lines);
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
}
