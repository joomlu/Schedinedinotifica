<?php

namespace App\Services;

/** Only this closed projection may cross the transport/persistence boundary. */
final class EsitoTrasmissioneQuestura
{
    private const MESSAGES = [
        'simulation' => 'Simulazione Questura: nessuna trasmissione ufficiale confermata.',
        'sent' => 'Richiesta inviata; accettazione Questura non verificata.',
        'unknown' => 'Esito non verificato; nessuna accettazione ufficiale confermata.',
        'rejected' => 'Rilevato un errore remoto; nessuna accettazione confermata.',
        'technical_error' => 'Errore tecnico Questura. Dettagli remoti omessi per sicurezza.',
        'historical_error' => 'Errore registrato nello storico; nessuna accettazione confermata.',
        'unavailable' => 'Download sospeso: validazione sicura degli allegati Questura non disponibile.',
    ];

    public static function crea(string $state, string $mode): array
    {
        return self::sanifica(['state' => $state, 'mode' => $mode]);
    }

    public static function sanifica(mixed $input): array
    {
        $data = $input instanceof \stdClass ? get_object_vars($input) : (is_array($input) ? $input : []);
        $state = $data['state'] ?? null;
        $mode = $data['mode'] ?? null;
        $state = is_string($state) && array_key_exists($state, self::MESSAGES) ? $state : 'unknown';
        $mode = is_string($mode) && in_array($mode, ['test', 'send', 'receipt', 'tables'], true) ? $mode : 'test';
        return [
            'state' => $state,
            'mode' => $mode,
            'simulated' => $state === 'simulation',
            'accepted' => false,
            'ok' => false,
            'message' => self::MESSAGES[$state],
        ];
    }

    public static function storico(mixed $state, mixed $result): array
    {
        if (is_array($result) && (($result['simulated'] ?? null) === true || ($result['state'] ?? null) === 'simulation')) {
            $state = 'simulation';
        }
        return self::crea($state === 'error' ? 'historical_error' : (is_string($state) ? $state : 'unknown'), 'test');
    }
}
