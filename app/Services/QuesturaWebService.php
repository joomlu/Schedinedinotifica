<?php

namespace App\Services;

use App\Exceptions\QuesturaTransportDisabledException;
use App\Models\Struttura;
use Carbon\Carbon;
use SoapClient;
use SoapFault;
use Throwable;

class QuesturaWebService
{
    private const WSDL = 'https://alloggiatiweb.poliziadistato.it/service/service.asmx?WSDL';

    public function credentialsStatus(Struttura $struttura): array
    {
        return [
            'configured' => filled($struttura->questura_username) && filled($struttura->questura_password) && filled($struttura->questura_wskey),
            'simulation' => $this->isSimulation($struttura),
            'missing' => array_values(array_filter([
                blank($struttura->questura_username) ? 'username' : null,
                blank($struttura->questura_password) ? 'password' : null,
                blank($struttura->questura_wskey) ? 'WSKEY' : null,
            ])),
        ];
    }

    public function verify(Struttura $struttura, string $txt): array
    {
        $this->assertTransportEnabled();
        try {
            $internal = $this->verifyInternal($struttura, $txt);
            $state = ($internal['simulated'] ?? false) === true ? 'simulation'
                : (($internal['ok'] ?? false) === true ? 'unknown' : 'rejected');
            return EsitoTrasmissioneQuestura::sanifica(['state' => $state, 'mode' => 'test'] + array_intersect_key($internal, array_flip(['valid_rows', 'row_errors'])));
        } catch (Throwable) {
            return EsitoTrasmissioneQuestura::crea('technical_error', 'test');
        }
    }

    public function send(Struttura $struttura, string $txt): array
    {
        $this->assertTransportEnabled();
        try {
            $internal = $this->sendInternal($struttura, $txt);
            $state = ($internal['simulated'] ?? false) === true ? 'simulation'
                : (($internal['pre_send_error'] ?? false) ? 'technical_error'
                : (($internal['uncertain'] ?? false) ? 'uncertain'
                : (($internal['partial'] ?? false) ? 'partial'
                : (($internal['ok'] ?? false) === true ? 'sent' : 'rejected'))));
            return EsitoTrasmissioneQuestura::sanifica(['state' => $state, 'mode' => 'send'] + array_intersect_key($internal, array_flip(['valid_rows', 'row_errors', 'transmission_excluded'])));
        } catch (Throwable) {
            // Un errore inatteso senza prova della fase raggiunta non libera il retry.
            return EsitoTrasmissioneQuestura::crea('uncertain', 'send');
        }
    }

    private function verifyInternal(Struttura $struttura, string $txt): array
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
        $auth = $this->authenticationTest($client, $token, $struttura);
        if (!$auth['ok']) {
            return $auth + ['stage' => 'authentication'];
        }

        $response = $this->call($client, 'Test', [
            'Utente' => (string) $struttura->questura_username,
            'token' => $token,
            'ElencoSchedine' => ['string' => explode("\r\n", $txt)],
        ]);

