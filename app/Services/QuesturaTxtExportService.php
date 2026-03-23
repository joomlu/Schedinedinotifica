<?php

namespace App\Services;

use App\Models\Componenti;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\Schedina;
use App\Models\TipoAlloggiato;
use App\Models\TipoDocumento;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuesturaTxtExportService
{
    public function schedinePerPeriodo(int $strutturaId, Carbon $dal, Carbon $al): Collection
    {
        return Schedina::query()
            ->withoutGlobalScope('struttura')
            ->with(['componenti' => fn ($query) => $query->withoutGlobalScope('struttura')])
            ->where('struttura_id', $strutturaId)
            ->where('circuito', 'schedina')
            ->whereDate('arrive', '>=', $dal->toDateString())
            ->whereDate('arrive', '<=', $al->toDateString())
            ->orderBy('arrive')
            ->orderBy('id')
            ->get();
    }

    public function analizzaSchedine(Collection $schedine): Collection
    {
        return $schedine->map(function (Schedina $schedina) {
            $validation = $this->validateSchedina($schedina);

            return [
                'schedina' => $schedina,
                'persone' => 1 + $schedina->componenti->count(),
                'componenti' => $schedina->componenti->count(),
                'valida' => empty($validation['errors']),
                'errors' => $validation['errors'],
                'righe' => $validation['rows'],
            ];
        });
    }

    public function buildTxt(Collection $analisi): string
    {
        $invalid = $analisi->filter(fn (array $row) => !$row['valida']);
        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'questura_export' => 'Sono presenti schedine non esportabili per Questura. Correggi i dati obbligatori prima del download.',
            ]);
        }

        $lines = $analisi
            ->flatMap(fn (array $item) => $item['righe'])
            ->values()
            ->all();

        return implode("\r\n", $lines);
    }

    public function buildTxtPerSchedina(Schedina $schedina): string
    {
        $analysis = $this->analizzaSchedine(collect([$schedina]))->first();
        if (!$analysis['valida']) {
            throw ValidationException::withMessages([
                'questura_export' => 'La schedina selezionata non è esportabile per Questura. Correggi i dati obbligatori prima del download.',
            ]);
        }

        return implode("\r\n", $analysis['righe']);
    }

    public function filename(Carbon $dal, Carbon $al): string
    {
        if ($dal->isSameDay($al)) {
            return 'questura_' . $dal->format('Ymd') . '.txt';
        }

        return 'questura_' . $dal->format('Ymd') . '_' . $al->format('Ymd') . '.txt';
    }

    public function filenamePerSchedina(Schedina $schedina): string
    {
        $code = preg_replace('/[^A-Z0-9\-]+/i', '_', (string) ($schedina->scheda ?: 'schedina'));
        return 'questura_' . $code . '.txt';
    }

    private function validateSchedina(Schedina $schedina): array
    {
        $errors = [];
        $rows = [];

        [$capoErrors, $capoRow] = $this->buildCapoRow($schedina);
        $errors = array_merge($errors, $capoErrors);
        if ($capoRow !== null) {
            $rows[] = $capoRow;
        }

        foreach ($schedina->componenti as $index => $componente) {
            [$componentErrors, $componentRow] = $this->buildComponenteRow($schedina, $componente, $index);
            $errors = array_merge($errors, $componentErrors);
            if ($componentRow !== null) {
                $rows[] = $componentRow;
            }
        }

        return [
            'errors' => $errors,
            'rows' => $rows,
        ];
    }

    private function buildCapoRow(Schedina $schedina): array
    {
        $errors = [];

        $tipoAlloggiato = $this->tipoAlloggiatoCode($schedina->relationship);
        if ($tipoAlloggiato === null || !in_array($tipoAlloggiato, ['16', '17', '18'], true)) {
            $errors[] = 'Tipo alloggiato ospite non valido per Questura.';
        }

        $arrivo = $this->formatDate($schedina->arrive);
        if ($arrivo === null) {
            $errors[] = 'Data arrivo mancante o non valida.';
        }

        $permanenza = $this->permanenza($schedina->arrive, $schedina->departure);
        if ($permanenza === null) {
            $errors[] = 'Periodo soggiorno non valido.';
        } elseif ($permanenza > 30) {
            $errors[] = 'Permanenza superiore a 30 giorni: non esportabile in un solo record Questura.';
        }

        $cognome = $this->sanitizeText($schedina->surname, 50);
        if ($cognome === '') {
            $errors[] = 'Cognome ospite mancante.';
        }

        $nome = $this->sanitizeText($schedina->name, 30);
        if ($nome === '') {
            $errors[] = 'Nome ospite mancante.';
        }

        $sesso = $this->sexCode($schedina->sex);
        if ($sesso === null) {
            $errors[] = 'Sesso ospite non valido.';
        }

        $dataNascita = $this->formatDate($schedina->oa_date_nac);
        if ($dataNascita === null) {
            $errors[] = 'Data di nascita ospite mancante o non valida.';
        }

        [$comuneNascita, $provinciaNascita, $statoNascita, $birthErrors] = $this->birthPlaceFields(
            $schedina->oa_country,
            $schedina->oa_prov,
            $schedina->oa_city
        );
        $errors = array_merge($errors, $birthErrors);

        $cittadinanza = $this->stateCodeFromCitizenship($schedina->oa_city_nac);
        if ($cittadinanza === null) {
            $errors[] = 'Cittadinanza ospite non mappabile ai codici ufficiali.';
        }

        $tipoDocumento = $this->tipoDocumentoCode($schedina->or_doctype);
        if ($tipoDocumento === null) {
            $errors[] = 'Tipo documento ospite non mappabile ai codici ufficiali.';
        }

        $numeroDocumento = $this->sanitizeText($schedina->or_doc, 20);
        if ($numeroDocumento === '') {
            $errors[] = 'Numero documento ospite mancante.';
        }

        $luogoRilascio = $this->documentReleasePlaceCode($schedina->or_published_country, $schedina->or_published_city);
        if ($luogoRilascio === null) {
            $errors[] = 'Luogo rilascio documento non mappabile ai codici ufficiali.';
        }

        if (!empty($errors)) {
            return [$this->prefixErrors($schedina, $errors), null];
        }

        return [[], $this->composeRecord([
            $tipoAlloggiato,
            $arrivo,
            str_pad((string) $permanenza, 2, '0', STR_PAD_LEFT),
            $cognome,
            $nome,
            $sesso,
            $dataNascita,
            $comuneNascita,
            $provinciaNascita,
            $statoNascita,
            $cittadinanza,
            $tipoDocumento,
            $numeroDocumento,
            $luogoRilascio,
        ])];
    }

    private function buildComponenteRow(Schedina $schedina, Componenti $componente, int $index): array
    {
        $errors = [];

        $tipoAlloggiato = $this->tipoAlloggiatoCode($componente->relationship);
        if ($tipoAlloggiato === null || !in_array($tipoAlloggiato, ['19', '20'], true)) {
            $errors[] = 'Tipo alloggiato componente non valido per Questura.';
        }

        $arrivo = $this->formatDate($schedina->arrive);
        $permanenza = $this->permanenza($schedina->arrive, $schedina->departure);
        if ($arrivo === null || $permanenza === null) {
            $errors[] = 'Periodo soggiorno schedina non valido per il componente.';
        } elseif ($permanenza > 30) {
            $errors[] = 'Permanenza superiore a 30 giorni: componente non esportabile in un solo record Questura.';
        }

        $cognome = $this->sanitizeText($componente->surname, 50);
        if ($cognome === '') {
            $errors[] = 'Cognome componente mancante.';
        }

        $nome = $this->sanitizeText($componente->name, 30);
        if ($nome === '') {
            $errors[] = 'Nome componente mancante.';
        }

        $sesso = $this->sexCode($componente->sex);
        if ($sesso === null) {
            $errors[] = 'Sesso componente non valido.';
        }

        $dataNascita = $this->formatDate($componente->date_nac);
        if ($dataNascita === null) {
            $errors[] = 'Data di nascita componente mancante o non valida.';
        }

        [$comuneNascita, $provinciaNascita, $statoNascita, $birthErrors] = $this->birthPlaceFields(
            $componente->country_nac,
            $componente->province_nac,
            $componente->comune_nac
        );
        $errors = array_merge($errors, $birthErrors);

        $cittadinanza = $this->stateCodeFromCitizenship($componente->city_nac);
        if ($cittadinanza === null) {
            $errors[] = 'Cittadinanza componente non mappabile ai codici ufficiali.';
        }

        if (!empty($errors)) {
            return [$this->prefixErrors($schedina, $errors, $index + 1), null];
        }

        return [[], $this->composeRecord([
            $tipoAlloggiato,
            $arrivo,
            str_pad((string) $permanenza, 2, '0', STR_PAD_LEFT),
            $cognome,
            $nome,
            $sesso,
            $dataNascita,
            $comuneNascita,
            $provinciaNascita,
            $statoNascita,
            $cittadinanza,
            str_repeat(' ', 5),
            str_repeat(' ', 20),
            str_repeat(' ', 9),
        ])];
    }

    private function birthPlaceFields($countryValue, $provValue, $cityValue): array
    {
        $errors = [];
        $stateCode = $this->stateCodeFromCountry($countryValue);
        if ($stateCode === null) {
            return [str_repeat(' ', 9), '  ', str_repeat(' ', 9), ['Stato nascita non mappabile ai codici ufficiali.']];
        }

        if ($this->isItalia($countryValue)) {
            $comuneCode = $this->comuneCode($cityValue);
            $provSigla = $this->provinciaSigla($provValue);

            if ($comuneCode === null) {
                $errors[] = 'Comune nascita non mappabile ai codici ufficiali.';
            }
            if ($provSigla === null) {
                $errors[] = 'Provincia nascita non mappabile ai codici ufficiali.';
            }

            return [
                $this->padRight($comuneCode ?? '', 9),
                $this->padRight($provSigla ?? '', 2),
                $this->padRight($stateCode, 9),
                $errors,
            ];
        }

        return [
            str_repeat(' ', 9),
            '  ',
            $this->padRight($stateCode, 9),
            [],
        ];
    }

    private function documentReleasePlaceCode($countryValue, $cityValue): ?string
    {
        if ($this->isItalia($countryValue)) {
            $comuneCode = $this->comuneCode($cityValue);
            return $comuneCode ? $this->padRight($comuneCode, 9) : null;
        }

        $stateCode = $this->stateCodeFromCountry($countryValue);
        return $stateCode ? $this->padRight($stateCode, 9) : null;
    }

    private function tipoAlloggiatoCode(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = $this->normalizeLookup($value);
        $record = TipoAlloggiato::query()->get(['codice', 'descrizione'])->first(function (TipoAlloggiato $row) use ($normalized) {
            return $this->normalizeLookup($row->descrizione) === $normalized
                || $this->normalizeLookup($row->codice) === $normalized;
        });

        return $record?->codice ? str_pad((string) $record->codice, 2, '0', STR_PAD_LEFT) : null;
    }

    private function tipoDocumentoCode(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = $this->normalizeLookup($value);
        $record = TipoDocumento::query()->get(['codice', 'descrizione'])->first(function (TipoDocumento $row) use ($normalized) {
            return $this->normalizeLookup($row->descrizione) === $normalized
                || $this->normalizeLookup($row->codice) === $normalized;
        });

        return $record?->codice ? $this->padRight($record->codice, 5) : null;
    }

    private function stateCodeFromCountry($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $nation = GeoNazione::query()->find((int) $value);
            return $nation ? (string) $nation->id : null;
        }

        $normalized = $this->normalizeLookup((string) $value);
        $nation = GeoNazione::query()->get(['id', 'nome', 'cittadinanza'])->first(function (GeoNazione $row) use ($normalized) {
            return $this->normalizeLookup($row->nome) === $normalized
                || $this->normalizeLookup((string) $row->cittadinanza) === $normalized;
        });

        return $nation ? (string) $nation->id : null;
    }

    private function stateCodeFromCitizenship(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = $this->normalizeLookup($value);
        $nation = GeoNazione::query()->get(['id', 'nome', 'cittadinanza'])->first(function (GeoNazione $row) use ($normalized) {
            return $this->normalizeLookup((string) $row->cittadinanza) === $normalized
                || $this->normalizeLookup($row->nome) === $normalized;
        });

        return $nation ? $this->padRight((string) $nation->id, 9) : null;
    }

    private function comuneCode($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $comune = GeoComune::query()->find((int) $value);
            if ($comune) {
                return (string) $comune->codice_istat;
            }

            $comune = GeoComune::query()->where('codice_istat', (string) $value)->first();
            return $comune?->codice_istat;
        }

        $normalized = $this->normalizeLookup((string) $value);
        $comune = GeoComune::query()->get(['codice_istat', 'nome'])->first(function (GeoComune $row) use ($normalized) {
            return $this->normalizeLookup($row->nome) === $normalized
                || $this->normalizeLookup((string) $row->codice_istat) === $normalized;
        });

        return $comune?->codice_istat;
    }

    private function provinciaSigla($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $provincia = GeoProvincia::query()->find((int) $value);
            return $provincia?->sigla;
        }

        $normalized = $this->normalizeLookup((string) $value);
        $provincia = GeoProvincia::query()->get(['sigla', 'nome'])->first(function (GeoProvincia $row) use ($normalized) {
            return $this->normalizeLookup($row->sigla) === $normalized
                || $this->normalizeLookup($row->nome) === $normalized;
        });

        return $provincia?->sigla;
    }

    private function sexCode(?string $value): ?string
    {
        return match (strtoupper(trim((string) $value))) {
            'M' => '1',
            'F' => '2',
            default => null,
        };
    }

    private function permanenza(?string $arrive, ?string $departure): ?int
    {
        try {
            $arrivo = Carbon::parse($arrive);
            $partenza = Carbon::parse($departure);
        } catch (\Throwable $e) {
            return null;
        }

        $days = $arrivo->diffInDays($partenza);
        return $days > 0 ? $days : null;
    }

    private function formatDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function composeRecord(array $fields): string
    {
        $line = implode('', $fields);
        return str_pad(substr($line, 0, 168), 168, ' ');
    }

    private function sanitizeText(?string $value, int $length): string
    {
        $value = strtoupper(trim((string) $value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Z0-9 \'\-\/\.]/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', (string) $value);

        return $this->padRight(trim((string) $value), $length);
    }

    private function padRight(string $value, int $length): string
    {
        return str_pad(substr($value, 0, $length), $length, ' ');
    }

    private function normalizeLookup(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Z0-9]+/', ' ', (string) $value);
        return trim((string) $value);
    }

    private function isItalia($value): bool
    {
        $stateCode = $this->stateCodeFromCountry($value);
        return $stateCode === '106';
    }

    private function prefixErrors(Schedina $schedina, array $errors, ?int $componentIndex = null): array
    {
        $prefix = $schedina->scheda ?: ('Schedina #' . $schedina->id);
        if ($componentIndex !== null) {
            $prefix .= ' - componente ' . $componentIndex;
        }

        return array_map(fn (string $error) => $prefix . ': ' . $error, $errors);
    }
}
