<?php

namespace App\Services;

use App\Models\GeoNazione;
use App\Models\GeoRegione;
use App\Models\IstatMovimentoGiornaliero;
use App\Models\Schedina;
use App\Models\Struttura;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IstatTabellaAService
{
    public const TIPO_TURISMO = [
        'LEISURE' => 'Leisure / Vacanza',
        'BUSINESS' => 'Business / Lavoro',
        'GROUP' => 'Gruppo organizzato',
        'HEALTH' => 'Salute / Cura',
        'OTHER' => 'Altro motivo',
    ];

    public const MEZZO_TRASPORTO = [
        'AUTO' => 'Auto',
        'TRENO' => 'Treno',
        'AEREO' => 'Aereo',
        'BUS' => 'Bus',
        'NAVE' => 'Nave',
        'MOTO' => 'Moto',
        'BICI' => 'Bicicletta',
        'PIEDI' => 'A piedi',
        'OTHER' => 'Altro mezzo',
    ];

    public const CANALE_PRENOTAZIONE = [
        'DIRECT' => 'Diretta',
        'OTA' => 'OTA / Portale online',
        'AGENCY' => 'Agenzia viaggi',
        'PHONE' => 'Telefono',
        'EMAIL' => 'Email',
        'WALKIN' => 'Walk-in',
        'OTHER' => 'Altro canale',
    ];

    public const TITOLO_STUDIO = [
        'NONE' => 'Nessuno / Non indicato',
        'PRIMARY' => 'Scuola primaria',
        'SECONDARY' => 'Scuola secondaria',
        'DIPLOMA' => 'Diploma',
        'LAUREA' => 'Laurea',
        'MASTER' => 'Master / Dottorato',
    ];

    public function schedinePerPeriodo(int $strutturaId, Carbon $dal, Carbon $al): Collection
    {
        return Schedina::query()
            ->withoutGlobalScope('struttura')
            ->where('struttura_id', $strutturaId)
            ->where('circuito', 'schedina')
            ->where(function ($query) {
                $query->whereNull('istat_non_turista')->orWhere('istat_non_turista', false);
            })
            ->whereNotNull('arrive')
            ->whereNotNull('departure')
            ->whereDate('arrive', '<=', $al->toDateString())
            ->whereDate('departure', '>=', $dal->toDateString())
            ->orderBy('arrive')
            ->orderBy('id')
            ->get();
    }

    public function dailyRows(Struttura $struttura, Carbon $dal, Carbon $al): Collection
    {
        $schedine = $this->schedinePerPeriodo($struttura->id, $dal, $al);
        $overrides = IstatMovimentoGiornaliero::query()
            ->where('struttura_id', $struttura->id)
            ->whereBetween('giorno', [$dal->toDateString(), $al->toDateString()])
            ->get()
            ->keyBy(fn (IstatMovimentoGiornaliero $row) => $row->giorno->toDateString());

        $period = CarbonPeriod::create($dal->copy(), $al->copy());
        $rows = collect();

        foreach ($period as $date) {
            $key = $date->toDateString();
            $override = $overrides->get($key);
            $openDefault = $this->isOpenForDay($struttura, $date);

            $active = $schedine->filter(function (Schedina $schedina) use ($date) {
                try {
                    $arrive = Carbon::parse($schedina->arrive)->startOfDay();
                    $departure = Carbon::parse($schedina->departure)->startOfDay();
                } catch (\Throwable $e) {
                    return false;
                }

                return $arrive->lte($date) && $departure->gt($date);
            })->values();

            $arrivi = $schedine->filter(fn (Schedina $schedina) => $this->sameDate($schedina->arrive, $date))->values();
            $partenze = $schedine->filter(fn (Schedina $schedina) => $this->sameDate($schedina->departure, $date))->values();

            $base = [
                'giorno' => $key,
                'aperta' => $openDefault,
                'movimento_zero' => $openDefault && $arrivi->isEmpty() && $partenze->isEmpty() && $active->isEmpty(),
                'camere_disponibili' => max((int) ($struttura->camere_disponibili ?? 0), 0),
                'letti_disponibili' => max((int) ($struttura->letti_disponibili ?? 0), 0),
                'camere_occupate' => (int) $active->sum(fn (Schedina $schedina) => (int) ($schedina->room ?? 0)),
                'arrivi' => (int) $arrivi->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
                'partenze' => (int) $partenze->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
                'presenti' => (int) $active->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
                'presenti_italiani' => (int) $active->filter(fn (Schedina $schedina) => $this->isItalia($schedina->or_country))->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
                'presenti_stranieri' => (int) $active->reject(fn (Schedina $schedina) => $this->isItalia($schedina->or_country))->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
                'provenienze_nazioni' => $this->summarizeForeignCountries($active),
                'provenienze_regioni' => $this->summarizeItalianRegions($active),
                'schedine_ids' => $active->pluck('id')->merge($arrivi->pluck('id'))->merge($partenze->pluck('id'))->unique()->values()->all(),
                'manuale' => $override !== null,
                'note' => $override?->note,
            ];

            if ($override) {
                foreach (['aperta', 'movimento_zero', 'camere_disponibili', 'letti_disponibili', 'camere_occupate', 'arrivi', 'partenze', 'presenti'] as $field) {
                    if (!is_null($override->{$field})) {
                        $base[$field] = $override->{$field};
                    }
                }
            }

            $rows->push($base);
        }

        return $rows;
    }

    public function analysePeriodo(Struttura $struttura, Carbon $dal, Carbon $al): array
    {
        $rows = $this->dailyRows($struttura, $dal, $al);
        $schedine = $this->schedinePerPeriodo($struttura->id, $dal, $al);
        $errors = [];

        if (blank($struttura->istat_codice_struttura)) {
            $errors[] = 'Codice struttura Ross1000 mancante in Struttura.';
        }

        foreach ($schedine as $schedina) {
            $prefix = $schedina->scheda ?: ('Schedina #' . $schedina->id);
            if (blank($schedina->cant_people) || (int) $schedina->cant_people <= 0) {
                $errors[] = $prefix . ': quantità persone non valida.';
            }
            if (blank($schedina->room) || (int) $schedina->room <= 0) {
                $errors[] = $prefix . ': quantità camere non valida.';
            }
            if (blank($schedina->beds) || (int) $schedina->beds <= 0) {
                $errors[] = $prefix . ': quantità letti non valida.';
            }
            if (blank($schedina->or_country)) {
                $errors[] = $prefix . ': provenienza/residenza mancante.';
            }
            if ($this->originCode($schedina) === '') {
                $errors[] = $prefix . ': provenienza ISTAT non risolvibile dai dati geo.';
            }
        }

        return [
            'rows' => $rows,
            'schedine' => $schedine,
            'errors' => array_values(array_unique($errors)),
            'valida' => empty($errors),
            'totale_schedine' => $schedine->count(),
            'totale_arrivi' => (int) $rows->sum('arrivi'),
            'totale_presenze' => (int) $rows->sum('presenti'),
            'totale_partenze' => (int) $rows->sum('partenze'),
        ];
    }

    public function buildXml(Struttura $struttura, Carbon $dal, Carbon $al): string
    {
        $analysis = $this->analysePeriodo($struttura, $dal, $al);
        if (!$analysis['valida']) {
            throw ValidationException::withMessages([
                'istat_export' => 'Sono presenti dati obbligatori mancanti per Tavola A. Correggi le schedine o la struttura prima di generare l\'XML.',
            ]);
        }

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $root = $xml->createElement('movimenti');
        $root->appendChild($xml->createElement('codice', (string) $struttura->istat_codice_struttura));
        $root->appendChild($xml->createElement('prodotto', 'Ross1000'));
        $root->appendChild($xml->createElement('periodoDal', $dal->format('Y-m-d')));
        $root->appendChild($xml->createElement('periodoAl', $al->format('Y-m-d')));
        $root->appendChild($xml->createElement('strutturaDenominazione', (string) ($struttura->nome_struttura ?? 'Struttura')));
        $root->appendChild($xml->createElement('comune', (string) ($struttura->citta ?? '')));

        foreach ($analysis['rows'] as $row) {
            $movimento = $xml->createElement('movimento');
            $movimento->appendChild($xml->createElement('data', Carbon::parse($row['giorno'])->format('Ymd')));

            $strutturaNode = $xml->createElement('struttura');
            $strutturaNode->appendChild($xml->createElement('aperta', $row['aperta'] ? 'true' : 'false'));
            $strutturaNode->appendChild($xml->createElement('movimentozero', $row['movimento_zero'] ? 'true' : 'false'));
            $strutturaNode->appendChild($xml->createElement('cameredisponibili', (string) $row['camere_disponibili']));
            $strutturaNode->appendChild($xml->createElement('lettidisponibili', (string) $row['letti_disponibili']));
            $strutturaNode->appendChild($xml->createElement('camereoccupate', (string) $row['camere_occupate']));
            $strutturaNode->appendChild($xml->createElement('presenti', (string) $row['presenti']));
            $movimento->appendChild($strutturaNode);

            $arriviNode = $xml->createElement('arrivi');
            foreach ($this->schedineArrivoData($analysis['schedine'], Carbon::parse($row['giorno'])) as $schedina) {
                $arrivo = $xml->createElement('arrivo');
                $arrivo->appendChild($xml->createElement('scheda', (string) ($schedina->scheda ?? $schedina->id)));
                $arrivo->appendChild($xml->createElement('ospite', trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? ''))));
                $arrivo->appendChild($xml->createElement('tipoturismo', (string) $schedina->istat_tipo_turismo));
                $arrivo->appendChild($xml->createElement('mezzotrasporto', (string) $schedina->istat_mezzo_trasporto));
                $arrivo->appendChild($xml->createElement('canaleprenotazione', (string) ($schedina->istat_canale_prenotazione ?? '')));
                $arrivo->appendChild($xml->createElement('persone', (string) ((int) ($schedina->cant_people ?? 0))));
                $arrivo->appendChild($xml->createElement('camere', (string) ((int) ($schedina->room ?? 0))));
                $arrivo->appendChild($xml->createElement('letti', (string) ((int) ($schedina->beds ?? 0))));
                $arrivo->appendChild($xml->createElement('provenienza', $this->originCode($schedina)));
                $arriviNode->appendChild($arrivo);
            }
            $movimento->appendChild($arriviNode);

            $partenzeNode = $xml->createElement('partenze');
            foreach ($this->schedinePartenzaData($analysis['schedine'], Carbon::parse($row['giorno'])) as $schedina) {
                $partenza = $xml->createElement('partenza');
                $partenza->appendChild($xml->createElement('scheda', (string) ($schedina->scheda ?? $schedina->id)));
                $partenza->appendChild($xml->createElement('ospite', trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? ''))));
                $partenza->appendChild($xml->createElement('persone', (string) ((int) ($schedina->cant_people ?? 0))));
                $partenza->appendChild($xml->createElement('provenienza', $this->originCode($schedina)));
                $partenzeNode->appendChild($partenza);
            }
            $movimento->appendChild($partenzeNode);

            $root->appendChild($movimento);
        }

        $xml->appendChild($root);
        return $xml->saveXML() ?: '';
    }

    public function buildSoapEnvelope(Struttura $struttura, string $xml, string $mode): string
    {
        $escapedXml = htmlspecialchars($xml, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $username = $this->xmlSafe((string) $struttura->istat_username);
        $password = $this->xmlSafe((string) $struttura->istat_password);
        $modeValue = $this->xmlSafe($mode);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.checkinV2.ross1000.regione.emilia-romagna.it/">
  <soapenv:Header/>
  <soapenv:Body>
    <ws:inviaMovimentazione>
      <username>{$username}</username>
      <password>{$password}</password>
      <xml>{$escapedXml}</xml>
      <modalita>{$modeValue}</modalita>
    </ws:inviaMovimentazione>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    public function filename(Carbon $dal, Carbon $al): string
    {
        return 'tabella_a_' . $dal->format('Ym') . '.xml';
    }

    public function saveDailyOverrides(Struttura $struttura, array $rows, ?int $userId = null): void
    {
        foreach ($rows as $day => $payload) {
            $date = Carbon::parse($day)->toDateString();
            IstatMovimentoGiornaliero::query()->updateOrCreate(
                ['struttura_id' => $struttura->id, 'giorno' => $date],
                [
                    'aperta' => $this->nullableBool($payload['aperta'] ?? null),
                    'movimento_zero' => $this->nullableBool($payload['movimento_zero'] ?? null),
                    'camere_disponibili' => $this->nullableInt($payload['camere_disponibili'] ?? null),
                    'letti_disponibili' => $this->nullableInt($payload['letti_disponibili'] ?? null),
                    'camere_occupate' => $this->nullableInt($payload['camere_occupate'] ?? null),
                    'arrivi' => $this->nullableInt($payload['arrivi'] ?? null),
                    'partenze' => $this->nullableInt($payload['partenze'] ?? null),
                    'presenti' => $this->nullableInt($payload['presenti'] ?? null),
                    'note' => trim((string) ($payload['note'] ?? '')) ?: null,
                    'override_payload' => $payload,
                    'confermato_il' => now(),
                    'confermato_da' => $userId,
                ]
            );
        }
    }

    private function schedineArrivoData(Collection $schedine, Carbon $date): Collection
    {
        return $schedine->filter(fn (Schedina $schedina) => $this->sameDate($schedina->arrive, $date))->values();
    }

    private function schedinePartenzaData(Collection $schedine, Carbon $date): Collection
    {
        return $schedine->filter(fn (Schedina $schedina) => $this->sameDate($schedina->departure, $date))->values();
    }

    private function originCode(Schedina $schedina): string
    {
        $country = trim((string) ($schedina->or_country ?? ''));
        if ($country !== '' && !$this->isItalia($country)) {
            return (string) ($this->stateCodeFromCountry($country) ?? $country);
        }

        return (string) ($this->comuneCode($schedina->or_city) ?? $schedina->or_city ?? '');
    }

    private function isOpenForDay(Struttura $struttura, Carbon $date): bool
    {
        if (($struttura->tipo_apertura ?? 'Annuale') === 'Annuale') {
            return true;
        }

        if (blank($struttura->data_apertura) || blank($struttura->data_chiusura)) {
            return true;
        }

        try {
            $apertura = Carbon::parse($struttura->data_apertura)->startOfDay();
            $chiusura = Carbon::parse($struttura->data_chiusura)->startOfDay();
        } catch (\Throwable $e) {
            return true;
        }

        if ($chiusura->greaterThanOrEqualTo($apertura)) {
            return $date->betweenIncluded($apertura, $chiusura);
        }

        $currentYearOpen = $apertura->copy()->year($date->year);
        $currentYearClose = $chiusura->copy()->year($date->year + 1);
        return $date->betweenIncluded($currentYearOpen, $currentYearClose);
    }

    private function sameDate(?string $value, Carbon $date): bool
    {
        try {
            return Carbon::parse($value)->isSameDay($date);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function nullableInt($value): ?int
    {
        return ($value === '' || $value === null) ? null : max((int) $value, 0);
    }

    private function nullableBool($value): ?bool
    {
        if ($value === '' || $value === null) {
            return null;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? null;
    }

    private function comuneCode($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $comune = \App\Models\GeoComune::query()->find((int) $value);
            if ($comune) {
                return (string) $comune->codice_istat;
            }
            $comune = \App\Models\GeoComune::query()->where('codice_istat', (string) $value)->first();
            return $comune?->codice_istat;
        }
        $normalized = $this->normalizeLookup((string) $value);
        $comune = \App\Models\GeoComune::query()->get(['codice_istat', 'nome'])->first(function ($row) use ($normalized) {
            return $this->normalizeLookup($row->nome) === $normalized || $this->normalizeLookup((string) $row->codice_istat) === $normalized;
        });
        return $comune?->codice_istat;
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
            return $this->normalizeLookup($row->nome) === $normalized || $this->normalizeLookup((string) $row->cittadinanza) === $normalized;
        });
        return $nation ? (string) $nation->id : null;
    }

    private function countryLabel($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        static $nationById = [];

        if (is_numeric($value)) {
            $key = (string) $value;
            if (!array_key_exists($key, $nationById)) {
                $nationById[$key] = GeoNazione::query()->find((int) $value);
            }
            return $nationById[$key]?->nome ?: $key;
        }

        return trim((string) $value) ?: null;
    }

    private function regionLabel($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        static $regionById = [];

        if (is_numeric($value)) {
            $key = (string) $value;
            if (!array_key_exists($key, $regionById)) {
                $regionById[$key] = GeoRegione::query()->find((int) $value);
            }
            return $regionById[$key]?->nome ?: $key;
        }

        return trim((string) $value) ?: null;
    }

    private function summarizeForeignCountries(Collection $schedine): string
    {
        $items = $schedine
            ->reject(fn (Schedina $schedina) => $this->isItalia($schedina->or_country))
            ->groupBy(fn (Schedina $schedina) => $this->countryLabel($schedina->or_country) ?: 'Estero non indicato')
            ->map(fn (Collection $group, string $label) => $label . ' ' . $group->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)))
            ->values()
            ->take(4);

        return $items->isEmpty() ? '—' : $items->implode(' · ');
    }

    private function summarizeItalianRegions(Collection $schedine): string
    {
        $items = $schedine
            ->filter(fn (Schedina $schedina) => $this->isItalia($schedina->or_country))
            ->groupBy(fn (Schedina $schedina) => $this->regionLabel($schedina->or_region) ?: 'Italia non indicata')
            ->map(fn (Collection $group, string $label) => $label . ' ' . $group->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)))
            ->values()
            ->take(4);

        return $items->isEmpty() ? '—' : $items->implode(' · ');
    }

    private function isItalia($value): bool
    {
        $stateCode = $this->stateCodeFromCountry($value);
        return (string) $stateCode === '106';
    }

    private function normalizeLookup(string $value): string
    {
        return trim((string) preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii($value))));
    }

    private function xmlSafe(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
