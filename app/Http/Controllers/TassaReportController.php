<?php

namespace App\Http\Controllers;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Services\TassaDiSoggiornoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TassaReportController extends Controller
{
    private TassaDiSoggiornoService $service;

    public function __construct(TassaDiSoggiornoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $mese = (int) $request->input('mese', now()->month);
        $anno = (int) $request->input('anno', now()->year);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 10;
        $q = trim((string) $request->input('q', ''));
        $missingSchedina = !Schema::hasTable('schedina');

        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        $config = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first();
        $esenzioni = Schema::hasTable('tassa_esenzioni')
            ? TassaEsenzione::where('struttura_id', $strutturaId)->where('attivo', true)->orderBy('ordine')->orderBy('codice')->get()
            : collect();

        $righe = $missingSchedina ? [] : $this->buildRows($mese, $anno, $strutturaId, $struttura, $config, $esenzioni);
        $collection = collect($righe);
        if ($q !== '') {
            $collection = $collection->filter(fn (array $riga) => $this->matchesRapportoSearch($riga, $q))->values();
        }
        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('tassa_di_soggiorno.rapporto', [
            'righe' => $paginator,
            'mese' => $mese,
            'anno' => $anno,
            'config' => $config,
            'struttura' => $struttura,
            'missingSchedina' => $missingSchedina,
            'q' => $q,
        ]);
    }

    public function controllo(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);
        $q = trim((string) $request->input('q', ''));

        if (!Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto', ['mese' => $mese, 'anno' => $anno])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $dataset = $this->buildControlDataset($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
        if ($q !== '') {
            $dataset = $this->filterControlDataset($dataset, $q);
        }

        return view('tassa_di_soggiorno.rapporto-controllo', array_merge($dataset, [
            'mese' => $mese,
            'anno' => $anno,
            'config' => $config,
            'struttura' => $struttura,
            'q' => $q,
        ]));
    }

