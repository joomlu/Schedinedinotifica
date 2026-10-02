<?php

namespace App\Support\Componenti;

use App\Models\GeoNazione;
use Carbon\Carbon;

final class DatiComponenteNormalizzati
{
    private static $geoNazioneResolver = null;

    /**
     * Campi universali richiesti dal contratto V2 per una riga componente compilata.
     */
    public const CAMPI_OBBLIGATORI = [
        'name' => 'Nome',
        'surname' => 'Cognome',
        'sex' => 'Sesso',
        'relationship' => 'Tipo alloggiato',
        'exent' => 'Esente',
        'city_nac' => 'Cittadinanza',
        'country_nac' => 'Nazione nascita',
        'date_nac' => 'Data di nascita',
    ];

    /**
     * Campi richiesti solo per chi nasce in Italia.
     */
    public const CAMPI_OBBLIGATORI_NASCITA_ITALIA = [
        'province_nac' => 'Provincia nascita',
        'comune_nac' => 'Città nascita',
    ];

    private const CAMPI_RIGA_ATTIVA = ['name', 'surname', 'sex'];
    private const SESSI_AMMESSI = ['M', 'F'];

    private function __construct(private array $dati)
    {
    }

    public static function daArray(array $row): self
    {
        $normalizzati = [];
        foreach ($row as $key => $value) {
            $normalizzati[$key] = is_string($value) ? trim($value) : $value;
        }

        if (($normalizzati['country_nac'] ?? null) !== null && $normalizzati['country_nac'] !== '') {
            $nazione = self::resolveGeoNazione($normalizzati['country_nac']);
            if ($nazione !== null) {
                $normalizzati['country_nac'] = $nazione['nome'];
            }
        }

        return new self($normalizzati);
    }

    public function toArray(): array
    {
        return $this->dati;
    }

    public function eVuoto(): bool
    {
        foreach (self::CAMPI_RIGA_ATTIVA as $campo) {
            if (trim((string) ($this->dati[$campo] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public function erroriValidazione(int $index, bool $validaSesso = false): array
    {
        $errori = [];
        $nazioneNascita = $this->resolveCountryNac();

        foreach (self::CAMPI_OBBLIGATORI as $key => $label) {
            if (($this->dati[$key] ?? null) === null || $this->dati[$key] === '') {
                $errori["componenti.$index.$key"] = 'Componente #' . ($index + 1) . ": campo obbligatorio ($label).";
            }
        }

        if (($this->dati['country_nac'] ?? null) !== null && ($this->dati['country_nac'] ?? '') !== '' && $nazioneNascita === null) {
            $errori["componenti.$index.country_nac"] = 'Componente #' . ($index + 1) . ': nazione nascita non riconosciuta dai dati GEO.';
        }

        if ($nazioneNascita !== null && $nazioneNascita['is_italia']) {
            foreach (self::CAMPI_OBBLIGATORI_NASCITA_ITALIA as $key => $label) {
                if (($this->dati[$key] ?? null) === null || $this->dati[$key] === '') {
                    $errori["componenti.$index.$key"] = 'Componente #' . ($index + 1) . ": campo obbligatorio ($label).";
                }
            }
        }

        if ($validaSesso && isset($this->dati['sex']) && $this->dati['sex'] !== '' && !in_array($this->dati['sex'], self::SESSI_AMMESSI, true)) {
            $errori["componenti.$index.sex"] = 'Componente #' . ($index + 1) . ': valore non valido per Sesso (usa M o F).';
        }

        return $errori;
    }

    private function nascitaInItalia(): bool
    {
        return (bool) ($this->resolveCountryNac()['is_italia'] ?? false);
    }

    private function resolveCountryNac(): ?array
    {
        $value = $this->dati['country_nac'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return self::resolveGeoNazione($value);
    }

    public static function impostaGeoNazioneResolverPerTest(?callable $resolver): void
    {
        self::$geoNazioneResolver = $resolver;
    }

    private static function resolveGeoNazione($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $customResolver = self::$geoNazioneResolver;
        if (is_callable($customResolver)) {
            return self::normalizeResolvedGeoNazione($customResolver($value));
        }

        if (!self::puoInterrogareGeoNazioni()) {
            return null;
        }

        if (is_numeric($value)) {
            $nation = GeoNazione::query()->find((int) $value, ['id', 'nome', 'cittadinanza', 'codice_iso2', 'is_italia']);
            return self::normalizeResolvedGeoNazione($nation);
        }

        $normalized = self::normalizeLookup((string) $value);

        $nation = GeoNazione::query()
            ->get(['id', 'nome', 'cittadinanza', 'codice_iso2', 'is_italia'])
            ->first(function (GeoNazione $row) use ($normalized) {
                return self::normalizeLookup((string) $row->nome) === $normalized
                    || self::normalizeLookup((string) $row->cittadinanza) === $normalized
                    || self::normalizeLookup((string) $row->codice_iso2) === $normalized;
            });

        return self::normalizeResolvedGeoNazione($nation);
    }

    private static function normalizeResolvedGeoNazione($nation): ?array
    {
        if ($nation instanceof GeoNazione) {
            return [
                'id' => $nation->id,
                'nome' => (string) $nation->nome,
                'cittadinanza' => $nation->cittadinanza !== null ? (string) $nation->cittadinanza : null,
                'codice_iso2' => $nation->codice_iso2 !== null ? (string) $nation->codice_iso2 : null,
                'is_italia' => (bool) $nation->is_italia,
            ];
        }

        if (!is_array($nation)) {
            return null;
        }

        if (!array_key_exists('nome', $nation) || !array_key_exists('is_italia', $nation)) {
            return null;
        }

        return [
            'id' => $nation['id'] ?? null,
            'nome' => (string) $nation['nome'],
            'cittadinanza' => array_key_exists('cittadinanza', $nation) && $nation['cittadinanza'] !== null ? (string) $nation['cittadinanza'] : null,
            'codice_iso2' => array_key_exists('codice_iso2', $nation) && $nation['codice_iso2'] !== null ? (string) $nation['codice_iso2'] : null,
            'is_italia' => (bool) $nation['is_italia'],
        ];
    }

    private static function puoInterrogareGeoNazioni(): bool
    {
        return class_exists(GeoNazione::class) && GeoNazione::getConnectionResolver() !== null;
    }

    private static function normalizeLookup(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Z0-9]+/', ' ', (string) $value);

        return trim((string) $value);
    }

    public static function normalizzaRighe(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => self::daArray((array) $row))
            ->filter(fn (self $row) => !$row->eVuoto())
            ->map(fn (self $row) => $row->toArray())
            ->values()
            ->all();
    }

    public static function validaRighe(array $rows, bool $validaSesso = false): array
    {
        $errori = [];

        foreach ($rows as $index => $row) {
            $errori = array_merge(
                $errori,
                self::daArray((array) $row)->erroriValidazione($index, $validaSesso)
            );
        }

        return $errori;
    }

    public static function normalizzaData($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // try next format
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
