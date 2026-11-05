<?php

namespace App\Services;

use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use Illuminate\Support\Str;

/** Adattatore ISTAT: legge GEO e traduce nelle tabelle ufficiali, senza scritture. */
final class IstatCodifiche
{
    private static array $tables = [];

    public static function normalize(string $value): string
    {
        return trim((string) preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii($value))));
    }

    public function table(string $name): array
    {
        if (!isset(self::$tables[$name])) {
            $handle = fopen(base_path('reference/istat/ross1000-er/'.$name.'.txt'), 'rb');
            if (!$handle) {
                throw new \RuntimeException('Tabella ufficiale ISTAT non disponibile.');
            }
            $headers = fgetcsv($handle);
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                $row = array_map(fn ($s) => mb_convert_encoding($s, 'UTF-8', 'Windows-1252'), $row);
                $record = array_combine($headers, $row);
                $rows[$record['Codice']] = $record;
            }
            fclose($handle);
            self::$tables[$name] = $rows;
        }
        return self::$tables[$name];
    }

    public function country(mixed $value, bool $birth = false): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $official = $this->table('stati');
        if (preg_match('/^\d{9}$/D', $value)) {
            return isset($official[$value]) && ($birth || $official[$value]['DataFineVal'] === '') ? $value : null;
        }
        $geo = ctype_digit($value) ? GeoNazione::find((int) $value) : null;
        if (!$geo && !ctype_digit($value)) {
            $matches = GeoNazione::all()->filter(fn ($row) => in_array(self::normalize($value), [self::normalize($row->nome), self::normalize((string) $row->cittadinanza), self::normalize((string) $row->codice_iso2)], true));
            $geo = $matches->count() === 1 ? $matches->first() : null;
        }
        $name = self::normalize($geo?->nome ?? $value);
        $matches = array_filter($official, fn ($row) => self::normalize($row['Descrizione']) === $name && ($birth || $row['DataFineVal'] === ''));
        return count($matches) === 1 ? (string) array_key_first($matches) : null;
    }

    public function comune(mixed $value, mixed $province, bool $birth = false): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $official = $this->table('comuni');
        if (preg_match('/^\d{9}$/D', $value)) {
            $record = $official[$value] ?? null;
            return $record && ($birth || $record['DataFineVal'] === '') && $this->provinceMatches($record, $province) ? $value : null;
        }
        // I codici ISTAT a sei cifre hanno priorità rispetto agli ID interni.
        $geo = preg_match('/^\d{6}$/D', $value) ? GeoComune::where('codice_istat', $value)->first() : (ctype_digit($value) ? GeoComune::find((int) $value) : null);
        if ($geo) {
            if (!$this->provinceMatches(['Provincia' => $geo->provincia?->sigla], $province)) {
                return null;
            }
            $province = $geo->provincia?->sigla;
        }
        $name = self::normalize($geo?->nome ?? $value);
        $matches = array_filter($official, fn ($row) => self::normalize($row['Descrizione']) === $name && $row['DataFineVal'] === '' && $this->provinceMatches($row, $province));
        // Una località omonima o cessata non viene risolta arbitrariamente.
        return count($matches) === 1 ? (string) array_key_first($matches) : null;
    }

    private function provinceMatches(array $record, mixed $value): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }
        $geo = ctype_digit($value) ? GeoProvincia::find((int) $value) : null;
        $normalized = self::normalize($geo?->sigla ?? $value);
        if (strlen($normalized) !== 2) {
            $matches = GeoProvincia::all()->filter(fn ($p) => self::normalize($p->nome) === $normalized);
            $normalized = $matches->count() === 1 ? self::normalize($matches->first()->sigla) : $normalized;
        }
        return self::normalize((string) $record['Provincia']) === $normalized;
    }

    public function tipo(mixed $value): ?string
    {
        $value = trim((string) $value);
        foreach ($this->table('tipi') as $code => $record) {
            if ($value === (string) $code || self::normalize($value) === self::normalize($record['Descrizione'])) {
                return (string) $code;
            }
        }
        return null;
    }
}
