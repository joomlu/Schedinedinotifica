<?php

namespace App\Support\Anagrafica;

use Carbon\Carbon;

final class EtaOperativa
{
    public static function etaCronologica(?string $dataNascita, ?Carbon $reference = null): ?int
    {
        if ($dataNascita === null) {
            return null;
        }

        $valore = trim((string) $dataNascita);
        if ($valore === '') {
            return null;
        }

        if (preg_match('/(^|[\/\-])0{1,2}(?=[\/\-]|$)/', $valore) === 1) {
            return null;
        }

        try {
            $nascita = Carbon::parse($valore);
        } catch (\Throwable $e) {
            return null;
        }

        $ref = $reference ?? Carbon::now();

        if ($nascita->isFuture()) {
            return null;
        }

        $year = (int) $nascita->format('Y');
        $month = (int) $nascita->format('m');
        $day = (int) $nascita->format('d');

        if ($year <= 0 || $month <= 0 || $day <= 0 || !checkdate($month, $day, $year)) {
            return null;
        }

        return max(0, (int) $nascita->diffInYears($ref));
    }

    public static function etaOperativa(?string $dataNascita, ?Carbon $reference = null): ?int
    {
        $etaCronologica = self::etaCronologica($dataNascita, $reference);

        if ($etaCronologica === null) {
            return null;
        }

        return max(1, $etaCronologica);
    }
}
