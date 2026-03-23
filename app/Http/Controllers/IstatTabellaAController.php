<?php

namespace App\Http\Controllers;

use App\Models\IstatExport;
use App\Models\IstatTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\IstatTabellaAService;
use App\Services\IstatWebService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $regionSupported = $this->isSupportedRegion($struttura);

        return view('istat_tabella_a.index', [
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
        ]);
    }

    public function saveControllo(Request $request)
    {
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
        try {
            $xml = $this->service->buildXml($struttura, $dal, $al);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])->withErrors($e->errors());
        }

        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);
        $filename = $this->service->filename($dal, $al);
        $export = $this->storeExport($struttura->id, $request->user()?->id, $dal, $al, $filename, $xml, $analysis['schedine']);

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
        abort_unless(Storage::disk('local')->exists($export->path), 404);

        return Storage::disk('local')->download($export->path, $export->filename, ['Content-Type' => 'application/xml; charset=UTF-8']);
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
        if ($transmission->receipt_path && Storage::disk('local')->exists($transmission->receipt_path)) {
            return Storage::disk('local')->download($transmission->receipt_path, $transmission->receipt_filename ?: 'ricevuta_istat.pdf', ['Content-Type' => 'application/pdf']);
        }

        $result = null;
        try {
            $result = $this->webService->receipt($struttura, Carbon::parse($transmission->executed_at ?: $transmission->created_at));
        } catch (Throwable $e) {
            // Ross1000 non espone ancora qui una ricevuta remota integrata: costruiamo
            // una ricevuta operativa locale basata sull'esito registrato, così il circuito
            // resta chiuso per l'operatore anche in modalità reale.
            $result = [
                'ok' => $transmission->status === 'success',
                'message' => 'Ricevuta operativa locale generata dal sistema.',
                'detail' => $transmission->response_detail ?: $e->getMessage(),
                'receipt_binary' => $this->buildOperatorReceiptPdf($struttura, $transmission),
                'generated_locally' => true,
            ];
        }

        if (empty($result['receipt_binary'])) {
            $result['receipt_binary'] = $this->buildOperatorReceiptPdf($struttura, $transmission);
            $result['generated_locally'] = true;
            $result['message'] = $result['message'] ?? 'Ricevuta operativa locale generata dal sistema.';
        }

        $filename = 'ricevuta_istat_' . Carbon::parse($transmission->executed_at ?: $transmission->created_at)->format('Ymd_His') . '.pdf';
        $path = 'istat/ricevute/struttura_' . $struttura->id . '/' . $filename;
        Storage::disk('local')->put($path, $result['receipt_binary']);
        $transmission->update([
            'receipt_filename' => $filename,
            'receipt_path' => $path,
            'response_message' => $result['message'] ?? $transmission->response_message,
            'result' => array_merge($transmission->result ?? [], collect($result)->except('receipt_binary')->all()),
        ]);

        return Storage::disk('local')->download($path, $filename, ['Content-Type' => 'application/pdf']);
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

        if (!$this->webService->credentialsStatus($struttura)['configured']) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
                ->withErrors(['istat_ws' => 'Credenziali invio diretto ISTAT incomplete.']);
        }

        try {
            $xml = $this->service->buildXml($struttura, $dal, $al);
        } catch (ValidationException $e) {
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])->withErrors($e->errors());
        }

        $analysis = $this->service->analysePeriodo($struttura, $dal, $al);
        $export = $this->storeExport($struttura->id, $request->user()?->id, $dal, $al, $this->service->filename($dal, $al), $xml, $analysis['schedine']);
        $transmission = IstatTransmission::query()->create([
            'struttura_id' => $struttura->id,
            'user_id' => $request->user()?->id,
            'istat_export_id' => $export->id,
            'mode' => $mode,
            'dal' => $dal,
            'al' => $al,
            'schedina_ids' => $analysis['schedine']->pluck('id')->values()->all(),
            'schedine_count' => $analysis['schedine']->count(),
            'movimenti_count' => count($analysis['rows']),
            'status' => 'pending',
            'payload' => ['filename' => $export->filename],
        ]);

        try {
            $result = $mode === 'verify'
                ? $this->webService->verify($struttura, $xml, $dal, $al)
                : $this->webService->send($struttura, $xml, $dal, $al);
        } catch (Throwable $e) {
            $transmission->update([
                'status' => 'error',
                'response_message' => $e->getMessage(),
                'response_detail' => $e->getTraceAsString(),
                'executed_at' => now(),
            ]);
            return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])->withErrors(['istat_ws' => strtoupper($mode) . ' ISTAT non riuscito: ' . $e->getMessage()]);
        }

        $transmission->update([
            'status' => ($result['ok'] ?? false) ? 'success' : 'error',
            'response_code' => $result['response_code'] ?? null,
            'response_message' => $result['message'] ?? null,
            'response_detail' => $result['detail'] ?? null,
            'result' => $result,
            'executed_at' => now(),
        ]);

        if (($result['ok'] ?? false) && $mode === 'send') {
            Schedina::query()
                ->whereIn('id', $analysis['schedine']->pluck('id')->all())
                ->update([
                    'istat_sent_at' => now(),
                    'istat_send_count' => DB::raw('COALESCE(istat_send_count, 0) + 1'),
                    'last_istat_transmission_id' => $transmission->id,
                ]);
        }

        return redirect()->route('istat.tabella_a.index', ['dal' => $dal->toDateString(), 'al' => $al->toDateString()])
            ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Operazione completata.');
    }

    private function storeExport(int $strutturaId, ?int $userId, Carbon $dal, Carbon $al, string $filename, string $xml, $schedine): IstatExport
    {
        $storedBasename = now()->format('Ymd_His') . '_' . $filename;
        $path = 'istat/struttura_' . $strutturaId . '/' . $storedBasename;
        Storage::disk('local')->put($path, $xml);

        $export = IstatExport::query()->create([
            'struttura_id' => $strutturaId,
            'user_id' => $userId,
            'dal' => $dal,
            'al' => $al,
            'filename' => $filename,
            'path' => $path,
            'schedine_count' => $schedine->count(),
            'movimenti_count' => Carbon::parse($dal)->diffInDays(Carbon::parse($al)) + 1,
            'schedina_ids' => $schedine->pluck('id')->values()->all(),
        ]);

        Schedina::query()->whereIn('id', $schedine->pluck('id')->all())->update([
            'istat_exported_at' => now(),
            'istat_export_count' => DB::raw('COALESCE(istat_export_count, 0) + 1'),
            'last_istat_export_id' => $export->id,
        ]);

        return $export;
    }

    private function buildOperatorReceiptPdf(Struttura $struttura, IstatTransmission $transmission): string
    {
        $lines = [
            'Tabella A Emilia-Romagna - Esito operativo invio',
            'Struttura: ' . ($struttura->nome_struttura ?: 'Struttura'),
            'Codice Ross1000: ' . ($struttura->istat_codice_struttura ?: '-'),
            'Tipo operazione: ' . ($transmission->mode === 'verify' ? 'Verifica invio diretto' : 'Invio diretto'),
            'Periodo: ' . optional($transmission->dal)->format('d/m/Y') . ' - ' . optional($transmission->al)->format('d/m/Y'),
            'Eseguito il: ' . optional($transmission->executed_at ?: $transmission->created_at)->format('d/m/Y H:i'),
            'Schedine incluse: ' . (string) ($transmission->schedine_count ?? 0),
            'Movimenti XML: ' . (string) ($transmission->movimenti_count ?? 0),
            'Esito: ' . ($transmission->status === 'success' ? 'OK' : ($transmission->status === 'error' ? 'ERRORE' : 'IN ATTESA')),
            'Codice risposta: ' . ($transmission->response_code ?: '-'),
            'Messaggio: ' . ($transmission->response_message ?: '-'),
        ];

        $detail = trim((string) ($transmission->response_detail ?: ''));
        if ($detail !== '') {
            $detail = preg_replace('/\s+/', ' ', $detail) ?: $detail;
            foreach (str_split($detail, 90) as $chunk) {
                $lines[] = $chunk;
            }
        } else {
            $lines[] = 'Dettaglio: non disponibile.';
        }

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
        $id = StrutturaCorrente::getId() ?: $request->user()?->struttura_id;
        return $id ? Struttura::query()->find($id) : null;
    }

    private function resolvePeriodo(Request $request): array
    {
        $reference = trim((string) $request->input('mese', $request->query('mese', '')));
        if ($reference !== '') {
            $day = Carbon::parse($reference)->startOfMonth();
            return [$day->copy()->startOfMonth(), $day->copy()->endOfMonth()];
        }

        $dal = Carbon::parse($request->input('dal', $request->query('dal', now()->startOfMonth()->toDateString())))->startOfDay();
        $al = Carbon::parse($request->input('al', $request->query('al', now()->endOfMonth()->toDateString())))->startOfDay();
        return [$dal->copy()->startOfMonth(), $al->copy()->endOfMonth()];
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
