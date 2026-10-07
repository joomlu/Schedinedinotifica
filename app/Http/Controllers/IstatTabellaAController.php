<?php

namespace App\Http\Controllers;

use App\Models\IstatExport;
use App\Models\IstatTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\IstatTabellaAService;
use App\Services\IstatWebService;
use App\Services\EsitoTrasmissioneIstat;
use App\Support\StrutturaAccess;
use App\Services\IstatOperationService;
use App\Services\IstatPayloadStore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class IstatTabellaAController extends Controller
{
    public function __construct(
        private IstatTabellaAService $service,
        private IstatWebService $webService,
    ) {
    }

    public function index(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        [$dal, $al] = $this->resolvePeriodo($request);
        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;
        $paginator = new LengthAwarePaginator(
            $analysis['schedine']->forPage($page, $perPage)->values(),
            $analysis['schedine']->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $storico = IstatExport::query()->where('struttura_id', $struttura->id)->latest('id')->limit(10)->get();
        $trasmissioni = IstatTransmission::query()->where('struttura_id', $struttura->id)->latest('id')->limit(10)->get();
        $attemptActors = \Illuminate\Support\Facades\DB::table('istat_transmission_events')
            ->where('struttura_id', $struttura->id)->whereIn('istat_transmission_id', $trasmissioni->pluck('id'))
            ->where('status', 'pending')->orderByDesc('id')->get()->groupBy('istat_transmission_id')
            ->map(fn ($events) => $events->first()->user_id);
        $reconciliations = \Illuminate\Support\Facades\DB::table('istat_transmission_events')
            ->where('struttura_id', $struttura->id)->whereIn('istat_transmission_id', $trasmissioni->pluck('id'))
            ->where('status', 'portal_reconciled')->orderBy('id')->get()->groupBy('istat_transmission_id');
        $regionSupported = $this->isSupportedRegion($struttura);
        $previewHash = null;
        $previewRecords = [];
        if ($regionSupported && $analysis['valida']) {
            try {
                $previewXml = $this->service->buildXml($struttura, $dal, $al, $analysis);
                $previewHash = hash('sha256', $previewXml);
                $previewRecords = $this->service->previewRecords($previewXml);
            } catch (ValidationException) {
                // Gli errori origine sono già esposti nell’analisi.
            }
        }

        return response()->view('istat_tabella_a.index', [
            'previewHash' => $previewHash,
            'previewRecords' => $previewRecords,
            'reconciliations' => $reconciliations,
            'attemptActors' => $attemptActors,
            'struttura' => $struttura,
            'credStatus' => $this->webService->credentialsStatus($struttura),
            'dal' => $dal,
            'al' => $al,
            'analysis' => $analysis,
            'schedinePaginator' => $paginator,
            'storico' => $storico,
            'trasmissioni' => $trasmissioni,
            'regionSupported' => $regionSupported,
            'regionMessage' => $regionSupported ? null : 'Questo modulo è attualmente configurato solo per strutture in Emilia-Romagna.',
        ])->header('Cache-Control', 'private, no-store');
    }

    public function configure(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        $validator = \Illuminate\Support\Facades\Validator::make($request->only('username', 'password', 'codice'), [
            'username' => 'nullable|string|max:100', 'password' => 'nullable|string|max:100',
            'codice' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_config' => 'Configurazione non valida. Controllare codice e lunghezza delle credenziali.']);
        }
        $data = ['istat_codice_struttura' => $request->input('codice'), 'istat_ws_url' => null];
        foreach (['username', 'password'] as $field) {
            if ($request->filled($field)) {
                $data['istat_'.$field] = $request->input($field);
            }
        }
        try {
            $struttura->update($data);
        } catch (Throwable) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_config' => 'Configurazione non salvata. Nessun dettaglio sensibile disponibile.']);
        }
        return redirect()->route('istat.tabella_a.index')->with('success', 'Configurazione Ross1000 salvata. Nessuna richiesta effettuata.');
    }

    public function registerManual(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $export = IstatExport::where('struttura_id', $struttura->id)->findOrFail($id);
        if (!$request->boolean('conferma_portale')) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_ws' => 'Confermare caricamento e verifica degli esiti sul portale Ross1000.']);
        }
        try {
            (new IstatPayloadStore())->read($export);
            $operations = new IstatOperationService();
            $tx = $operations->reserve($struttura, $export, 'manual', $request->user()->id);
            $operations->finalize($tx, EsitoTrasmissioneIstat::crea('manual_registered', 'manual'));
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index')->withErrors($e->errors());
        }
        return redirect()->route('istat.tabella_a.index')->with('warning', 'Consegna manuale registrata; nessuna ricevuta ufficiale creata.');
    }

    public function reconcile(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $tx = IstatTransmission::where('struttura_id', $struttura->id)->findOrFail($id);
        if (!$request->boolean('conferma_portale')) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_ws' => 'Prima verificare lo storico importazioni e correggere o annullare i record sul portale Ross1000.']);
        }
        $procedure = $request->input('procedura', 'verifica_portale');
        if (!is_string($procedure)) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_ws' => 'Procedura sul portale non valida.']);
        }
        try {
            (new IstatOperationService())->reconcile($tx, $request->user()->id, $procedure);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index')->withErrors($e->errors());
        }
        return redirect()->route('istat.tabella_a.index')->with('warning', 'Verifica e rettifica sul portale dichiarate dall’operatore. Generare una nuova anteprima.');
    }

    public function saveControllo(Request $request)
    {
        $this->resolveStruttura($request);
        [$dal, $al] = $this->resolvePeriodo($request);
        return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
            ->withErrors(['istat_tabella_a' => 'Il riepilogo giornaliero di Tabella A Emilia-Romagna e solo informativo e non puo essere modificato da questa schermata.']);
    }

    public function downloadXml(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        abort_unless($struttura, 403);
        $unsupported = $this->unsupportedRegionResponse($struttura, $request);
        if ($unsupported) {
            return $unsupported;
        }

        [$dal, $al] = $this->resolvePeriodo($request);
        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);
        try {
            $xml = $this->service->buildXml($struttura, $dal, $al, $analysis);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])->withErrors($e->errors());
        }

        $filename = $this->service->filename($dal, $al);
        $export = (new IstatPayloadStore())->store($struttura->id, $request->user()?->id, $dal, $al, $filename, $xml, $analysis['schedine']);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
        ]);
    }

    public function downloadStorico(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        abort_unless($struttura, 403);

        $export = IstatExport::query()->where('struttura_id', $struttura->id)->findOrFail($id);
        try {
            $xml = (new IstatPayloadStore())->read($export);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index')->withErrors($e->errors());
        }
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="istat_export_'.(int) $export->id.'.xml"']);
    }

    public function verifyPeriodo(Request $request)
    {
        return $this->runWsAction($request, 'verify');
    }

    public function sendPeriodo(Request $request)
    {
        return $this->runWsAction($request, 'send');
    }

    public function downloadReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        abort_unless($struttura, 403);

        $transmission = IstatTransmission::query()->where('struttura_id', $struttura->id)->findOrFail($id);
        // Historical PDFs/details may contain remote secrets. Keep them untouched,
        // but generate a safe local summary without reading or merging that material.
        return response($this->buildOperatorReceiptPdf($struttura, $transmission), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="riepilogo_locale_istat_' . (int) $transmission->id . '.pdf"',
        ]);
    }

    public function printSummary(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        abort_unless($struttura, 403);
        $unsupported = $this->unsupportedRegionResponse($struttura, $request);
        if ($unsupported) {
            return $unsupported;
        }

        [$dal, $al] = $this->resolvePeriodo($request);
        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);

        $provenienze = $analysis['schedine']
            ->groupBy(fn (Schedina $schedina) => trim((string) ($schedina->or_country ?: $schedina->or_city ?: 'Non indicata')))
            ->map(fn ($group, $label) => ['label' => $label, 'count' => $group->count()])
            ->sortByDesc('count')
            ->take(10)
            ->values();

        return view('istat_tabella_a.print-summary', [
            'struttura' => $struttura,
            'dal' => $dal,
            'al' => $al,
            'analysis' => $analysis,
            'provenienze' => $provenienze,
        ]);
    }

    private function runWsAction(Request $request, string $mode)
    {
        $struttura = $this->resolveStruttura($request);
        abort_unless($struttura, 403);
        $unsupported = $this->unsupportedRegionResponse($struttura, $request);
        if ($unsupported) {
            return $unsupported;
        }
        [$dal, $al] = $this->resolvePeriodo($request);

        if ($mode === 'send' && config('istat.enabled', false) !== true) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_ws' => 'Trasporto ISTAT disabilitato.']);
        }
        if ($mode === 'send' && !$this->webService->credentialsStatus($struttura)['configured']) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
                ->withErrors(['istat_ws' => 'Credenziali invio diretto ISTAT incomplete.']);
        }

        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);
        try {
            $xml = $this->service->buildXml($struttura, $dal, $al, $analysis);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])->withErrors($e->errors());
        }

        if ($mode === 'send' && !hash_equals(hash('sha256', $xml), (string) $request->input('preview_hash'))) {
            return redirect()->route('istat.tabella_a.index')->withErrors(['istat_ws' => 'Dati cambiati dopo l’anteprima. Verificare una nuova anteprima prima dell’invio.']);
        }
        $export = (new IstatPayloadStore())->store($struttura->id, $request->user()?->id, $dal, $al, $this->service->filename($dal, $al), $xml, $analysis['schedine']);
        try {
            $transmission = (new IstatOperationService())->reserve($struttura, $export, $mode, $request->user()?->id);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index')->withErrors($e->errors());
        }

        try {
            $result = $mode === 'verify'
                ? $this->webService->verify($struttura, $xml, $dal, $al)
                : $this->webService->send($struttura, $xml, $dal, $al);
        } catch (Throwable) {
            $result = EsitoTrasmissioneIstat::crea($mode === 'send' ? 'uncertain' : 'not_delivered', $mode);
        }
        // A second closed-schema projection protects the persistence boundary.
        $result = EsitoTrasmissioneIstat::sanifica($result);

        (new IstatOperationService())->finalize($transmission, $result);

        // Attempts remain in the transmission history. Without an official acceptance
        // parser, neither simulation nor HTTP delivery updates confirmed-send counters.

        return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
            ->with(in_array($result['state'], ['technical_error', 'rejected'], true) ? 'error' : 'warning', $result['message']);
    }

    private function buildOperatorReceiptPdf(Struttura $struttura, IstatTransmission $transmission): string
    {
        $lines = [
            'Riepilogo locale ISTAT - NON e una ricevuta ufficiale',
            'Identificativo trasmissione locale: ' . (int) $transmission->id,
            'Tipo operazione: ' . ($transmission->mode === 'verify' ? ($transmission->status === 'validated' ? 'Validazione locale' : 'Verifica registrata nello storico') : ($transmission->mode === 'manual' ? 'Consegna sul portale' : 'Invio diretto')),
            'Periodo: ' . optional($transmission->dal)->format('d/m/Y') . ' - ' . optional($transmission->al)->format('d/m/Y'),
            'Eseguito il: ' . optional($transmission->executed_at ?: $transmission->created_at)->format('d/m/Y H:i'),
            'Schedine incluse: ' . (string) ($transmission->schedine_count ?? 0),
            'Giornate XML: ' . (string) ($transmission->movimenti_count ?? 0),
            'Esito: ' . $transmission->esitoSicuro()['state'],
            $transmission->esitoSicuro()['message'],
        ];

        $text = implode("\n", $lines);
        $stream = "BT\n/F1 11 Tf\n40 790 Td\n14 TL\n";
        foreach (explode("\n", $text) as $i => $line) {
            if ($i > 0) {
                $stream .= "T*\n";
            }
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= '(' . $escaped . ") Tj\n";
        }
        $stream .= "ET";
        $len = strlen($stream);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $offsets[] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $offsets[] = strlen($pdf);
        $pdf .= "5 0 obj\n<< /Length $len >>\nstream\n$stream\nendstream\nendobj\n";
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

        return $pdf;
    }

    private function resolveStruttura(Request $request): ?Struttura
    {
        return StrutturaAccess::resolve($request);
    }

    private function resolvePeriodo(Request $request): array
    {
        $reference = trim((string) $request->input('mese', ''));
        try {
            if ($reference !== '') {
                if (!preg_match('/^\d{4}-\d{2}$/D', $reference)) {
                    throw new \InvalidArgumentException();
                }
                $day = Carbon::createFromFormat('!Y-m-d', $reference.'-01');
                if ($day->format('Y-m') !== $reference) {
                    throw new \InvalidArgumentException();
                }
                return [$day->copy()->startOfMonth(), $day->copy()->endOfMonth()->startOfDay()];
            }
            $dates = [];
            foreach (['dal' => now()->startOfMonth(), 'al' => now()->endOfMonth()] as $field => $default) {
                $value = (string) $request->input($field, $default->toDateString());
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
                    throw new \InvalidArgumentException();
                }
                $date = Carbon::createFromFormat('!Y-m-d', $value);
                if ($date->format('Y-m-d') !== $value) {
                    throw new \InvalidArgumentException();
                }
                $dates[] = $date;
            }
            if ($dates[1]->lt($dates[0])) {
                throw new \InvalidArgumentException();
            }
            if ($dates[0]->diffInDays($dates[1]) > 366) {
                throw new \InvalidArgumentException();
            }
            return [$dates[0], $dates[1]];
        } catch (\Throwable) {
            throw ValidationException::withMessages(['istat_periodo' => 'Periodo non valido: usa date reali in formato AAAA-MM-GG e un intervallo ordinato.']);
        }
    }

    private function isSupportedRegion(Struttura $struttura): bool
    {
        $normalized = Str::of((string) ($struttura->regione ?? ''))
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', ' ')
            ->trim()
            ->value();

        return in_array($normalized, ['EMILIA ROMAGNA', 'EMILIA-ROMAGNA'], true);
    }

    private function unsupportedRegionResponse(Struttura $struttura, Request $request)
    {
        if ($this->isSupportedRegion($struttura)) {
            return null;
        }

        [$dal, $al] = $this->resolvePeriodo($request);

        return redirect()
            ->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
            ->withErrors(['istat_ws' => 'Tabella A Emilia-Romagna è disponibile solo per strutture con regione Emilia-Romagna.']);
    }
}
