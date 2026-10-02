<?php

namespace App\Support\Componenti;

use Carbon\Carbon;

final class DatiComponenteNormalizzati
{
    /**
     * Campi richiesti dalla validazione attuale per una riga componente compilata.
     * cap_nac resta volutamente facoltativo in questa fase.
     */
    public const CAMPI_OBBLIGATORI = [
        'name' => 'Nome',
        'surname' => 'Cognome',
        'sex' => 'Sesso',
        'relationship' => 'Tipo alloggiato',
        'exent' => 'Esente',
        'city_nac' => 'Cittadinanza',
        'country_nac' => 'Nazione nascita',
        'regione_nac' => 'Regione nascita',
        'province_nac' => 'Provincia nascita',
        'comune_nac' => 'Città nascita',
        'city' => 'Città residenza',
        'date_nac' => 'Data di nascita',
        'country' => 'Nazione',
        'regione' => 'Regione',
        'province' => 'Provincia',
        'typeaway' => 'Tipo via',
        'address' => 'Strada',
        'number' => 'Num',
        'cap' => 'CAP',
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

        foreach (self::CAMPI_OBBLIGATORI as $key => $label) {
            if (($this->dati[$key] ?? null) === null || $this->dati[$key] === '') {
                $errori["componenti.$index.$key"] = 'Componente #' . ($index + 1) . ": campo obbligatorio ($label).";
            }
        }

        if ($validaSesso && isset($this->dati['sex']) && $this->dati['sex'] !== '' && !in_array($this->dati['sex'], self::SESSI_AMMESSI, true)) {
            $errori["componenti.$index.sex"] = 'Componente #' . ($index + 1) . ': valore non valido per Sesso (usa M o F).';
        }

        return $errori;
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
