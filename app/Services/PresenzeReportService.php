<?php

namespace App\Services;

use App\Models\GeoNazione;
use App\Models\Schedina;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PresenzeReportService
{
    public function schedinePeriodo(int $strutturaId, Carbon $dal, Carbon $al): Collection
    {
        return Schedina::query()
            ->withoutGlobalScope('struttura')
            ->with(['componenti' => fn ($query) => $query->withoutGlobalScope('struttura')])
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

        for ($mese = 1; $mese <= 12; $mese++) {
            $dal = Carbon::create($anno, $mese, 1)->startOfMonth();
            $al = $dal->copy()->endOfMonth();
            $schedine = $this->schedinePeriodo($strutturaId, $dal, $al);

            $italiane = $this->buildCategoryMetrics($schedine, $dal, $al, true);
            $straniere = $this->buildCategoryMetrics($schedine, $dal, $al, false);

            $rows->push([
                'mese' => $mese,
                'mese_label' => $dal->locale('it')->translatedFormat('F Y'),
                'dal' => $dal,
                'al' => $al,
                'italiane' => $italiane,
                'straniere' => $straniere,
                'totale' => [
                    'presenti' => $italiane['presenti'] + $straniere['presenti'],
                    'arrivi' => $italiane['arrivi'] + $straniere['arrivi'],
                    'partenze' => $italiane['partenze'] + $straniere['partenze'],
                    'presenze' => $italiane['presenze'] + $straniere['presenze'],
                    'totale_esteri' => $straniere['presenze'],
                    'totale_italiani' => $italiane['presenze'],
                ],
            ]);
        }

        return $rows;
    }

    public function dettaglioPeriodo(int $strutturaId, Carbon $dal, Carbon $al): array
    {
        $schedine = $this->schedinePeriodo($strutturaId, $dal, $al);
        $rows = $schedine->map(function (Schedina $schedina) use ($dal, $al) {
            $arrivo = $this->safeDate($schedina->arrive);
            $partenza = $this->safeDate($schedina->departure);
            $componenti = $schedina->componenti ?? collect();
            $persone = max(1, (int) ($schedina->cant_people ?? ($componenti->count() + 1)));
            $presenze = $this->overlapNights($arrivo, $partenza, $dal, $al) * $persone;
            [$adulti, $minori] = $this->splitAdultiMinori($schedina, $arrivo);
            $origin = $this->originLabel($schedina->or_country ?: $schedina->oa_country);

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
                'categoria' => $this->isItalia($schedina->or_country ?: $schedina->oa_country) ? 'Italiane' : 'Straniere',
            ];
        })->values();

        return [
            'rows' => $rows,
            'totali' => [
                'schedine' => $rows->count(),
                'arrivi' => (int) $rows->sum('persone'),
                'partenze' => (int) $rows->filter(fn ($row) => $row['partenza'] && $row['partenza']->betweenIncluded($dal, $al))->sum('persone'),
                'presenze' => (int) $rows->sum('presenze'),
                'adulti' => (int) $rows->sum('adulti'),
                'minori' => (int) $rows->sum('minori'),
                'italiani' => (int) $rows->where('categoria', 'Italiane')->sum('presenze'),
                'stranieri' => (int) $rows->where('categoria', 'Straniere')->sum('presenze'),
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
            [$a, $m] = $this->splitAdultiMinori($schedina, $giorno);
            $adulti += $a;
            $minori += $m;
        }

        return [
            'giorno' => $giorno,
            'presenti_totali' => (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0)),
            'adulti' => $adulti,
            'minori' => $minori,
            'italiani' => (int) $presenti->filter(fn (Schedina $schedina) => $this->isItalia($schedina->or_country ?: $schedina->oa_country))->sum('cant_people'),
            'stranieri' => (int) $presenti->filter(fn (Schedina $schedina) => !$this->isItalia($schedina->or_country ?: $schedina->oa_country))->sum('cant_people'),
            'arrivi_oggi' => (int) $arriviOggi->sum('cant_people'),
            'partenze_oggi' => (int) $partenzeOggi->sum('cant_people'),
            'camere_occupate' => (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->room ?? 0)),
            'letti_occupati' => (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->beds ?? 0)),
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

        for ($day = $dal->copy(); $day->lte($al); $day->addDay()) {
            $presenti = $schedine->filter(function (Schedina $schedina) use ($day) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);

                return $arrivo && $partenza && $arrivo->lte($day) && $partenza->gt($day);
            });

            $camereOccupate = (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->room ?? 0));
            $lettiOccupati = (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->beds ?? 0));
            $personePresenti = (int) $presenti->sum(fn (Schedina $schedina) => (int) ($schedina->cant_people ?? 0));

            $rows->push([
                'giorno' => $day->copy(),
                'camere_disponibili' => $camereDisponibili,
                'camere_occupate' => $camereOccupate,
                'camere_libere' => max(0, $camereDisponibili - $camereOccupate),
                'letti_disponibili' => $lettiDisponibili,
                'letti_occupati' => $lettiOccupati,
                'letti_liberi' => max(0, $lettiDisponibili - $lettiOccupati),
                'persone_presenti' => $personePresenti,
                'occupazione_camere' => $camereDisponibili > 0 ? round(($camereOccupate / $camereDisponibili) * 100, 1) : 0,
                'occupazione_letti' => $lettiDisponibili > 0 ? round(($lettiOccupati / $lettiDisponibili) * 100, 1) : 0,
            ]);
        }

        return [
            'rows' => $rows,
            'totali' => [
                'media_camere_occupate' => round((float) $rows->avg('camere_occupate'), 1),
                'media_letti_occupati' => round((float) $rows->avg('letti_occupati'), 1),
                'media_occupazione_camere' => round((float) $rows->avg('occupazione_camere'), 1),
                'media_occupazione_letti' => round((float) $rows->avg('occupazione_letti'), 1),
                'picco_persone_presenti' => (int) $rows->max('persone_presenti'),
            ],
        ];
    }

    private function buildCategoryMetrics(Collection $schedine, Carbon $dal, Carbon $al, bool $italiane): array
    {
        $filtered = $schedine->filter(function (Schedina $schedina) use ($italiane) {
            $isItalia = $this->isItalia($schedina->or_country ?: $schedina->oa_country);
            return $italiane ? $isItalia : !$isItalia;
        });

        return [
            'presenti' => (int) $filtered->sum(function (Schedina $schedina) use ($dal) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);
                if (!$arrivo || !$partenza) {
                    return 0;
                }
                return $arrivo->lt($dal) && $partenza->gt($dal) ? (int) ($schedina->cant_people ?? 0) : 0;
            }),
            'arrivi' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $arrivo = $this->safeDate($schedina->arrive);
                return $arrivo && $arrivo->betweenIncluded($dal, $al) ? (int) ($schedina->cant_people ?? 0) : 0;
            }),
            'partenze' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $partenza = $this->safeDate($schedina->departure);
                return $partenza && $partenza->betweenIncluded($dal, $al) ? (int) ($schedina->cant_people ?? 0) : 0;
            }),
            'presenze' => (int) $filtered->sum(function (Schedina $schedina) use ($dal, $al) {
                $arrivo = $this->safeDate($schedina->arrive);
                $partenza = $this->safeDate($schedina->departure);
                return $this->overlapNights($arrivo, $partenza, $dal, $al) * (int) ($schedina->cant_people ?? 0);
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
                $adulti++;
                continue;
            }

            $age = $birth->diffInYears($reference);
            if ($age >= 18) {
                $adulti++;
            } else {
                $minori++;
            }
        }

        if (($adulti + $minori) === 0) {
            $adulti = max(1, (int) ($schedina->cant_people ?? 1));
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
            'persone' => max(1, (int) ($schedina->cant_people ?? 1)),
            'camere' => (int) ($schedina->room ?? 0),
            'letti' => (int) ($schedina->beds ?? 0),
            'provenienza' => $this->originLabel($schedina->or_country ?: $schedina->oa_country),
            'categoria' => $this->isItalia($schedina->or_country ?: $schedina->oa_country) ? 'Italiane' : 'Straniere',
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
        if (blank($value)) {
            return null;
        }

        try {
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
            $nazione = GeoNazione::query()->find((int) $value);
            if ($nazione) {
                return $nazione->nome;
            }
        }

        return (string) $value;
    }

    private function isItalia($value): bool
    {
        if (blank($value)) {
            return false;
        }

        if (is_numeric($value)) {
            $nazione = GeoNazione::query()->find((int) $value);
            if ($nazione && property_exists($nazione, 'is_italia')) {
                return (bool) $nazione->is_italia;
            }
            return (int) $value === 106;
        }

        $normalized = Str::lower(Str::ascii((string) $value));
        return in_array($normalized, ['italia', 'italiano', 'italiana', 'italy'], true);
    }
}
