<?php

namespace App\Http\Controllers;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\TassaExport;
use App\Services\TassaDiSoggiornoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TassaReportController extends Controller
{
    private TassaDiSoggiornoService $service;

    private Carbon $dataDa;

    private Carbon $dataA;

    private array $calcoliSnapshot = [];

    private ?TassaDiSoggiorno $configurazionePeriodo = null;

    public function __construct(TassaDiSoggiornoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);
        $strutturaId = (int) $struttura->id;
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 10;
        $q = trim((string) $request->input('q', ''));
        $missingSchedina = ! Schema::hasTable('schedina');

        try {
            $righe = ($missingSchedina || $request->filled('export_id')) ? [] : $this->buildRows($mese, $anno, $strutturaId, $struttura, $config, $esenzioni);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $request->expectsJson() && isset($e->errors()['configurazione_legacy_discordante'])) {
                return redirect()->route('tassa_di_soggiorno.edit', ['anno_fiscale' => $anno])->withErrors($e->errors());
            }
            throw $e;
        }
        if (! $request->filled('export_id')) {
            $config = $this->configurazionePeriodo ?? $config;
        }
        $storico = null;
        if ($request->filled('export_id')) {
            $request->validate(['export_id' => 'integer|min:1']);
            $storico = TassaExport::where('struttura_id', $strutturaId)->findOrFail($request->integer('export_id'));
            $snapshot = $storico->snapshot;
            abort_unless(hash_equals($storico->sha256, hash('sha256', $snapshot['csv'])), 409, 'Integrità export Tassa non verificata.');
            $righe = $snapshot['movimenti'];
            $config = new TassaDiSoggiorno($snapshot['configurazione'] ?? []);
            $struttura = new Struttura($snapshot['struttura'] ?? ['nome_struttura' => $struttura->nome_struttura, 'citta' => $struttura->citta]);
            $this->dataDa = Carbon::parse($storico->data_da)->startOfDay();
            $this->dataA = Carbon::parse($storico->data_a)->endOfDay();
        }
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
            'storico' => $storico,
            'totalePeriodo' => collect($righe)->sum('tassa'),
            'mese' => $mese,
            'anno' => $anno,
            'dataDa' => $this->dataDa->toDateString(),
            'dataA' => $this->dataA->toDateString(),
            'config' => $config,
            'struttura' => $struttura,
            'missingSchedina' => $missingSchedina,
            'q' => $q,
            'exports' => Schema::hasTable('tassa_exports') ? TassaExport::where('struttura_id', $strutturaId)->orderByDesc('id')->limit(20)->get() : collect(),
        ]);
    }

    public function controllo(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);
        $q = trim((string) $request->input('q', ''));

        if (! Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto', ['data_da' => $this->dataDa->toDateString(), 'data_a' => $this->dataA->toDateString()])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $dataset = $this->buildControlDataset($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
        if ($q !== '') {
            $dataset = $this->filterControlDataset($dataset, $q);
        }

        return view('tassa_di_soggiorno.rapporto-controllo', array_merge($dataset, [
            'mese' => $mese,
            'anno' => $anno,
            'dataDa' => $this->dataDa->toDateString(),
            'dataA' => $this->dataA->toDateString(),
            'config' => $config,
            'struttura' => $struttura,
            'q' => $q,
        ]));
    }

    public function exportCsv(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (! Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto', ['data_da' => $this->dataDa->toDateString(), 'data_a' => $this->dataA->toDateString()])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $righe = $this->buildRows($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
        $csv = $this->csvFromRows($righe);
        $monthName = $this->dataDa->copy()->locale('it')->monthName;
        $filename = sprintf('%s_%d.csv', str_replace(' ', '_', strtolower($monthName)), $anno);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportControlloCsv(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (! Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto.controllo', ['data_da' => $this->dataDa->toDateString(), 'data_a' => $this->dataA->toDateString()])
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
            $lines[] = $this->csvLine([
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
                $row['tariffa'] === null ? 'Variabile' : $row['tariffa'],
                $row['tassa'],
                $row['tassa_totale_scheda'],
            ]);
        }

        $csv = implode("\n", $lines);
        $monthName = $this->dataDa->copy()->locale('it')->monthName;
        $filename = sprintf('controllo_tassa_%s_%d.csv', str_replace(' ', '_', strtolower($monthName)), $anno);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function printControllo(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);

        if (! Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto.controllo', ['data_da' => $this->dataDa->toDateString(), 'data_a' => $this->dataA->toDateString()])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $dataset = $this->buildControlDataset($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);

        return view('tassa_di_soggiorno.rapporto-print', array_merge($dataset, [
            'mese' => $mese,
            'anno' => $anno,
            'dataDa' => $this->dataDa->toDateString(),
            'dataA' => $this->dataA->toDateString(),
            'config' => $config,
            'struttura' => $struttura,
            'meseLabel' => Carbon::create($anno, $mese, 1)->locale('it')->monthName,
            'vista' => 'schede',
        ]));
    }

    private function validateStayTourScope(Struttura $struttura, ?TassaDiSoggiorno $config): void
    {
        $bellaria = in_array(mb_strtolower(trim((string) $struttura->citta)), ['bellaria-igea marina', 'bellaria igea marina'], true);
        if ($bellaria && (! $config || $config->giorni_massimo !== 6)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['limite_csv' => 'Il tracciato Bellaria verificato usa il limite di 6 notti. Il raccordo StayTour per limiti differenti resta non documentato.']);
        }
    }

    private function csvLine(array $fields): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $fields, ';', '"', '', "\n");
        rewind($stream);
        $line = stream_get_contents($stream);
        fclose($stream);

        return rtrim($line, "\n");
    }

    private function csvFromRows(array $righe): string
    {
        $lines = [];
        $inizioPeriodo = $this->dataDa;
        $finePeriodo = $this->dataA;
        $lines[] = $inizioPeriodo->format('d/m/Y').';'.$finePeriodo->format('d/m/Y').';';
        foreach ($righe as $riga) {
            $lines[] = $this->csvLine([
                $riga['tipo'],
                $this->formatCsvDate($riga['data_reg']),
                $this->formatCsvDate($riga['arrivo']),
                $this->formatCsvDate($riga['partenza']),
                $riga['nominativo'],
                $riga['soggetti'],
                (string) $riga['tipo'] === '777' ? $riga['pernottamenti_oltre_max'] : $riga['pernottamenti_imponibili'],
                $riga['tariffa'],
                '',
            ]);
        }

        return implode("\n", $lines);
    }

    public function consolida(Request $request)
    {
        [$mese, $anno, $struttura, $config, $esenzioni] = $this->loadContext($request);
        $export = DB::transaction(function () use ($struttura, $request, $mese, $anno) {
            $struttura = Struttura::whereKey($struttura->id)->lockForUpdate()->firstOrFail();
            $config = TassaDiSoggiorno::where('struttura_id', $struttura->id)->first();

            $esenzioni = TassaEsenzione::where('struttura_id', $struttura->id)->where('attivo', true)->get();
            $esenzioni = $this->service->catalogoBellaria($struttura, $esenzioni);
            $righe = $this->buildRows($mese, $anno, (int) $struttura->id, $struttura, $config, $esenzioni);
            $config = $this->configurazionePeriodo ?? $config;
            $csv = $this->csvFromRows($righe);
            $precedente = TassaExport::where('struttura_id', $struttura->id)
                ->where('data_da', $this->dataDa->toDateString())->where('data_a', $this->dataA->toDateString())
                ->orderByDesc('versione')->first();

            return TassaExport::create([
                'struttura_id' => $struttura->id, 'data_da' => $this->dataDa->toDateString(),
                'data_a' => $this->dataA->toDateString(), 'versione' => ($precedente?->versione ?? 0) + 1,
                'precedente_id' => $precedente?->id, 'created_by' => $request->user()->id,
                'snapshot' => ['csv' => $csv, 'movimenti' => $righe, 'calcoli' => $this->calcoliSnapshot, 'configurazione' => $config ? array_intersect_key($config->getAttributes(), array_flip(['struttura_id', 'tassa_soggiorno', 'giorni_massimo', 'inizio', 'fine', 'max_age_children', 'min_age_adult', 'ricevuta_immagine', 'regola_versione', 'regola_categoria', 'regola_fonte', 'valida_dal', 'valida_al'])) : null, 'struttura' => array_replace(array_intersect_key($struttura->getAttributes(), array_flip(['id', 'nome_struttura', 'indirizzo', 'numero_civico', 'cap', 'logo'])), ['citta' => $struttura->citta, 'localita' => $struttura->localita, 'logo_citta' => $struttura->logo_citta])],
                'sha256' => hash('sha256', $csv), 'created_at' => now(),
            ]);
        });

        return redirect()->route('tassa_di_soggiorno.export.download', ['id' => $export->id]);
    }

    public function downloadStorico(Request $request, int $id)
    {
        [, , $struttura] = $this->loadContext($request, false);
        $export = TassaExport::where('struttura_id', $struttura->id)->findOrFail($id);
        $csv = $export->snapshot['csv'];
        abort_unless(hash_equals($export->sha256, hash('sha256', $csv)), 409, 'Integrità export Tassa non verificata.');

        return response($csv)->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="tassa_export_'.$export->id.'_v'.$export->versione.'.csv"');
    }

    private function loadContext(Request $request, bool $automatico = true): array
    {
        $validated = $request->validate([
            'data_da' => 'nullable|required_with:data_a|date_format:Y-m-d|after_or_equal:2015-01-01|before_or_equal:2100-12-31',
            'data_a' => 'nullable|required_with:data_da|date_format:Y-m-d|after_or_equal:data_da|before_or_equal:2100-12-31',
            'mese' => 'nullable|integer|between:1,12',
            'anno' => 'nullable|integer|between:2015,2100',
        ]);
        $mese = (int) ($validated['mese'] ?? now()->month);
        $anno = (int) ($validated['anno'] ?? now()->year);
        $this->dataDa = isset($validated['data_da']) ? Carbon::parse($validated['data_da'])->startOfDay() : Carbon::create($anno, $mese, 1)->startOfDay();
        $this->dataA = isset($validated['data_a']) ? Carbon::parse($validated['data_a'])->endOfDay() : $this->dataDa->copy()->endOfMonth();
        abort_if($this->dataDa->diffInDays($this->dataA) > 366, 422, 'Il periodo interno può comprendere al massimo un anno.');
        $mese = $this->dataDa->month;
        $anno = $this->dataDa->year;

        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (! $strutturaId) {
            abort(302, '', ['Location' => route('strutture.seleziona.index')]);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        $config = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first();
        $esenzioni = Schema::hasTable('tassa_esenzioni')
            ? TassaEsenzione::where('struttura_id', $strutturaId)->where('attivo', true)->orderBy('ordine')->orderBy('codice')->get()
            : collect();

        $esenzioni = $this->service->catalogoBellaria($struttura, $esenzioni);

        return [$mese, $anno, $struttura, $config, $esenzioni];
    }

    private function formatCsvDate($value): string
    {
        if (! $value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function buildControlDataset(int $mese, int $anno, int $strutturaId, Struttura $struttura, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        $schedine = Schedina::where('is_arrive', 0)
            ->where('struttura_id', $strutturaId)
            ->get()->filter(fn ($schedina) => ($arrivo = $this->service->parseDate($schedina->arrive)) && $arrivo->betweenIncluded($this->dataDa, $this->dataA));
        if ($schedine->isEmpty()) {
            $this->configurazionePeriodo = $this->service->configurazioneAutomatica($struttura, $config, $this->dataDa);
            $this->service->validaConfigurazioneBellaria($struttura, $this->configurazionePeriodo, $this->dataDa);
        }

        $rows = [];
        $schedeSummary = [];
        $reconciliationErrors = [];

        foreach ($schedine as $schedina) {
            $arrivo = $this->service->parseDate($schedina->arrive);
            if (! $arrivo || ! $arrivo->betweenIncluded($this->dataDa, $this->dataA)) {
                continue;
            }

            $partenza = $this->service->parseDate($schedina->departure);
            $componenti = Componenti::where('schedina_id', $schedina->id)->get();
            $configMovimento = $this->service->configurazioneAutomatica($struttura, $config, $arrivo);
            if (! $configMovimento || $configMovimento->tassa_soggiorno === null || $configMovimento->giorni_massimo === null) {
                throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_tassa' => 'Configurare aliquota e limite prima di elaborare i movimenti Tassa.']);
            }
            $dettaglio = $this->service->dettaglioSchedina($schedina, $componenti, $configMovimento, $esenzioni, $struttura);

            $movimenti = $this->service->exportRows($dettaglio, $arrivo, $partenza);
            $nottiMovimenti = array_sum(array_column($movimenti, 'pernottamenti'));
            $importoMovimenti = array_sum(array_map(fn ($r) => $r['pernottamenti'] * $r['tariffa'], $movimenti));
            if ($nottiMovimenti !== array_sum(array_column($dettaglio['righe'], 'notti_periodo'))
                || round($importoMovimenti * 100) !== round($dettaglio['totale'] * 100)) {
                $reconciliationErrors[] = $schedina->id;
            }

            $dettaglioRows = collect($dettaglio['righe'] ?? [])->map(function (array $riga) {
                $eta = $riga['eta'] ?? null;
                $minore = filled($eta) && (int) $eta < 18;
                $nottiTassate = (int) ($riga['notti_tassate'] ?? 0);
                $tassa = (float) ($riga['subtotale'] ?? 0);

                return [
                    'nominativo' => $riga['nome'] ?? 'Ospite',
                    'eta' => $eta,
                    'minore' => $minore,
                    'esente' => ! empty($riga['esente']),
                    'motivo' => $riga['motivo'] ?? null,
                    'codice_export' => $riga['codice'] ?? 0,
                    'notti_totali' => (int) ($riga['notti_totali'] ?? 0),
                    'notti_periodo' => (int) ($riga['notti_periodo'] ?? 0),
                    'notti_tassate' => $nottiTassate,
                    'pernottamenti_oltre_max' => (int) ($riga['notti_oltre_max'] ?? 0),
                    'tariffa' => $riga['aliquota'] === null ? null : (! empty($riga['esente']) ? 0.0 : (float) $riga['aliquota']),
                    'tassa' => $tassa,
                    'paga' => $tassa > 0,
                ];
            })->values();

            $personeTotali = $dettaglioRows->count();
            $minori = $dettaglioRows->where('minore', true)->count();
            $esentiTotali = $dettaglioRows->where('esente', true)->count();
            $paganti = $dettaglioRows->where('paga', true)->count();
            $adulti = max(0, $personeTotali - $minori);
            $riferimento = trim(($schedina->surname ? $schedina->surname.' ' : '').($schedina->name ?? '')) ?: ($dettaglioRows->first()['nominativo'] ?? '—');
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
            'reconciliationErrors' => $reconciliationErrors,
            'rows' => $rowsCollection,
            'schedeSummary' => $schedeCollection,
            'summary' => $summary,
            'esenzioniSummary' => $esenzioniSummary,
            'epilogo' => $epilogo,
        ];
    }

    private function buildRows(int $mese, int $anno, int $strutturaId, Struttura $struttura, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        if (! Schema::hasTable('schedina')) {
            return [];
        }

        $schedine = Schedina::where('is_arrive', 0)
            ->where('struttura_id', $strutturaId)
            ->get()->filter(fn ($schedina) => ($arrivo = $this->service->parseDate($schedina->arrive)) && $arrivo->betweenIncluded($this->dataDa, $this->dataA));
        if ($schedine->isEmpty()) {
            $this->configurazionePeriodo = $this->service->configurazioneAutomatica($struttura, $config, $this->dataDa);
            $this->service->validaConfigurazioneBellaria($struttura, $this->configurazionePeriodo, $this->dataDa);
            $this->validateStayTourScope($struttura, $this->configurazionePeriodo);
        }
        $righe = [];
        $this->calcoliSnapshot = [];

        foreach ($schedine as $schedina) {
            $arrivo = $this->service->parseDate($schedina->arrive);
            if (! $arrivo || ! $arrivo->betweenIncluded($this->dataDa, $this->dataA)) {
                continue;
            }
            $partenza = $this->service->parseDate($schedina->departure);
            $componenti = Componenti::where('schedina_id', $schedina->id)->get();
            $configMovimento = $this->service->configurazioneAutomatica($struttura, $config, $arrivo);
            if (! $configMovimento || $configMovimento->tassa_soggiorno === null || $configMovimento->giorni_massimo === null) {
                throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_tassa' => 'Configurare aliquota e limite prima di elaborare i movimenti Tassa.']);
            }
            $dettaglio = $this->service->dettaglioSchedina($schedina, $componenti, $configMovimento, $esenzioni, $struttura);

            $this->validateStayTourScope($struttura, $configMovimento);
            $this->configurazionePeriodo ??= $configMovimento;
            if ($this->configurazionePeriodo->regola_versione !== $configMovimento->regola_versione || count($dettaglio['profili_fiscali'] ?? []) > 1) {
                $this->configurazionePeriodo = new TassaDiSoggiorno(['struttura_id' => $strutturaId, 'ricevuta_immagine' => $config?->ricevuta_immagine]);
            }
            $this->calcoliSnapshot[$schedina->id] = [
                'configurazione' => $configMovimento->getAttributes(),
                'schedina' => array_intersect_key($schedina->getAttributes(), array_flip(['id', 'struttura_id', 'name', 'surname', 'scheda', 'arrive', 'departure'])),
                'dettaglio' => $dettaglio,
            ];

            foreach ($dettaglio['righe'] as $persona) {
                foreach ($persona['segmenti'] ?? [$persona] as $riga) {
                    $righe[] = [
                        'arrivo' => $arrivo?->toDateString(),
                        'partenza' => $partenza?->toDateString(),
                        'scheda' => $schedina->scheda,
                        'schedina_id' => $schedina->id,
                        'persona_id' => $riga['persona_id'] ?? null,
                        'persona_tipo' => $riga['persona_tipo'] ?? null,
                        'nominativo' => $riga['nome'],
                        'eta' => $riga['eta'],
                        'esente' => $riga['esente'],
                        'motivo' => $riga['motivo'],
                        'pernottamenti_imponibili' => $riga['notti_imponibili'],
                        'pernottamenti_oltre_max' => 0,
                        'tassa' => $riga['subtotale'],
                        'tariffa' => $riga['aliquota'],
                        'tipo' => $riga['codice'] ?? 0,
                        'data_reg' => ! empty($dettaglio['bellaria']) ? $arrivo?->toDateString() : now()->toDateString(),
                        'soggetti' => 1,
                    ];

                    if ($riga['notti_oltre_max'] > 0) {
                        $righe[] = [
                            'arrivo' => $arrivo?->toDateString(),
                            'partenza' => $partenza?->toDateString(),
                            'scheda' => $schedina->scheda,
                            'schedina_id' => $schedina->id,
                            'persona_id' => $riga['persona_id'] ?? null,
                            'persona_tipo' => $riga['persona_tipo'] ?? null,
                            'nominativo' => $riga['nome'],
                            'eta' => $riga['eta'],
                            'esente' => true,
                            'motivo' => 'Oltre giorni max',
                            'pernottamenti_imponibili' => 0,
                            'pernottamenti_oltre_max' => $riga['notti_oltre_max'],
                            'tassa' => 0,
                            'tariffa' => 0,
                            'tipo' => 777,
                            'data_reg' => ! empty($dettaglio['bellaria']) ? $arrivo?->toDateString() : now()->toDateString(),
                            'soggetti' => 1,
                        ];
                    }
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
        if (! $date) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $date;
        }
    }
}
