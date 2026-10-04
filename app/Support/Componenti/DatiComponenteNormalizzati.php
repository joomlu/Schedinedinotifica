<?php

namespace App\Support\Componenti;

use App\Models\GeoNazione;
use Carbon\Carbon;

final class DatiComponenteNormalizzati
{
    public const STATO_COMPLETO = 'COMPLETO';
    public const STATO_DA_VERIFICARE = 'DA_VERIFICARE';
    public const STATO_DA_COMPLETARE = 'DA_COMPLETARE';
    public const STATO_NON_IMPORTABILE = 'NON_IMPORTABILE';

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

    private static function etichettaCampo(string $campo): string
    {
        return match ($campo) {
            'name' => 'Nome',
            'surname' => 'Cognome',
            'sex' => 'Sesso',
            'city_nac' => 'Cittadinanza',
            'country_nac' => 'Nazione nascita',
            'date_nac' => 'Data di nascita',
            'province_nac' => 'Provincia nascita',
            'comune_nac' => 'Comune nascita',
            default => ucfirst(str_replace('_', ' ', $campo)),
        };
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

    public static function classificaRiga(array $row): array
    {
        $dato = self::daArray($row);
        $dati = $dato->toArray();
        $status = self::STATO_COMPLETO;
        $messaggio = 'Componente completo.';
        $campo = null;
        $proposta = [];
        $motivazione = null;

        if ($dato->eVuoto()) {
            return [
                'status' => self::STATO_NON_IMPORTABILE,
                'messaggio' => 'Riga vuota o priva di dati identificativi.',
                'campo' => '_struct',
                'proposta' => [],
                'motivazione' => 'riga_vuota',
                'metadata' => [
                    '_review_status' => self::STATO_NON_IMPORTABILE,
                    '_review_message' => 'Riga vuota o priva di dati identificativi.',
                    '_review_field' => '_struct',
                    '_review_requires_confirmation' => false,
                    '_review_proposed_value' => null,
                    '_review_original_value' => null,
                    '_review_normalized_value' => null,
                    '_review_confirmed' => false,
                ],
            ];
        }

        foreach (['name', 'surname'] as $campoObbligatorio) {
            if (trim((string) ($dati[$campoObbligatorio] ?? '')) === '') {
                $status = self::STATO_DA_COMPLETARE;
                $campo = $campoObbligatorio;
                $messaggio = 'Dati personali mancanti: ' . self::etichettaCampo($campoObbligatorio) . '.';
                $motivazione = 'dato_obbligatorio_mancante';
            }
        }

        if ($status === self::STATO_COMPLETO && isset($dati['sex']) && trim((string) $dati['sex']) !== '' && !in_array(strtoupper((string) $dati['sex']), ['M', 'F'], true)) {
            $status = self::STATO_DA_COMPLETARE;
            $campo = 'sex';
            $messaggio = 'Sesso non valido. Inserire M o F.';
            $motivazione = 'sesso_invalido';
        }

        if ($status === self::STATO_COMPLETO && trim((string) ($dati['country_nac'] ?? '')) === '') {
            $status = self::STATO_DA_COMPLETARE;
            $campo = 'country_nac';
            $messaggio = 'Nazione di nascita mancante.';
            $motivazione = 'nazione_nascita_mancante';
        }

        if ($status === self::STATO_COMPLETO && trim((string) ($dati['date_nac'] ?? '')) === '') {
            $status = self::STATO_DA_COMPLETARE;
            $campo = 'date_nac';
            $messaggio = 'Data di nascita mancante.';
            $motivazione = 'data_nascita_mancante';
        }

        if (trim((string) ($dati['country_nac'] ?? '')) !== '') {
            $geo = self::resolveGeoNazione($dati['country_nac']);
            if ($geo === null) {
                return [
                    'status' => self::STATO_NON_IMPORTABILE,
                    'messaggio' => 'Nazione di nascita non riconosciuta dal database GEO.',
                    'campo' => 'country_nac',
                    'proposta' => [],
                    'motivazione' => 'nazione_non_riconosciuta',
                    'metadata' => [
                        '_review_status' => self::STATO_NON_IMPORTABILE,
                        '_review_message' => 'Nazione di nascita non riconosciuta dal database GEO.',
                        '_review_field' => 'country_nac',
                        '_review_requires_confirmation' => false,
                        '_review_proposed_value' => null,
                        '_review_original_value' => $dati['country_nac'] ?? null,
                        '_review_normalized_value' => null,
                        '_review_confirmed' => false,
                    ],
                ];
            }

            if (!empty($geo['is_italia'])) {
                if (trim((string) ($dati['province_nac'] ?? '')) === '' || trim((string) ($dati['comune_nac'] ?? '')) === '') {
                    if ($status === self::STATO_COMPLETO) {
                        $status = self::STATO_DA_COMPLETARE;
                        $campo = empty($dati['province_nac'] ?? '') ? 'province_nac' : 'comune_nac';
                        $messaggio = 'Provincia o comune di nascita mancanti per nascita in Italia.';
                        $motivazione = 'geo_italia_incompleto';
                    }
                }
            }
        }

        if ($status === self::STATO_COMPLETO && trim((string) ($dati['city_nac'] ?? '')) === '') {
            $countryValue = trim((string) ($dati['country_nac'] ?? ''));
            $geo = self::resolveGeoNazione($countryValue);
            if ($geo !== null && !empty($geo['cittadinanza'])) {
                $status = self::STATO_DA_VERIFICARE;
                $campo = 'city_nac';
                $proposta = ['city_nac' => (string) $geo['cittadinanza']];
                $messaggio = 'Cittadinanza proposta automaticamente dalla nazione di nascita. Verificare o confermare il valore.';
                $motivazione = 'cittadinanza_proposta';
            }
        }

        if ($status === self::STATO_COMPLETO) {
            $messaggio = 'Componente completo.';
        }

        return [
            'status' => $status,
            'messaggio' => $messaggio,
            'campo' => $campo,
            'proposta' => $proposta,
            'motivazione' => $motivazione,
            'metadata' => [
                '_review_status' => $status,
                '_review_message' => $messaggio,
                '_review_field' => $campo,
                '_review_requires_confirmation' => $status === self::STATO_DA_VERIFICARE,
                '_review_proposed_value' => $proposta['city_nac'] ?? null,
                '_review_original_value' => $row['city_nac'] ?? null,
                '_review_normalized_value' => $proposta['city_nac'] ?? ($row['city_nac'] ?? null),
                '_review_confirmed' => $status === self::STATO_COMPLETO,
            ],
        ];
    }

    public static function classificaRighe(array $rows): array
    {
        return array_map(fn (array $row) => self::classificaRiga($row), $rows);
    }

    public static function normalizzaData($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2})$/', $raw, $matches) === 1) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $shortYear = (int) $matches[3];

            $targetYear = 2000 + $shortYear;
            $fallbackYear = 1900 + $shortYear;

            if (!checkdate($month, $day, $targetYear) && !checkdate($month, $day, $fallbackYear)) {
                return null;
            }

            $candidate = Carbon::create($targetYear, $month, $day, 0, 0, 0);
            if ($candidate->isFuture()) {
                $candidate = Carbon::create($fallbackYear, $month, $day, 0, 0, 0);
            }

            return $candidate->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // try next format
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
