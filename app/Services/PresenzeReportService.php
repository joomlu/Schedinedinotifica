<?php

namespace App\Services;

use App\Models\GeoNazione;
use App\Models\Schedina;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PresenzeReportService
{
    private array $periodi = [];
    private ?Collection $nazioni = null;

    public function schedinePeriodo(int $strutturaId, Carbon $dal, Carbon $al): Collection
    {
        $key = $strutturaId.':'.$dal->toDateString().':'.$al->toDateString();
        foreach ($this->periodi as $periodo => $schedine) {
            [$id, $inizio, $fine] = explode(':', $periodo);
            if ((int) $id === $strutturaId && $inizio <= $dal->toDateString() && $fine >= $al->toDateString()) {
                return $schedine->filter(fn ($s) => $s->arrive <= $al->toDateString() && $s->departure >= $dal->toDateString())->values();
            }
        }
        return $this->periodi[$key] ??= Schedina::query()->effettive()
            ->withoutGlobalScope('struttura')
            ->with(['componenti' => fn ($query) => $query->withoutGlobalScope('struttura')->where('struttura_id', $strutturaId), 'camere' => fn ($query) => $query->withoutGlobalScope('struttura')->where('struttura_id', $strutturaId)])
            ->where('struttura_id', $strutturaId)
            ->where(function ($query) {
                $query->where('circuito', 'schedina')
                    ->orWhere(function ($inner) {
                        $inner->whereNull('circuito')->where('is_arrive', 0);
                    });
            })
            ->whereNotNull('arrive')
            ->whereNotNull('departure')
            ->whereDate('arrive', '<=', $al->toDateString())
            ->whereDate('departure', '>=', $dal->toDateString())
            ->orderBy('arrive')
            ->orderBy('id')
            ->get();
    }

    public function riepilogoAnno(int $strutturaId, int $anno): Collection
    {
        $rows = collect();

        $annuali = $this->schedinePeriodo($strutturaId, Carbon::create($anno, 1, 1), Carbon::create($anno, 12, 31));
        for ($mese = 1; $mese <= 12; $mese++) {
            $dal = Carbon::create($anno, $mese, 1)->startOfMonth();
            $al = $dal->copy()->endOfMonth()->startOfDay();
            $schedine = $annuali;

            $italiane = $this->buildCategoryMetrics($schedine, $dal, $al, true);
            $straniere = $this->buildCategoryMetrics($schedine, $dal, $al, false);
            $sconosciute = $this->buildCategoryMetrics($schedine, $dal, $al, null);

            $rows->push([
                'mese' => $mese,
                'mese_label' => $dal->locale('it')->translatedFormat('F Y'),
                'dal' => $dal,
                'al' => $al,
                'italiane' => $italiane,
                'straniere' => $straniere,
                'sconosciute' => $sconosciute,
                'totale' => [
                    'presenti' => $italiane['presenti'] + $straniere['presenti'] + $sconosciute['presenti'],
                    'arrivi' => $italiane['arrivi'] + $straniere['arrivi'] + $sconosciute['arrivi'],
                    'partenze' => $italiane['partenze'] + $straniere['partenze'] + $sconosciute['partenze'],
                    'presenze' => $italiane['presenze'] + $straniere['presenze'] + $sconosciute['presenze'],
                    'totale_esteri' => $straniere['presenze'],
                    'totale_italiani' => $italiane['presenze'],
                ],
            ]);
        }

        return $rows;
    }

    public function dettaglioPeriodo(int $strutturaId, Carbon $dal, Carbon $al, string $categoria = 'tutte'): array
    {
        $schedine = $this->schedinePeriodo($strutturaId, $dal, $al);
        $rows = $schedine->map(function (Schedina $schedina) use ($dal, $al, $categoria) {
            $arrivo = $this->safeDate($schedina->arrive);
            $partenza = $this->safeDate($schedina->departure);
            $componenti = $schedina->componenti ?? collect();
            $ospiti = $this->ospiti($schedina)->filter(fn ($o) => $categoria === 'tutte' || $this->categoria($o['country']) === match ($categoria) {
                'italiane' => 'Italiane', 'straniere' => 'Straniere', default => 'Non disponibile',
            });
            $persone = $ospiti->count();
            $presenze = $this->overlapNights($arrivo, $partenza, $dal, $al) * $persone;
            $adulti = $ospiti->filter(fn ($o) => ($eta = \App\Support\Anagrafica\EtaOperativa::etaOperativa($o['birth'], $arrivo)) !== null && $eta >= 18)->count();
            $minori = $ospiti->filter(fn ($o) => ($eta = \App\Support\Anagrafica\EtaOperativa::etaOperativa($o['birth'], $arrivo)) !== null && $eta < 18)->count();
            $origin = $this->originLabel($schedina->or_country);

            return [
                'scheda' => $schedina->scheda ?: ('#' . $schedina->id),
                'arrivo' => $arrivo,
                'partenza' => $partenza,
                'ospite' => trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? '')),
                'persone' => $persone,
                'componenti' => max(0, $persone - 1),
                'adulti' => $adulti,
                'minori' => $minori,
                'presenze' => $presenze,
                'provenienza' => $origin,
                'categoria' => $this->categoria($schedina->or_country),
                'notti_italiani' => $ospiti->filter(fn ($o) => $this->isItalia($o['country']) === true)->count() * $this->overlapNights($arrivo, $partenza, $dal, $al),
                'notti_stranieri' => $ospiti->filter(fn ($o) => $this->isItalia($o['country']) === false)->count() * $this->overlapNights($arrivo, $partenza, $dal, $al),
            ];
        })->filter(fn ($row) => $row['persone'] > 0)->values();

        return [
            'rows' => $rows,
            'totali' => [
                'schedine' => $rows->count(),
                'arrivi' => (int) $rows->filter(fn ($row) => $row['arrivo'] && $row['arrivo']->betweenIncluded($dal, $al))->sum('persone'),
                'partenze' => (int) $rows->filter(fn ($row) => $row['partenza'] && $row['partenza']->betweenIncluded($dal, $al))->sum('persone'),
                'presenze' => (int) $rows->sum('presenze'),
                'adulti' => (int) $rows->sum('adulti'),
                'minori' => (int) $rows->sum('minori'),
                'italiani' => (int) $rows->sum('notti_italiani'),
                'stranieri' => (int) $rows->sum('notti_stranieri'),
            ],
        ];
    }

    public function situazioneOggi(int $strutturaId, ?Carbon $giorno = null): array
    {
        $giorno = ($giorno ?: now())->copy()->startOfDay();
        $schedine = $this->schedinePeriodo($strutturaId, $giorno, $giorno);

        $presenti = $schedine->filter(function (Schedina $schedina) use ($giorno) {
            $arrivo = $this->safeDate($schedina->arrive);
            $partenza = $this->safeDate($schedina->departure);

            return $arrivo && $partenza && $arrivo->lte($giorno) && $partenza->gt($giorno);
        })->values();

        $arriviOggi = $schedine->filter(fn (Schedina $schedina) => $this->sameDay($schedina->arrive, $giorno))->values();
        $partenzeOggi = $schedine->filter(fn (Schedina $schedina) => $this->sameDay($schedina->departure, $giorno))->values();

        $adulti = 0;
        $minori = 0;
        foreach ($presenti as $schedina) {
            [$a, $m] = $this->splitAdultiMinori($schedina, $this->safeDate($schedina->arrive));
            $adulti += $a;
            $minori += $m;
        }

        return [
            'giorno' => $giorno,
            'presenti_totali' => (int) $presenti->sum(fn (Schedina $schedina) => $this->persone($schedina)),
            'adulti' => $adulti,
            'minori' => $minori,
            'italiani' => (int) $presenti->sum(fn ($s) => $this->ospiti($s)->filter(fn ($o) => $this->isItalia($o['country']) === true)->count()),
            'stranieri' => (int) $presenti->sum(fn ($s) => $this->ospiti($s)->filter(fn ($o) => $this->isItalia($o['country']) === false)->count()),
            'eta_non_disponibile' => (int) $presenti->sum(fn ($s) => $this->persone($s)) - $adulti - $minori,
            'sconosciuti' => (int) $presenti->sum(fn ($s) => $this->ospiti($s)->filter(fn ($o) => $this->isItalia($o['country']) === null)->count()),
            'arrivi_oggi' => (int) $arriviOggi->sum(fn ($s) => $this->persone($s)),
            'partenze_oggi' => (int) $partenzeOggi->sum(fn ($s) => $this->persone($s)),
            'camere_occupate' => $this->camereOccupate($presenti),
            'letti_occupati' => (int) $presenti->sum(fn (Schedina $schedina) => $this->persone($schedina)),
            'arrivi_rows' => $arriviOggi->map(fn (Schedina $schedina) => $this->movimentoRow($schedina))->values(),
            'partenze_rows' => $partenzeOggi->map(fn (Schedina $schedina) => $this->movimentoRow($schedina))->values(),
            'presenti_rows' => $presenti->map(fn (Schedina $schedina) => $this->movimentoRow($schedina))->values(),
        ];
    }

    public function movimentiPeriodo(int $strutturaId, Carbon $dal, Carbon $al): array
    {
        $schedine = $this->schedinePeriodo($strutturaId, $dal, $al);

        $arriviRows = $schedine
            ->filter(fn (Schedina $schedina) => $this->safeDate($schedina->arrive)?->betweenIncluded($dal, $al))
            ->map(fn (Schedina $schedina) => $this->movimentoRow($schedina))
            ->values();

        $partenzeRows = $schedine
            ->filter(fn (Schedina $schedina) => $this->safeDate($schedina->departure)?->betweenIncluded($dal, $al))
            ->map(fn (Schedina $schedina) => $this->movimentoRow($schedina))
            ->values();

        return [
            'arrivi' => $arriviRows,
            'partenze' => $partenzeRows,
            'totali' => [
                'schedine_arrivo' => $arriviRows->count(),
                'schedine_partenza' => $partenzeRows->count(),
                'persone_arrivo' => (int) $arriviRows->sum('persone'),
                'persone_partenza' => (int) $partenzeRows->sum('persone'),
            ],
        ];
    }

    public function occupazionePeriodo(int $strutturaId, Carbon $dal, Carbon $al, int $camereDisponibili, int $lettiDisponibili): array
    {
        $schedine = $this->schedinePeriodo($strutturaId, $dal, $al);
        $rows = collect();
        $struttura = Struttura::query()->findOrFail($strutturaId);

        for ($day = $dal->copy(); $day->lte($al); $day->addDay()) {
            $presenti = $schedine->filter(function (Schedina $schedina) use ($day) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);

                return $arrivo && $partenza && $arrivo->lte($day) && $partenza->gt($day);
            });

            $aperta = ($struttura->tipo_apertura ?? 'Annuale') === 'Annuale' || ($this->safeDate($struttura->data_apertura) && $this->safeDate($struttura->data_chiusura) && $day->betweenIncluded($this->safeDate($struttura->data_apertura), $this->safeDate($struttura->data_chiusura)));
            $camereGiorno = $aperta ? $camereDisponibili : 0;
            $lettiGiorno = $aperta ? $lettiDisponibili : 0;
            $camereOccupate = $this->camereOccupate($presenti);
            $lettiOccupati = (int) $presenti->sum(fn (Schedina $schedina) => $this->persone($schedina));
            $personePresenti = (int) $presenti->sum(fn (Schedina $schedina) => $this->persone($schedina));

            $rows->push([
                'giorno' => $day->copy(),
                'incoerente' => $camereOccupate > $camereGiorno || $personePresenti > $lettiGiorno,
                'camere_disponibili' => $camereGiorno,
                'camere_occupate' => $camereOccupate,
                'camere_libere' => max(0, $camereGiorno - $camereOccupate),
                'letti_disponibili' => $lettiGiorno,
                'letti_occupati' => $lettiOccupati,
                'letti_liberi' => max(0, $lettiGiorno - $lettiOccupati),
                'persone_presenti' => $personePresenti,
                'occupazione_camere' => $camereGiorno > 0 ? round(($camereOccupate / $camereGiorno) * 100, 1) : null,
                'occupazione_letti' => $lettiGiorno > 0 ? round(($lettiOccupati / $lettiGiorno) * 100, 1) : null,
            ]);
        }

        return [
            'rows' => $rows,
            'totali' => [
                'camere_notti_occupate' => $rows->sum('camere_occupate'),
                'camere_notti_disponibili' => $rows->sum('camere_disponibili'),
                'posti_notti_occupati' => $rows->sum('persone_presenti'),
                'posti_notti_disponibili' => $rows->sum('letti_disponibili'),
                'incoerenze' => $rows->where('incoerente', true)->count(),
                'media_camere_occupate' => round((float) $rows->avg('camere_occupate'), 1),
                'media_letti_occupati' => round((float) $rows->avg('letti_occupati'), 1),
                'media_occupazione_camere' => ($rows->sum('camere_disponibili') > 0 ? round(100 * $rows->sum('camere_occupate') / $rows->sum('camere_disponibili'), 1) : null),
                'media_occupazione_letti' => ($rows->sum('letti_disponibili') > 0 ? round(100 * $rows->sum('letti_occupati') / $rows->sum('letti_disponibili'), 1) : null),
                'picco_persone_presenti' => (int) $rows->max('persone_presenti'),
            ],
        ];
    }

    private function buildCategoryMetrics(Collection $schedine, Carbon $dal, Carbon $al, ?bool $italiane): array
    {
        $filtered = $schedine->map(function (Schedina $schedina) use ($italiane) {
            $count = $this->ospiti($schedina)->filter(fn ($ospite) => $this->isItalia($ospite['country']) === $italiane)->count();
            $copy = clone $schedina;
            $copy->setAttribute('cant_people', $count);
            return $copy;
        });

        return [
            'presenti' => (int) $filtered->sum(function (Schedina $schedina) use ($dal) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);
                if (!$arrivo || !$partenza) {
                    return 0;
                }
                return $arrivo->lt($dal) && $partenza->gte($dal) ? (int) $schedina->cant_people : 0;
            }),
            'arrivi' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $arrivo = $this->safeDate($schedina->arrive);
                return $arrivo && $arrivo->betweenIncluded($dal, $al) ? (int) $schedina->cant_people : 0;
            }),
            'partenze' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $partenza = $this->safeDate($schedina->departure);
                return $partenza && $partenza->betweenIncluded($dal, $al) ? (int) $schedina->cant_people : 0;
            }),
            'presenze' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);
                return $this->overlapNights($arrivo, $partenza, $dal, $al) * (int) $schedina->cant_people;
            }),
        ];
    }

    private function splitAdultiMinori(Schedina $schedina, ?Carbon $referenceDate): array
    {
        $reference = $referenceDate ?: now();
        $dates = collect([$schedina->oa_date_nac])
            ->merge(($schedina->componenti ?? collect())->pluck('date_nac'));

        $adulti = 0;
        $minori = 0;

        foreach ($dates as $date) {
            $birth = $this->safeDate($date);
            if (!$birth) {
                continue;
            }

            $age = \App\Support\Anagrafica\EtaOperativa::etaOperativa($date, $reference);
            if ($age === null) {
                continue;
            }

            if ($age >= 18) {
                $adulti++;
            } else {
                $minori++;
            }
        }



        return [$adulti, $minori];
    }

    private function movimentoRow(Schedina $schedina): array
    {
        return [
            'scheda' => $schedina->scheda ?: ('#' . $schedina->id),
            'arrivo' => $this->safeDate($schedina->arrive),
            'partenza' => $this->safeDate($schedina->departure),
            'ospite' => trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? '')),
            'persone' => $this->persone($schedina),
            'camere' => $this->camere($schedina),
            'letti' => $this->persone($schedina),
            'provenienza' => $this->originLabel($schedina->or_country),
            'categoria' => $this->categoria($schedina->or_country),
        ];
    }

    private function overlapNights(?Carbon $arrivo, ?Carbon $partenza, Carbon $dal, Carbon $al): int
    {
        if (!$arrivo || !$partenza || !$partenza->gt($arrivo)) {
            return 0;
        }

        $start = $arrivo->copy()->max($dal);
        $endExclusive = $partenza->copy()->min($al->copy()->addDay());
        if ($endExclusive->lte($start)) {
            return 0;
        }

        return $start->diffInDays($endExclusive);
    }

    private function safeDate($value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->startOfDay();
        if (blank($value)) {
            return null;
        }

        try {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', (string) $value, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) return null;
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sameDay($value, Carbon $day): bool
    {
        $date = $this->safeDate($value);
        return $date ? $date->equalTo($day) : false;
    }

    private function originLabel($value): string
    {
        if (blank($value)) {
            return 'Non disponibile';
        }

        if (is_numeric($value)) {
            $this->nazioni ??= GeoNazione::query()->get()->keyBy('id');
            $nazione = $this->nazioni->get((int) $value);
            if ($nazione) {
                return $nazione->nome;
            }
        }

        return (string) $value;
    }

    private function persone(Schedina $schedina): int
    {
        return max(1 + $schedina->componenti->count(), (int) $schedina->cant_people);
    }

    private function ospiti(Schedina $schedina): Collection
    {
        $ospiti = collect([['country' => $schedina->or_country, 'birth' => $schedina->oa_date_nac]]);
        foreach ($schedina->componenti as $componente) {
            $ospiti->push(['country' => $componente->country, 'birth' => $componente->date_nac]);
        }
        while ($ospiti->count() < $this->persone($schedina)) {
            $ospiti->push(['country' => null, 'birth' => null]);
        }
        return $ospiti;
    }

    private function camereOccupate(Collection $schedine): int
    {
        $reali = collect();
        $legacy = 0;
        foreach ($schedine as $s) {
            if ($s->camere->isEmpty()) $legacy += max(0, (int) $s->room);
            else $reali = $reali->merge($s->camere->pluck('numero_camera')->filter()->map(fn ($n) => trim((string) $n)));
        }
        return $legacy + $reali->unique()->count();
    }

    public function avvisi(): array
    {
        $schede = collect($this->periodi)->flatten(1)->unique('id');
        $avvisi = [];
        if ($schede->contains(fn ($s) => (int) $s->cant_people !== 1 + $s->componenti->count())) {
            $avvisi[] = 'Alcune schedine hanno un numero di persone diverso dai nominativi: si usa il maggiore dei due; persone senza anagrafica classificate come non disponibili.';
        }
        if ($schede->contains(fn ($s) => !$this->safeDate($s->arrive) || !$this->safeDate($s->departure) || $this->safeDate($s->departure)->lt($this->safeDate($s->arrive)))) {
            $avvisi[] = 'Sono presenti date soggiorno non valide: verificare le schedine prima di usare i totali.';
        }
        return $avvisi;
    }

    private function camere(Schedina $schedina): int
    {
        // Il numero camera è un identificativo, mai una quantità da sommare.
        return $schedina->camere->isNotEmpty()
            ? $schedina->camere->pluck('numero_camera')->filter()->unique()->count()
            : max(0, (int) $schedina->room);
    }

    private function categoria($value): string
    {
        return match ($this->isItalia($value)) {
            true => 'Italiane', false => 'Straniere', default => 'Non disponibile',
        };
    }

    private function isItalia($value): ?bool
    {
        if (blank($value)) return null;
        $this->nazioni ??= GeoNazione::query()->get()->keyBy('id');
        if (is_numeric($value)) {
            $nazione = $this->nazioni->get((int) $value);
        } else {
            $normalized = Str::lower(Str::ascii(trim((string) $value)));
            $nazione = $this->nazioni->first(fn ($n) => in_array($normalized, [Str::lower(Str::ascii($n->nome)), Str::lower((string) $n->codice_iso2)], true));
        }
        return $nazione ? ((bool) $nazione->is_italia || strtoupper((string) $nazione->codice_iso2) === 'IT') : null;
    }
}
