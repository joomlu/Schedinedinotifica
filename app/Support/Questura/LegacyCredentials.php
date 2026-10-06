<?php

namespace App\Support\Questura;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

final class LegacyCredentials
{
    public static function encrypted(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            Crypt::decryptString($value);
            return $value;
        } catch (DecryptException) {
            // Un involucro cifrato illeggibile non diventa mai una credenziale plaintext.
            $decoded = base64_decode($value, true);
            if (str_starts_with($value, 'eyJ') || ($decoded !== false && preg_match('/["\'](?:iv|value|mac|tag)["\']\s*:/', $decoded))) {
                throw new \RuntimeException('Migrazione credenziali Questura bloccata: valore cifrato ambiguo o non decifrabile. Verificare APP_KEY e backup senza sovrascrivere il valore.');
            }
            return Crypt::encryptString($value);
        }
    }
}
