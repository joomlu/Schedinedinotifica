<?php

namespace App\Services;

use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class IstatWebService
{
    private const DEFAULT_URL = 'https://datiturismo.regione.emilia-romagna.it/ws/checkinV2';

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
            return EsitoTrasmissioneIstat::crea('simulation', 'verify');
        }

        // Il WSDL espone soltanto operazioni che importano dati. Una verifica non invia.
        (new IstatXmlValidator())->validate($xml);
        return EsitoTrasmissioneIstat::crea('validated', 'verify');
    }

    public function send(Struttura $struttura, string $xml, Carbon $dal, Carbon $al): array
    {
        if ($this->isSimulation($struttura)) {
            return EsitoTrasmissioneIstat::crea('simulation', 'send');
        }

        return $this->callRealService($struttura, $xml, 'send', $dal, $al);
    }

    public function receipt(Struttura $struttura, Carbon $date): array
    {
        // No remote receipt protocol is implemented; never fabricate one.
        return EsitoTrasmissioneIstat::crea($this->isSimulation($struttura) ? 'simulation' : 'unknown', 'receipt');
    }

    private function callRealService(Struttura $struttura, string $xml, string $mode, Carbon $dal, Carbon $al): array
    {
        try {
            $soap = $this->service->buildSoapEnvelope($struttura, $xml, $mode);
            $response = Http::withBasicAuth((string) $struttura->istat_username, (string) $struttura->istat_password)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'Accept' => 'text/xml, application/xml, */*',
                    'SOAPAction' => '""',
                ])
                ->withoutRedirecting()
                ->timeout(30)
                ->send('POST', $struttura->istat_ws_url ?: self::DEFAULT_URL, ['body' => $soap]);
            return EsitoTrasmissioneIstat::daHttp($response->status(), $response->body(), $mode);
        } catch (Throwable) {
            // Do not propagate messages, traces or previous exceptions containing requests.
            return EsitoTrasmissioneIstat::crea('technical_error', $mode);
        } finally {
            unset($soap, $response);
        }
    }

    private function isSimulation(Struttura $struttura): bool
    {
        return (bool) ($struttura->istat_ws_simulazione ?? false);
    }

}
