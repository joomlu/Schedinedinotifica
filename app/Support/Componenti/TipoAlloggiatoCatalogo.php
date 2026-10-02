<?php

namespace App\Support\Componenti;

use App\Models\TipoAlloggiato;
use RuntimeException;

final class TipoAlloggiatoCatalogo
{
    private const CODICI_CAPO_SCHEDINA = [16, 17, 18];
    private const CODICI_COMPONENTE = [19, 20];
    private const CODICE_DEFAULT_COMPONENTE = 20;

    private function __construct()
    {
    }

    public static function opzioniCapoSchedina(?callable $resolver = null): array
    {
        return self::filtraPerCodice(self::catalogo($resolver), self::CODICI_CAPO_SCHEDINA);
    }

    public static function opzioniComponente(?callable $resolver = null): array
    {
        return self::filtraPerCodice(self::catalogo($resolver), self::CODICI_COMPONENTE);
    }

    public static function voceDefaultComponente(?callable $resolver = null): array
    {
        foreach (self::catalogo($resolver) as $row) {
            if (self::codiceNormalizzato($row['codice'] ?? null) === self::CODICE_DEFAULT_COMPONENTE) {
                return $row;
            }
        }

        throw new RuntimeException('Catalogo TipoAlloggiato incompleto: codice 20 (MEMBRO GRUPPO) non disponibile.');
    }

    public static function valoreCompatibileConOpzione($value, array $opzione): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return false;
        }

        $codiceOpzione = self::codiceNormalizzato($opzione['codice'] ?? null);
        if ($codiceOpzione !== null && ctype_digit($raw) && (int) $raw === $codiceOpzione) {
            return true;
        }

        return self::normalizeLookup($raw) === self::normalizeLookup((string) ($opzione['descrizione'] ?? ''));
    }

    private static function filtraPerCodice(array $catalogo, array $allowedCodes): array
    {
        $allowed = array_fill_keys($allowedCodes, true);
        $out = [];

        foreach ($catalogo as $row) {
            $codice = self::codiceNormalizzato($row['codice'] ?? null);
            if ($codice === null || !isset($allowed[$codice])) {
                continue;
            }

            $descrizione = (string) ($row['descrizione'] ?? '');
            if ($descrizione === '') {
                continue;
            }

            $out[] = [
                'codice' => (string) $codice,
                'descrizione' => $descrizione,
            ];
        }

        return $out;
    }

    private static function catalogo(?callable $resolver = null): array
    {
        if (is_callable($resolver)) {
            return self::normalizzaCatalogo((array) $resolver());
        }

        if (!self::puoInterrogareCatalogo()) {
            return [];
        }

        $rows = TipoAlloggiato::query()
            ->orderBy('codice')
            ->get(['codice', 'descrizione'])
            ->map(fn (TipoAlloggiato $row) => [
                'codice' => (string) $row->codice,
                'descrizione' => (string) $row->descrizione,
            ])
            ->all();

        return self::normalizzaCatalogo($rows);
    }

    private static function normalizzaCatalogo(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $descrizione = trim((string) ($row['descrizione'] ?? ''));
            $codice = self::codiceNormalizzato($row['codice'] ?? null);
            if ($descrizione === '' || $codice === null) {
                continue;
            }

            $normalized[] = [
                'codice' => (string) $codice,
                'descrizione' => $descrizione,
            ];
        }

        return $normalized;
    }

    private static function puoInterrogareCatalogo(): bool
    {
        return class_exists(TipoAlloggiato::class)
            && TipoAlloggiato::getConnectionResolver() !== null;
    }

    private static function normalizeLookup(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Z0-9]+/', ' ', (string) $value);

        return trim((string) $value);
    }

    private static function codiceNormalizzato($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        return (int) $raw;
    }
}