        return $this->normalizeWsResponse('test', $response, ['count' => count(explode("\r\n", $txt))]);
    }

    private function sendInternal(Struttura $struttura, string $txt): array
    {
        if ($this->isSimulation($struttura)) {
            return [
                'ok' => true,
                'mode' => 'send',
                'response_code' => 'SIM-SEND-OK',
                'message' => 'Simulazione Questura: invio completato con esito positivo.',
                'detail' => 'Nessuna trasmissione o ricevuta ufficiale.',
                'raw' => ['simulation' => true, 'bytes' => strlen($txt)],
                'context' => ['simulation' => true],
                'simulated' => true,
            ];
        }

        try {
            $client = $this->makeClient();
            $token = $this->generateToken($client, $struttura);
            $auth = $this->authenticationTest($client, $token, $struttura);
            if (!$auth['ok']) {
                return $auth + ['stage' => 'authentication', 'transmission_excluded' => true];
            }
        } catch (Throwable) {
            return ['pre_send_error' => true, 'transmission_excluded' => true];
        }

        try {
            $response = $this->call($client, 'Send', [
                'Utente' => (string) $struttura->questura_username,
            'token' => $token,
            'ElencoSchedine' => ['string' => explode("\r\n", $txt)],
        ]);

            return $this->normalizeWsResponse('send', $response, ['count' => count(explode("\r\n", $txt))]);
        } catch (Throwable) {
            return ['uncertain' => true, 'ok' => false];
        }
    }

    public function receipt(Struttura $struttura, Carbon $date): array
    {
        $this->assertTransportEnabled();
        if ($this->isSimulation($struttura)) {
            return EsitoTrasmissioneQuestura::crea('unavailable', 'receipt');
        }
        try {
            if ($date->startOfDay()->greaterThanOrEqualTo(now()->startOfDay()) || $date->lessThan(now()->startOfDay()->subDays(30))) {
                return EsitoTrasmissioneQuestura::crea('unavailable', 'receipt');
            }
            $client = $this->makeClient();
            $token = $this->generateToken($client, $struttura);
            $response = $this->normalizeValue($this->call($client, 'Ricevuta', [
                'Utente' => (string) $struttura->questura_username,
                'token' => $token,
                'Data' => $date->format('Y-m-d\\TH:i:s'),
            ]));
            $pdf = $response['PDF'] ?? null;
            // ext-soap decodifica xsd:base64Binary in byte; nessuna ricerca euristica.
            if (($response['RicevutaResult']['esito'] ?? null) !== true || !is_string($pdf)
                || strlen($pdf) > 10 * 1024 * 1024 || !str_starts_with($pdf, '%PDF-') || !str_contains(substr($pdf, -1024), '%%EOF')) {
                return EsitoTrasmissioneQuestura::crea('unavailable', 'receipt');
            }
            return ['state' => 'receipt_available', 'bytes' => $pdf, 'mime' => 'application/pdf'];
        } catch (Throwable) {
            return EsitoTrasmissioneQuestura::crea('technical_error', 'receipt');
        }
    }

    public function downloadReferenceTables(Struttura $struttura): array
    {
        $this->assertTransportEnabled();
        // No CSV persistence or catalog mutation until a validated artifact contract exists.
        return EsitoTrasmissioneQuestura::crea('unavailable', 'tables');
    }

    public function assertTransportEnabled(): void
    {
        if (config('questura.enabled') !== true) {
            throw new QuesturaTransportDisabledException;
        }
    }

    protected function isSimulation(Struttura $struttura): bool
    {
        return !function_exists('app') || !app()->environment('production')
            || config('questura.enabled') !== true
            || (bool) ($struttura->questura_ws_simulazione ?? true);
    }

    protected function makeClient(): SoapClient
    {
        $this->assertTransportEnabled();
        if (!app()->environment('production') || config('questura.enabled') !== true) {
            throw new \RuntimeException('Trasporto Questura non abilitato.');
        }
        if (!class_exists(SoapClient::class)) {
            throw new \RuntimeException('Estensione SOAP non disponibile sul server PHP.');
        }

        return new SoapClient(self::WSDL, [
            'soap_version' => SOAP_1_2,
            'stream_context' => stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true], 'http' => ['timeout' => 30]]),
            'trace' => false,
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

        $rawToken = $this->normalizeValue($response);
        if (($rawToken['result']['esito'] ?? null) !== true) {
            throw new \RuntimeException('Generazione token Questura non riuscita.');
        }
        $token = $rawToken['GenerateTokenResult']['token'] ?? null;
        $expires = $rawToken['GenerateTokenResult']['expires'] ?? null;
        if (!is_string($expires) || !str_contains($expires, 'T') || strtotime($expires) === false || strtotime($expires) <= time()) {
            throw new \RuntimeException('Token Questura scaduto o senza scadenza verificabile.');
        }
        if (!is_string($token) || trim($token) === '') {
            throw new \RuntimeException('GenerateToken non ha restituito un token valido.');
        }

        return trim($token);
    }

    private function authenticationTest(SoapClient $client, string $token, Struttura $struttura): array
    {
        $response = $this->call($client, 'Authentication_Test', [
            'Utente' => (string) $struttura->questura_username,
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
        $key = match ($mode) {
            'authentication' => 'Authentication_TestResult',
            'test' => 'TestResult',
            'send' => 'SendResult',
        };
        $esito = $raw[$key]['esito'] ?? null;
        if (!is_bool($esito)) {
            throw new \RuntimeException('Risposta Questura non conforme al contratto.');
        }
        if ($mode === 'authentication' || !$esito) {
            // Il solo esito generale negativo non prova zero righe acquisite.
            return ['ok' => $esito];
        }
        $count = $raw['result']['SchedineValide'] ?? null;
        $details = $raw['result']['Dettaglio']['EsitoOperazioneServizio'] ?? null;
        if (is_array($details) && !array_is_list($details)) { $details = [$details]; }
        if (!is_int($count) || $count < 0 || $count > ($context['count'] ?? 0)
            || !is_array($details) || count($details) !== ($context['count'] ?? 0)) {
            throw new \RuntimeException('Esiti per riga Questura non conformi al contratto.');
        }
        $valid = 0; $errors = [];
        foreach ($details as $index => $detail) {
            if (!is_bool($detail['esito'] ?? null)) { throw new \RuntimeException('Esito per riga Questura non valido.'); }
            if ($detail['esito']) { $valid++; }
            else {
                $code = $detail['ErroreCod'] ?? null;
                // Soltanto codici esplicitamente descritti nella fonte consultata.
                $errors[] = ['row' => $index + 1, 'code' => in_array($code, ['11', '12'], true) ? $code : 'non_classificato'];
            }
        }
        if ($valid !== $count) { throw new \RuntimeException('Conteggio esiti Questura incoerente.'); }
        return ['ok' => $count === ($context['count'] ?? 0), 'partial' => $count > 0 && $count < ($context['count'] ?? 0), 'valid_rows' => $count, 'row_errors' => $errors]
            + ($mode === 'send' && $count === 0 ? ['transmission_excluded' => true] : []);
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

}
