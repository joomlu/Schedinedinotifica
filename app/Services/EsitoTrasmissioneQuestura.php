<?php

namespace App\Services;

/** Only this closed projection may cross the transport/persistence boundary. */
final class EsitoTrasmissioneQuestura
{
    private const MESSAGES = [
        'simulation' => 'Simulazione Questura: nessuna trasmissione ufficiale confermata.',
        'in_progress' => 'Tentativo registrato; esito non ancora disponibile. Non ripetere automaticamente.',
        'uncertain' => 'Esito incerto: richiesta Send iniziata, risposta non verificabile. Non ripetere automaticamente.',
        'partial' => 'Acquisizione parziale dichiarata da Questura. Verificare gli esiti senza ripetere tutto il payload.',
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
        $result = [
            'state' => $state,
            'mode' => $mode,
            'simulated' => $state === 'simulation',
            'accepted' => false,
            'ok' => false,
            'message' => self::MESSAGES[$state],
        ];
        if ($mode === 'send' && in_array($state, ['technical_error', 'rejected'], true)
            && ($data['transmission_excluded'] ?? null) === true) {
            $result['transmission_excluded'] = true;
        }
        if (is_int($data['valid_rows'] ?? null) && $data['valid_rows'] >= 0 && $data['valid_rows'] <= 1000) {
            $result['valid_rows'] = $data['valid_rows'];
            $result['row_errors'] = [];
            foreach (array_slice(is_array($data['row_errors'] ?? null) ? $data['row_errors'] : [], 0, 1000) as $error) {
                if (is_array($error) && is_int($error['row'] ?? null) && $error['row'] >= 1 && $error['row'] <= 1000) {
                    $result['row_errors'][] = ['row' => $error['row'], 'code' => in_array($error['code'] ?? null, ['11', '12'], true) ? $error['code'] : 'non_classificato'];
                }
            }
        }
        return $result;
    }

    public static function storico(mixed $state, mixed $result): array
    {
        if (is_array($result) && (($result['simulated'] ?? null) === true || ($result['state'] ?? null) === 'simulation')) {
            $state = 'simulation';
        }
        return self::sanifica(['state' => $state === 'error' ? 'historical_error' : (is_string($state) ? $state : 'unknown'), 'mode' => 'test'] + (is_array($result) ? array_intersect_key($result, array_flip(['valid_rows', 'row_errors'])) : []));
    }
}
