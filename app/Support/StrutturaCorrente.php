<?php

namespace App\Support;

class StrutturaCorrente
{
    protected static ?int $cachedId = null;

    public static function getId(): ?int
    {
        if (self::$cachedId !== null) {
            return self::$cachedId;
        }

        if (app()->runningInConsole()) {
            return null;
        }

        try {
            $value = session()->get('struttura_corrente_id');
            return $value !== null ? (int) $value : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function setId(?int $id): void
    {
        self::$cachedId = $id;

        if (app()->runningInConsole()) {
            return;
        }

        try {
            session()->put('struttura_corrente_id', $id);
        } catch (\Throwable $e) {
            // ignore session errors in contexts without session
        }
    }

    public static function clear(): void
    {
        self::setId(null);
    }
}
