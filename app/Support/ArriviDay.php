<?php

namespace App\Support;

use Carbon\Carbon;

/** Giorno operativo: nessun campo fuso per struttura esiste nello schema corrente. */
final class ArriviDay
{
    public static function today(): string
    {
        return Carbon::now(config('app.timezone'))->toDateString();
    }

    public static function clock(): array
    {
        $now = Carbon::now(config('app.timezone'));
        return ['today' => $now->toDateString(), 'timezone' => config('app.timezone'),
            'epoch_ms' => $now->getTimestampMs(), 'next_day_ms' => $now->copy()->addDay()->startOfDay()->getTimestampMs()];
    }
}
