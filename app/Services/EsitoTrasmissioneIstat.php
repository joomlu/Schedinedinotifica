<?php

namespace App\Services;

/** Closed schema: provider text is never eligible for persistence or presentation. */
final class EsitoTrasmissioneIstat
{
    private const MESSAGGI = [
        'validated' => 'XML verificato localmente contro gli XSD regionali; nessuna trasmissione effettuata.',
        'simulation' => 'Simulazione locale: nessuna accettazione ufficiale Ross1000.',
        'sent' => 'Richiesta inviata; accettazione Ross1000 non verificata.',
        'rejected' => 'Rilevato un errore nella risposta; nessuna accettazione confermata.',
        'historical_error' => 'Errore registrato nello storico; nessuna accettazione ufficiale confermata.',
        'technical_error' => 'Errore tecnico ISTAT. Dettagli remoti omessi per sicurezza.',
        'pending' => 'Operazione in attesa; nessuna accettazione confermata.',
        'disabled' => 'Trasporto ISTAT disabilitato: nessuna richiesta effettuata.',
        'not_delivered' => 'Richiesta non consegnata: errore prima del trasporto.',
        'uncertain' => 'Esito incerto: verificare lo storico Ross1000 prima di ripetere o caricare il file.',
        'processed' => 'Esito positivo dei record ricevuto; verificare anche il calendario sul portale Ross1000.',
        'partial' => 'Esito parziale dei record: verificare e correggere sul portale Ross1000.',
        'manual_registered' => 'Consegna sul portale dichiarata dall’operatore; non costituisce ricevuta ufficiale.',
        'unknown' => 'Esito non verificato; nessuna accettazione ufficiale confermata.',
    ];

    public static function crea(string $stato, string $modo, ?int $http = null): array
    {
        return self::sanifica(['state' => $stato, 'mode' => $modo, 'transport' => ['http_status' => $http]]);
    }

    public static function daHttp(int $http, string $body, string $modo): array
    {
        // Senza correlazione con i record originali l’HTTP non prova l’esito.
        return self::crea('uncertain', $modo, $http);
    }

    public static function sanifica(mixed $input): array
    {
        $safe = self::proietta($input, [
            'state' => array_keys(self::MESSAGGI),
            'mode' => ['send', 'verify', 'receipt', 'manual'],
            'transport' => ['http_status' => 'http'],
        ]);
        $stato = $safe['state'] ?? 'unknown';
        return [
            'state' => $stato,
            'mode' => $safe['mode'] ?? 'verify',
            'transport' => ['http_status' => $safe['transport']['http_status'] ?? null],
            'simulated' => $stato === 'simulation',
            'accepted' => false,
            'ok' => false,
            'message' => self::MESSAGGI[$stato],
        ];
    }

    private static function proietta(mixed $input, array $schema): array
    {
        // Do not invoke serialization/magic methods on untrusted objects.
        $data = $input instanceof \stdClass ? get_object_vars($input) : (is_array($input) ? $input : []);
        $out = [];
        foreach ($schema as $key => $rule) {
            $value = $data[$key] ?? null;
            if (is_array($rule) && !array_is_list($rule)) {
                $out[$key] = self::proietta($value, $rule);
            } elseif ($rule === 'http') {
                $out[$key] = is_int($value) && $value >= 100 && $value <= 599 ? $value : null;
            } elseif (is_string($value) && in_array($value, $rule, true)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    public static function storico(mixed $stato, mixed $result): array
    {
        $simulation = is_array($result) && (($result['simulated'] ?? null) === true
            || ($result['state'] ?? null) === 'simulation');
        // Preserve legacy failure semantics only in presentation, without rewriting history.
        $stato = $stato === 'error' ? 'historical_error' : $stato;
        return self::crea($simulation ? 'simulation' : (is_string($stato) ? $stato : 'unknown'), 'receipt');
    }
}
