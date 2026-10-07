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
        try {
            $missing = array_values(array_filter([
                blank($struttura->istat_username) ? 'username' : null,
                blank($struttura->istat_password) ? 'password' : null,
                blank($struttura->istat_codice_struttura) ? 'codice struttura Ross1000' : null,
            ]));
            return ['configured' => !$missing, 'missing' => $missing, 'blocked' => false];
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return ['configured' => false, 'missing' => ['credenziali da riconfigurare'], 'blocked' => true];
        }
    }

    public function verify(Struttura $struttura, string $xml, Carbon $dal, Carbon $al): array
    {
        // Il WSDL espone soltanto operazioni che importano dati. Una verifica non invia.
        (new IstatXmlValidator())->validate($xml);
        return EsitoTrasmissioneIstat::crea('validated', 'verify');
    }

    public function send(Struttura $struttura, string $xml, Carbon $dal, Carbon $al): array
    {
        if (config('istat.enabled', false) !== true) {
            return EsitoTrasmissioneIstat::crea('disabled', 'send');
        }
        $region = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]+/', ' ', (string) $struttura->regione)));
        if ($region !== 'EMILIA ROMAGNA' || !$this->credentialsStatus($struttura)['configured']
            || ($struttura->istat_ws_url && $struttura->istat_ws_url !== self::DEFAULT_URL)) {
            return EsitoTrasmissioneIstat::crea('not_delivered', 'send');
        }

        return $this->callRealService($struttura, $xml, 'send', $dal, $al);
    }

    public function receipt(Struttura $struttura, Carbon $date): array
    {
        // No remote receipt protocol is implemented; never fabricate one.
        return EsitoTrasmissioneIstat::crea('unknown', 'receipt');
    }

    private function callRealService(Struttura $struttura, string $xml, string $mode, Carbon $dal, Carbon $al): array
    {
        $dispatched = false;
        try {
            $soap = $this->service->buildSoapEnvelope($struttura, $xml, $mode);
            $dispatched = true;
            $response = Http::withBasicAuth((string) $struttura->istat_username, (string) $struttura->istat_password)
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=UTF-8',
                    'Accept' => 'text/xml, application/xml, */*',
                    'SOAPAction' => '""',
                ])
                ->withoutRedirecting()
                ->timeout(30)
                ->send('POST', self::DEFAULT_URL, ['body' => $soap]);
            return (new IstatResponseParser())->parse($response->status(), $response->body(), $xml);
        } catch (Throwable) {
            // Do not propagate messages, traces or previous exceptions containing requests.
            return EsitoTrasmissioneIstat::crea($dispatched ? 'uncertain' : 'not_delivered', $mode);
        } finally {
            unset($soap, $response);
        }
    }

}
