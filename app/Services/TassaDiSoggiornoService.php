<?php

namespace App\Services;

use App\Models\Componenti;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\Schedina;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TassaDiSoggiornoService
{
    public function parseDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', Carbon::ISO8601] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function diffNotti(?Carbon $arrivo, ?Carbon $partenza): int
    {
        if (!$arrivo || !$partenza) {
            return 0;
        }

        return max(0, $arrivo->diffInDays($partenza));
    }

    public function diffNottiNelPeriodo(?Carbon $arrivo, ?Carbon $partenza, ?TassaDiSoggiorno $config): int
    {
        if (!$arrivo || !$partenza) {
            return 0;
        }

        $inizioRaw = $config?->inizio;
        $fineRaw = $config?->fine;

        $inizio = $inizioRaw instanceof Carbon ? $inizioRaw->copy() : $this->parseDate($inizioRaw ? (string) $inizioRaw : null);
        $fine = $fineRaw instanceof Carbon ? $fineRaw->copy() : $this->parseDate($fineRaw ? (string) $fineRaw : null);

        if (!$inizio || !$fine) {
            return $this->diffNotti($arrivo, $partenza);
        }

        $count = 0;
        $cursor = $arrivo->copy()->startOfDay();
        $end = $partenza->copy()->startOfDay();

        while ($cursor->lt($end)) {
            if ($this->isDataNelPeriodo($cursor, $inizio, $fine)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    public function dettaglioSchedina(Schedina $schedina, Collection $componenti, ?TassaDiSoggiorno $config, Collection $esenzioni, ?Struttura $struttura = null): array
    {
        $arrivo = $this->parseDate($schedina->arrive);
        $partenza = $this->parseDate($schedina->departure);
        $giorniMax = $config && $config->giorni_massimo !== null ? (int) $config->giorni_massimo : null;
        $aliquota = $config ? (float) str_replace(',', '.', $config->tassa_soggiorno ?? 0) : 0.0;

        $righe = [];
        $totale = 0;
        $oltreMaxTotale = 0;

        $persone = collect();
        $persone->push(["nome" => trim(($schedina->surname ? $schedina->surname . ' ' : '') . ($schedina->name ?? '')), "exent" => $schedina->exent, "eta" => $this->etaFromString($schedina->oa_date_nac ?? null)]);
        foreach ($componenti as $comp) {
            $persone->push([
                'nome' => trim(($comp->surname ? $comp->surname . ' ' : '') . ($comp->name ?? '')),
                'exent' => $comp->exent,
                'eta' => $this->etaFromString($comp->date_nac ?? null),
            ]);
        }

        foreach ($persone as $persona) {
            $info = $this->calcolaPersona($persona, $arrivo, $partenza, $giorniMax, $aliquota, $config, $esenzioni);
            $righe[] = $info;
            $totale += $info['subtotale'];
            $oltreMaxTotale += $info['notti_oltre_max'];
        }

        return [
            'righe' => $righe,
            'totale' => $totale,
            'notti_oltre_max' => $oltreMaxTotale,
            'notti_totali' => $this->diffNotti($arrivo, $partenza),
        ];
    }

    public function exportRows(array $dettaglio, ?Carbon $arrivo, ?Carbon $partenza): array
    {
        $rows = [];
        foreach ($dettaglio['righe'] as $riga) {
            $rows[] = [
                'tipo' => $riga['codice'] ?? 0,
                'data_reg' => now()->toDateString(),
                'arrivo' => $arrivo?->toDateString(),
                'partenza' => $partenza?->toDateString(),
                'nominativo' => $riga['nome'],
                'soggetti' => 1,
                'pernottamenti' => $riga['notti_imponibili'],
                'tariffa' => $riga['aliquota'],
            ];
            if ($riga['notti_oltre_max'] > 0) {
                $rows[] = [
                    'tipo' => 777,
                    'data_reg' => now()->toDateString(),
                    'arrivo' => $arrivo?->toDateString(),
                    'partenza' => $partenza?->toDateString(),
                    'nominativo' => $riga['nome'],
                    'soggetti' => 1,
                    'pernottamenti' => $riga['notti_oltre_max'],
                    'tariffa' => 0,
                ];
            }
        }

        return $rows;
    }

    private function calcolaPersona(array $persona, ?Carbon $arrivo, ?Carbon $partenza, ?int $giorniMax, float $aliquota, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        $nottiTot = $this->diffNotti($arrivo, $partenza);
        $nottiNelPeriodo = $this->diffNottiNelPeriodo($arrivo, $partenza, $config);
        $esenzione = $this->resolveEsenzione($persona['exent'] ?? null, $esenzioni);
        $isEsente = $esenzione !== null;

        // Esenzione automatica per età
        $eta = $persona['eta'];
        if (!$isEsente && $eta !== null) {
            if ($config && $config->max_age_children && $eta <= (int) $config->max_age_children) {
                $isEsente = true;
                $esenzione = $esenzione ?? $this->resolveAutomaticEsenzione(
                    $esenzioni,
                    ['400', 'ETA_BIMBI'],
                    ['minori', 'bambini', 'minore'],
                    'ETA_BIMBI',
                    'Esente per età bambini'
                );
            }
            if ($config && $config->min_age_adult && $eta < (int) $config->min_age_adult) {
                $isEsente = true;
                $esenzione = $esenzione ?? $this->resolveAutomaticEsenzione(
                    $esenzioni,
                    ['ETA_MIN'],
                    ['eta minima', 'età minima'],
                    'ETA_MIN',
                    'Esente per età minima'
                );
            }
        }

        $nottiImponibili = $nottiNelPeriodo;
        if ($giorniMax !== null) {
            $nottiImponibili = min($nottiNelPeriodo, $giorniMax);
        }

        $nottiOltre = $giorniMax !== null
            ? max(0, $nottiNelPeriodo - $giorniMax)
            : 0;

        $nottiTassate = $isEsente ? 0 : $nottiImponibili;
        $aliquotaApplicata = $isEsente ? 0.0 : $aliquota;

        $motivo = $esenzione?->descrizione;
        if (!$isEsente && $nottiTot > 0 && $nottiNelPeriodo === 0) {
            $motivo = 'Fuori periodo di applicazione';
        } elseif (!$isEsente && $giorniMax !== null && $giorniMax === 0 && $nottiNelPeriodo > 0) {
            $motivo = 'Giorni massimo imponibili impostati a 0';
        } elseif (!$isEsente && $giorniMax !== null && $nottiNelPeriodo > 0 && $nottiImponibili === 0 && $nottiOltre > 0) {
            $motivo = 'Oltre giorni massimo';
        } elseif (!$isEsente && $nottiTot === 0) {
            $motivo = 'Soggiorno senza pernottamenti imponibili';
        }

        return [
            'nome' => $persona['nome'] ?: 'Ospite',
            'eta' => $eta,
            'esente' => $isEsente,
            'motivo' => $motivo,
            'codice' => $esenzione?->codice ?? 0,
            'notti_periodo' => $nottiNelPeriodo,
            'notti_totali' => $nottiTot,
            'notti_imponibili' => $nottiImponibili,
            'notti_tassate' => $nottiTassate,
            'notti_oltre_max' => $nottiOltre,
            'aliquota' => $aliquotaApplicata,
            'subtotale' => $nottiTassate * $aliquotaApplicata,
        ];
    }

    private function resolveEsenzione(?string $value, Collection $esenzioni): ?TassaEsenzione
    {
        if (!$value || strtoupper(trim($value)) === 'NO') {
            return null;
        }

        $clean = trim($value);
        if ($clean === '777') {
            return null;
        }
        return $esenzioni->first(function ($row) use ($clean) {
            return strcasecmp($row->codice, '777') !== 0
                && (strcasecmp($row->codice, $clean) === 0 || strcasecmp($row->descrizione, $clean) === 0);
        });
    }

    private function isDataNelPeriodo(Carbon $giorno, Carbon $inizio, Carbon $fine): bool
    {
        $giornoKey = (int) $giorno->format('md');
        $inizioKey = (int) $inizio->format('md');
        $fineKey = (int) $fine->format('md');

        if ($inizioKey <= $fineKey) {
            return $giornoKey >= $inizioKey && $giornoKey <= $fineKey;
        }

        return $giornoKey >= $inizioKey || $giornoKey <= $fineKey;
    }

    private function etaFromString(?string $value): ?int
    {
        $date = $this->parseDate($value);
        if (!$date) {
            return null;
        }
        return $date->age;
    }

    private function makeVirtualEsenzione(string $codice, string $descrizione): TassaEsenzione
    {
        $fake = new TassaEsenzione();
        $fake->codice = $codice;
        $fake->descrizione = $descrizione;
        $fake->attivo = true;
        $fake->richiede_nota = false;
        $fake->ordine = 9999;
        return $fake;
    }

    private function resolveAutomaticEsenzione(Collection $esenzioni, array $codes, array $descriptionFragments, string $fallbackCode, string $fallbackDescription): TassaEsenzione
    {
        foreach ($codes as $code) {
            $match = $esenzioni->first(function ($row) use ($code) {
                return strcasecmp((string) $row->codice, $code) === 0;
            });

            if ($match) {
                return $match;
            }
        }

        foreach ($descriptionFragments as $fragment) {
            $match = $esenzioni->first(function ($row) use ($fragment) {
                return str_contains(mb_strtolower((string) $row->descrizione), mb_strtolower($fragment));
            });

            if ($match) {
                return $match;
            }
        }

        return $this->makeVirtualEsenzione($fallbackCode, $fallbackDescription);
    }

}
