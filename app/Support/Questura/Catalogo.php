<?php

namespace App\Support\Questura;

final class Catalogo
{
    private static array $tables = [];

    public static function codice(string $table, string $value, ?string $province = null): ?string
    {
        $rows = self::$tables[$table] ??= self::load($table);
        $matches = array_filter($rows, fn ($row) =>
            (self::normalizza($row['Descrizione']) === self::normalizza($value) || $row['Codice'] === $value)
            && ($province === null || ($row['Provincia'] ?? null) === $province));
        $codes = array_unique(array_column($matches, 'Codice'));
        return count($codes) === 1 ? reset($codes) : null;
    }

    private static function load(string $table): array
    {
        if (!in_array($table, ['comuni', 'stati', 'documenti', 'tipi'], true)) {
            throw new \InvalidArgumentException('Catalogo Questura non valido.');
        }
        $path = base_path('reference/questura/'.$table.'.csv');
        $manifest = json_decode(file_get_contents(base_path('reference/questura/manifest.json')), true, 512, JSON_THROW_ON_ERROR);
        $source = array_values(array_filter($manifest, fn ($row) => $row['file'] === $table.'.csv'))[0] ?? null;
        if (!$source || !hash_equals($source['sha256'], hash_file('sha256', $path))) {
            throw new \RuntimeException('Integrità catalogo Questura non verificata.');
        }
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException('Catalogo ufficiale Questura non disponibile.');
        }
        try {
            $header = fgetcsv($handle);
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($header)) {
                    $rows[] = array_combine($header, $row);
                }
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    public static function normalizza(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtoupper($value, 'UTF-8')));
    }
}