    public function exportCsv(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (!Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto', ['mese' => $mese, 'anno' => $anno])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $righe = $this->buildRows($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
        $lines = [];
        $lines[] = 'data_inizio;data_fine;tipo;data_reg;arrivo;partenza;nominativo;soggetti;pernottamenti_imponibili;tariffa';
        foreach ($righe as $riga) {
            $lines[] = implode(';', [
                $riga['arrivo'],
                $riga['partenza'],
                $riga['tipo'],
                $riga['data_reg'],
                $riga['arrivo'],
                $riga['partenza'],
                str_replace(';', ',', $riga['nominativo']),
                $riga['soggetti'],
                $riga['pernottamenti_imponibili'],
                $riga['tariffa'],
            ]);
        }

        $csv = implode("\n", $lines);
        $monthName = now()->setMonth($mese)->locale('it')->monthName;
        $filename = sprintf('%s_%d.csv', str_replace(' ', '_', strtolower($monthName)), $anno);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportControlloCsv(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (!Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto.controllo', ['mese' => $mese, 'anno' => $anno])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $dataset = $this->buildControlDataset($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
        $lines = [
            implode(';', [
                'arrivo',
                'partenza',
                'scheda',
                'riferimento',
                'persone_scheda',
                'adulti_scheda',
                'minori_scheda',
                'paganti_scheda',
                'esenti_scheda',
                'nominativo',
                'eta',
                'minore',
                'codice_export',
                'paga',
                'esente',
                'motivo',
                'notti_totali',
                'notti_periodo',
                'notti_tassate',
                'notti_oltre_max',
                'tariffa',
                'tassa',
                'totale_scheda',
            ]),
        ];

        foreach ($dataset['rows'] as $row) {
            $lines[] = implode(';', [
                $this->formatCsvDate($row['arrivo']),
                $this->formatCsvDate($row['partenza']),
                $row['scheda'],
                str_replace(';', ',', (string) $row['riferimento']),
                $row['persone_totali_scheda'],
                $row['adulti_scheda'],
                $row['minori_scheda'],
                $row['paganti_scheda'],
                $row['esenti_scheda'],
                str_replace(';', ',', (string) $row['nominativo']),
                $row['eta'] ?? '',
                $row['minore'] ? 'SI' : 'NO',
                $row['codice_export'],
                $row['paga'] ? 'SI' : 'NO',
                $row['esente'] ? 'SI' : 'NO',
                str_replace(';', ',', (string) ($row['motivo'] ?? '')),
                $row['notti_totali'] ?? 0,
                $row['notti_periodo'] ?? 0,
                $row['notti_tassate'] ?? 0,
                $row['pernottamenti_oltre_max'] ?? 0,
                $row['tariffa'],
                $row['tassa'],
                $row['tassa_totale_scheda'],
            ]);
        }

        $csv = implode("\n", $lines);
        $monthName = now()->setMonth($mese)->locale('it')->monthName;
        $filename = sprintf('controllo_tassa_%s_%d.csv', str_replace(' ', '_', strtolower($monthName)), $anno);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function printControllo(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (!Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto.controllo', ['mese' => $mese, 'anno' => $anno])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $dataset = $this->buildControlDataset($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);

        return view('tassa_di_soggiorno.rapporto-print', array_merge($dataset, [
            'mese' => $mese,
            'anno' => $anno,
            'config' => $config,
            'struttura' => $struttura,
            'meseLabel' => Carbon::create($anno, $mese, 1)->locale('it')->monthName,
            'vista' => 'schede',
        ]));
    }

    private function loadContext(Request $request): array
    {
        $mese = (int) $request->input('mese', now()->month);
        $anno = (int) $request->input('anno', now()->year);

        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (!$strutturaId) {
            abort(302, '', ['Location' => route('strutture.seleziona.index')]);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        $config = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first();
        $esenzioni = Schema::hasTable('tassa_esenzioni')
            ? TassaEsenzione::where('struttura_id', $strutturaId)->where('attivo', true)->orderBy('ordine')->orderBy('codice')->get()
            : collect();

        return [$mese, $anno, $struttura, $config, $esenzioni];
    }

    private function buildControlDataset(int $mese, int $anno, int $strutturaId, Struttura $struttura, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        $schedine = Schedina::where('is_arrive', 0)
            ->where('struttura_id', $strutturaId)
            ->get();

        $rows = [];
        $schedeSummary = [];

        foreach ($schedine as $schedina) {
            $arrivo = $this->service->parseDate($schedina->arrive);
            if (!$arrivo || $arrivo->month !== $mese || $arrivo->year !== $anno) {
                continue;
            }

            $partenza = $this->service->parseDate($schedina->departure);
            $componenti = Componenti::where('schedina_id', $schedina->id)->get();
            $dettaglio = $this->service->dettaglioSchedina($schedina, $componenti, $config, $esenzioni, $struttura);

            $dettaglioRows = collect($dettaglio['righe'] ?? [])->map(function (array $riga) {
                $eta = $riga['eta'] ?? null;
                $minore = filled($eta) && (int) $eta < 18;
                $nottiTassate = !empty($riga['esente']) ? 0 : (int) ($riga['notti_imponibili'] ?? 0);
                $tassa = (float) ($riga['subtotale'] ?? 0);

                return [
                    'nominativo' => $riga['nome'] ?? 'Ospite',
                    'eta' => $eta,
                    'minore' => $minore,
                    'esente' => !empty($riga['esente']),
                    'motivo' => $riga['motivo'] ?? null,
                    'codice_export' => $riga['codice'] ?? 0,
                    'notti_totali' => (int) ($riga['notti_totali'] ?? 0),
                    'notti_periodo' => (int) ($riga['notti_periodo'] ?? 0),
                    'notti_tassate' => $nottiTassate,
                    'pernottamenti_oltre_max' => (int) ($riga['notti_oltre_max'] ?? 0),
                    'tariffa' => !empty($riga['esente']) ? 0.0 : (float) ($riga['aliquota'] ?? 0),
                    'tassa' => $tassa,
                    'paga' => $tassa > 0,
                ];
            })->values();

            $personeTotali = $dettaglioRows->count();
            $minori = $dettaglioRows->where('minore', true)->count();
            $esentiTotali = $dettaglioRows->where('esente', true)->count();
            $paganti = $dettaglioRows->where('paga', true)->count();
            $adulti = max(0, $personeTotali - $minori);
            $riferimento = trim(($schedina->surname ? $schedina->surname . ' ' : '') . ($schedina->name ?? '')) ?: ($dettaglioRows->first()['nominativo'] ?? '—');
            $tassaTotale = (float) $dettaglioRows->sum('tassa');
            $nottiTassateTotali = (int) $dettaglioRows->sum('notti_tassate');
            $nottiOltreTotali = (int) $dettaglioRows->sum('pernottamenti_oltre_max');

            $schedeSummary[] = [
                'arrivo' => $arrivo?->toDateString(),
                'partenza' => $partenza?->toDateString(),
                'scheda' => $schedina->scheda,
                'riferimento' => $riferimento,
                'persone_totali' => $personeTotali,
                'adulti_totali' => $adulti,
                'minori_totali' => $minori,
                'soggetti_paganti' => $paganti,
                'soggetti_esenti' => $esentiTotali,
                'notti_imponibili' => $nottiTassateTotali,
                'notti_oltre_max' => $nottiOltreTotali,
                'tassa_totale' => $tassaTotale,
            ];

            foreach ($dettaglioRows as $detail) {
                $rows[] = array_merge($detail, [
                    'arrivo' => $arrivo?->toDateString(),
                    'partenza' => $partenza?->toDateString(),
                    'scheda' => $schedina->scheda,
                    'riferimento' => $riferimento,
                    'persone_totali_scheda' => $personeTotali,
                    'adulti_scheda' => $adulti,
                    'minori_scheda' => $minori,
                    'paganti_scheda' => $paganti,
                    'esenti_scheda' => $esentiTotali,
                    'tassa_totale_scheda' => $tassaTotale,
                    'pernottamenti_imponibili' => $detail['notti_tassate'],
                ]);
            }
        }

        $rowsCollection = collect($rows)->sortBy([
            ['arrivo', 'asc'],
            ['scheda', 'asc'],
            ['nominativo', 'asc'],
        ])->values();

        $schedeCollection = collect($schedeSummary)->sortBy([
            ['arrivo', 'asc'],
            ['scheda', 'asc'],
        ])->values();

        $summary = [
            'totale_schedine' => $schedeCollection->count(),
            'totale_ospiti' => $rowsCollection->count(),
            'totale_paganti' => $rowsCollection->where('paga', true)->count(),
            'totale_esenti' => $rowsCollection->where('esente', true)->count(),
            'totale_minori' => $rowsCollection->where('minore', true)->count(),
            'totale_notti_imponibili' => (int) $rowsCollection->sum('notti_tassate'),
            'totale_tassa' => (float) $rowsCollection->sum('tassa'),
        ];

        $esenzioniSummary = $rowsCollection
            ->filter(fn (array $row) => $row['esente'])
            ->groupBy(fn (array $row) => (string) $row['codice_export'])
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'codice' => $first['codice_export'],
                    'motivo' => $first['motivo'] ?? 'Esenzione',
                    'notti_periodo' => (int) $group->sum('notti_periodo'),
                    'quantita' => $group->count(),
                ];
            })
            ->sortBy('codice')
            ->values();

        $epilogo = [
            'totale_schedine_con_imposta' => $schedeCollection->filter(fn (array $row) => (float) $row['tassa_totale'] > 0)->count(),
            'totale_schedine_senza_imposta' => $schedeCollection->filter(fn (array $row) => (float) $row['tassa_totale'] <= 0)->count(),
            'totale_notti_oltre_max' => (int) $rowsCollection->sum('pernottamenti_oltre_max'),
            'totale_persone_paganti' => $rowsCollection->where('paga', true)->count(),
            'totale_persone_esenti' => $rowsCollection->where('esente', true)->count(),
            'totale_da_versare' => (float) $rowsCollection->sum('tassa'),
        ];

        return [
            'rows' => $rowsCollection,
            'schedeSummary' => $schedeCollection,
            'summary' => $summary,
            'esenzioniSummary' => $esenzioniSummary,
            'epilogo' => $epilogo,
        ];
    }

    private function buildRows(int $mese, int $anno, int $strutturaId, Struttura $struttura, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        if (!Schema::hasTable('schedina')) {
            return [];
        }

        $schedine = Schedina::where('is_arrive', 0)
            ->where('struttura_id', $strutturaId)
            ->get();
        $righe = [];

        foreach ($schedine as $schedina) {
            $arrivo = $this->service->parseDate($schedina->arrive);
            if (!$arrivo || $arrivo->month !== $mese || $arrivo->year !== $anno) {
                continue;
            }
            $partenza = $this->service->parseDate($schedina->departure);
            $componenti = Componenti::where('schedina_id', $schedina->id)->get();
            $dettaglio = $this->service->dettaglioSchedina($schedina, $componenti, $config, $esenzioni, $struttura);

            foreach ($dettaglio['righe'] as $riga) {
                $righe[] = [
                    'arrivo' => $arrivo?->toDateString(),
                    'partenza' => $partenza?->toDateString(),
                    'scheda' => $schedina->scheda,
                    'nominativo' => $riga['nome'],
                    'eta' => $riga['eta'],
                    'esente' => $riga['esente'],
                    'motivo' => $riga['motivo'],
                    'pernottamenti_imponibili' => $riga['notti_imponibili'],
                    'pernottamenti_oltre_max' => 0,
                    'tassa' => $riga['subtotale'],
                    'tariffa' => $riga['aliquota'],
                    'tipo' => $riga['codice'] ?? 0,
                    'data_reg' => now()->toDateString(),
                    'soggetti' => 1,
                ];

                if ($riga['notti_oltre_max'] > 0) {
                    $righe[] = [
                        'arrivo' => $arrivo?->toDateString(),
                        'partenza' => $partenza?->toDateString(),
                        'scheda' => $schedina->scheda,
                        'nominativo' => $riga['nome'],
                        'eta' => $riga['eta'],
                        'esente' => true,
                        'motivo' => 'Oltre giorni max',
                        'pernottamenti_imponibili' => 0,
                        'pernottamenti_oltre_max' => $riga['notti_oltre_max'],
                        'tassa' => 0,
                        'tariffa' => 0,
                        'tipo' => 777,
                        'data_reg' => now()->toDateString(),
                        'soggetti' => 1,
                    ];
                }
            }
        }

        return $righe;
    }

    private function filterControlDataset(array $dataset, string $query): array
    {
        $rowsCollection = collect($dataset['rows'] ?? [])
            ->filter(fn (array $row) => $this->matchesControlloRowSearch($row, $query))
            ->values();

        $matchedSchede = $rowsCollection->pluck('scheda')->filter()->unique()->values();

        $schedeCollection = collect($dataset['schedeSummary'] ?? [])
            ->filter(function (array $scheda) use ($query, $matchedSchede) {
                return $matchedSchede->contains($scheda['scheda'])
                    || $this->matchesControlloSchedaSearch($scheda, $query);
            })
            ->values();

        $summary = [
            'totale_schedine' => $schedeCollection->count(),
            'totale_ospiti' => $rowsCollection->count(),
            'totale_paganti' => $rowsCollection->where('paga', true)->count(),
            'totale_esenti' => $rowsCollection->where('esente', true)->count(),
            'totale_minori' => $rowsCollection->where('minore', true)->count(),
            'totale_notti_imponibili' => (int) $rowsCollection->sum('notti_tassate'),
            'totale_tassa' => (float) $rowsCollection->sum('tassa'),
        ];

        return array_merge($dataset, [
            'rows' => $rowsCollection,
            'schedeSummary' => $schedeCollection,
            'summary' => $summary,
        ]);
    }

    private function matchesRapportoSearch(array $row, string $query): bool
    {
        $needle = $this->normalizeSearchText($query);
        $values = [
            $row['scheda'] ?? null,
            $row['nominativo'] ?? null,
            $row['motivo'] ?? null,
            $row['arrivo'] ?? null,
            $row['partenza'] ?? null,
            isset($row['arrivo']) ? $this->formatSearchDate($row['arrivo']) : null,
            isset($row['partenza']) ? $this->formatSearchDate($row['partenza']) : null,
        ];

        foreach ($values as $value) {
            if (str_contains($this->normalizeSearchText($value), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function matchesControlloRowSearch(array $row, string $query): bool
    {
        $needle = $this->normalizeSearchText($query);
        $values = [
            $row['scheda'] ?? null,
            $row['riferimento'] ?? null,
            $row['nominativo'] ?? null,
            $row['motivo'] ?? null,
            $row['arrivo'] ?? null,
            $row['partenza'] ?? null,
            isset($row['arrivo']) ? $this->formatSearchDate($row['arrivo']) : null,
            isset($row['partenza']) ? $this->formatSearchDate($row['partenza']) : null,
        ];

        foreach ($values as $value) {
            if (str_contains($this->normalizeSearchText($value), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function matchesControlloSchedaSearch(array $row, string $query): bool
    {
        $needle = $this->normalizeSearchText($query);
        $values = [
            $row['scheda'] ?? null,
            $row['riferimento'] ?? null,
            $row['arrivo'] ?? null,
            $row['partenza'] ?? null,
            isset($row['arrivo']) ? $this->formatSearchDate($row['arrivo']) : null,
            isset($row['partenza']) ? $this->formatSearchDate($row['partenza']) : null,
        ];

        foreach ($values as $value) {
            if (str_contains($this->normalizeSearchText($value), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeSearchText(mixed $value): string
    {
        $text = mb_strtolower(trim((string) $value));

        return str_replace(['/', '-', '.', ',', '  '], [' ', ' ', ' ', ' ', ' '], $text);
    }

    private function formatSearchDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $date;
        }
    }
}
