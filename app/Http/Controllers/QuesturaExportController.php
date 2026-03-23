<?php

namespace App\Http\Controllers;

use App\Models\QuesturaExport;
use App\Models\QuesturaTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\QuesturaTxtExportService;
use App\Services\QuesturaWebService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class QuesturaExportController extends Controller
{
    public function __construct(
        private QuesturaTxtExportService $service,
        private QuesturaWebService $webService,
    ) {
    }

    public function index(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        [$dal, $al] = $this->resolvePeriodo($request);
        $q = trim((string) $request->query('q', ''));
        $filtro = (string) $request->query('filtro', 'tutte');
        $schedine = $this->service->schedinePerPeriodo($struttura->id, $dal, $al);
        $analisi = $this->service->analizzaSchedine($schedine)
            ->when($filtro !== '' && $filtro !== 'tutte', function ($collection) use ($filtro) {
                return match ($filtro) {
                    'pronte' => $collection->where('valida', true)->values(),
                    'correggere' => $collection->where('valida', false)->values(),
                    'esportate' => $collection->filter(fn (array $item) => ((int) ($item['schedina']->questura_export_count ?? 0)) > 0)->values(),
                    'inviate' => $collection->filter(fn (array $item) => ((int) ($item['schedina']->questura_send_count ?? 0)) > 0)->values(),
                    default => $collection,
                };
            })
            ->when($q !== '', function ($collection) use ($q) {
                $needle = mb_strtolower($q);

                return $collection->filter(function (array $item) use ($needle) {
                    $schedina = $item['schedina'];
                    $haystack = mb_strtolower(trim(implode(' ', [
                        (string) ($schedina->scheda ?? ''),
                        (string) ($schedina->surname ?? ''),
                        (string) ($schedina->name ?? ''),
                    ])));

                    return str_contains($haystack, $needle);
                })->values();
            });

        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;
        $paginator = new LengthAwarePaginator(
            $analisi->forPage($page, $perPage)->values(),
            $analisi->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $storico = QuesturaExport::query()
            ->where('struttura_id', $struttura->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $trasmissioni = QuesturaTransmission::query()
            ->where('struttura_id', $struttura->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $latestTableSnapshot = collect(Storage::disk('local')->files('questura/tabelle/struttura_' . $struttura->id))
            ->filter(fn (string $path) => str_ends_with($path, '/manifest.json'))
            ->sortDesc()
            ->map(function (string $path) {
                $data = json_decode(Storage::disk('local')->get($path), true);
                return is_array($data) ? $data + ['manifest_path' => $path] : null;
            })
            ->filter()
            ->first();

        return view('questura.index', [
            'struttura' => $struttura,
            'credStatus' => $this->webService->credentialsStatus($struttura),
            'dal' => $dal,
            'al' => $al,
            'analisi' => $paginator,
            'totaleSchedine' => $analisi->count(),
            'totaleValide' => $analisi->where('valida', true)->count(),
            'totaleNonValide' => $analisi->where('valida', false)->count(),
            'storico' => $storico,
            'trasmissioni' => $trasmissioni,
            'filtro' => $filtro,
            'latestTableSnapshot' => $latestTableSnapshot,
        ]);
    }

    public function downloadPeriodo(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        try {
            [$dal, $al, $txt, $analisi] = $this->buildPeriodoTxt($struttura->id, $request);
        } catch (ValidationException $e) {
            [$dal, $al] = $this->resolvePeriodo($request);
            return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])->withErrors($e->errors());
        }
        $filename = $this->service->filename($dal, $al);
        $export = $this->storeExport(
            strutturaId: $struttura->id,
            userId: $request->user()?->id,
            dal: $dal,
            al: $al,
            filename: $filename,
            txt: $txt,
            schedine: $analisi->pluck('schedina')
        );

        return response($this->toDownloadEncoding($txt), 200, [
            'Content-Type' => 'text/plain; charset=ISO-8859-1',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
        ]);
    }

    public function downloadSchedina(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $schedina = Schedina::query()
            ->with('componenti')
            ->where('struttura_id', $struttura->id)
            ->where('circuito', 'schedina')
            ->findOrFail($id);

        try {
            $txt = $this->service->buildTxtPerSchedina($schedina);
        } catch (ValidationException $e) {
            return redirect()
                ->route('questura.index', ['dal' => $schedina->arrive, 'al' => $schedina->arrive])
                ->withErrors($e->errors());
        }
        $filename = $this->service->filenamePerSchedina($schedina);
        $export = $this->storeExport(
            strutturaId: $struttura->id,
            userId: $request->user()?->id,
            dal: Carbon::parse($schedina->arrive),
            al: Carbon::parse($schedina->arrive),
            filename: $filename,
            txt: $txt,
            schedine: collect([$schedina])
        );

        return response($this->toDownloadEncoding($txt), 200, [
            'Content-Type' => 'text/plain; charset=ISO-8859-1',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
        ]);
    }

    public function verifyPeriodo(Request $request)
    {
        return $this->runWsAction($request, 'verify');
    }

    public function downloadOfficialTables(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $credStatus = $this->webService->credentialsStatus($struttura);
        if (!$credStatus['configured']) {
            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => 'Credenziali Questura incomplete per scaricare le tabelle ufficiali: ' . implode(', ', $credStatus['missing']) . '.',
            ]);
        }

        try {
            $result = $this->webService->downloadReferenceTables($struttura);
        } catch (Throwable $e) {
            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => 'Download tabelle ufficiali non riuscito: ' . $e->getMessage(),
            ]);
        }

        if (!($result['ok'] ?? false) || empty($result['tables'])) {
            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => $result['message'] ?? 'Download tabelle ufficiali non disponibile.',
            ]);
        }

        $timestamp = now()->format('Ymd_His');
        $basePath = 'questura/tabelle/struttura_' . $struttura->id . '/' . $timestamp;
        $manifest = [
            'downloaded_at' => now()->toIso8601String(),
            'struttura_id' => $struttura->id,
            'struttura' => $struttura->nome_struttura,
            'simulation' => (bool) ($result['simulated'] ?? false),
            'sync' => $result['sync'] ?? [],
            'tables' => [],
        ];

        foreach ($result['tables'] as $table) {
            $filename = (string) ($table['filename'] ?? 'questura_tabella.csv');
            $relativePath = $basePath . '/' . $filename;
            Storage::disk('local')->put($relativePath, $table['csv'] ?? '');
            $manifest['tables'][] = [
                'type' => $table['type'] ?? null,
                'filename' => $filename,
                'path' => $relativePath,
            ];
        }

        $manifestPath = $basePath . '/manifest.json';
        Storage::disk('local')->put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $zipFilename = 'questura_tabelle_ufficiali_' . $timestamp . '.zip';
        $zipPath = storage_path('app/' . $basePath . '/' . $zipFilename);

        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($manifest['tables'] as $item) {
                    $zip->addFile(storage_path('app/' . $item['path']), $item['filename']);
                }
                $zip->addFile(storage_path('app/' . $manifestPath), 'manifest.json');
                $zip->close();

                return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
            }
        }

        return Storage::disk('local')->download($manifest['tables'][0]['path'], $manifest['tables'][0]['filename'], [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function sendPeriodo(Request $request)
    {
        return $this->runWsAction($request, 'send');
    }

    public function downloadStorico(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $export = QuesturaExport::query()
            ->where('struttura_id', $struttura->id)
            ->findOrFail($id);

        abort_unless(Storage::disk('local')->exists($export->path), 404);

        return Storage::disk('local')->download($export->path, $export->filename, [
            'Content-Type' => 'text/plain; charset=ISO-8859-1',
        ]);
    }

    public function downloadReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $transmission = QuesturaTransmission::query()
            ->where('struttura_id', $struttura->id)
            ->where('mode', 'send')
            ->findOrFail($id);

        if ($transmission->receipt_path && Storage::disk('local')->exists($transmission->receipt_path)) {
            return Storage::disk('local')->download($transmission->receipt_path, $transmission->receipt_filename ?: 'ricevuta_questura.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        }

        try {
            $result = $this->webService->receipt($struttura, Carbon::parse($transmission->executed_at ?: $transmission->created_at));
        } catch (Throwable $e) {
            $this->updateTransmissionWithFailure($transmission, $e->getMessage(), [
                'exception' => $e::class,
            ]);

            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => 'Recupero ricevuta non riuscito: ' . $e->getMessage(),
            ]);
        }

        if (empty($result['receipt_binary'])) {
            $this->updateTransmissionWithFailure($transmission, $result['message'] ?? 'Ricevuta non disponibile.', Arr::except($result, ['receipt_binary']));

            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => $result['message'] ?? 'Ricevuta non disponibile.',
            ]);
        }

        $receiptFilename = 'ricevuta_questura_' . Carbon::parse($transmission->executed_at ?: $transmission->created_at)->format('Ymd_His') . '.pdf';
        $receiptPath = 'questura/ricevute/struttura_' . $struttura->id . '/' . $receiptFilename;
        Storage::disk('local')->put($receiptPath, $result['receipt_binary']);

        $transmission->update([
            'status' => 'success',
            'response_code' => $result['response_code'] ?? null,
            'response_message' => $result['message'] ?? 'Ricevuta scaricata.',
            'response_detail' => $result['detail'] ?? null,
            'result' => Arr::except($result, ['receipt_binary']),
            'receipt_filename' => $receiptFilename,
            'receipt_path' => $receiptPath,
            'executed_at' => now(),
        ]);

        return Storage::disk('local')->download($receiptPath, $receiptFilename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function runWsAction(Request $request, string $mode)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $credStatus = $this->webService->credentialsStatus($struttura);
        if (!$credStatus['configured']) {
            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => 'Credenziali per invio diretto Questura incomplete: ' . implode(', ', $credStatus['missing']) . '.',
            ]);
        }

        try {
            [$dal, $al, $txt, $analisi] = $this->buildPeriodoTxt($struttura->id, $request);
        } catch (ValidationException $e) {
            [$dal, $al] = $this->resolvePeriodo($request);
            return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])->withErrors($e->errors());
        }

        $schedinaIds = $analisi->pluck('schedina.id')->filter()->values()->all();
        $filename = $this->service->filename($dal, $al);
        $payload = [
            'dal' => $dal->toDateString(),
            'al' => $al->toDateString(),
            'filename' => $filename,
            'schedina_ids' => $schedinaIds,
        ];

        try {
            $result = $mode === 'verify'
                ? $this->webService->verify($struttura, $this->toDownloadEncoding($txt))
                : $this->webService->send($struttura, $this->toDownloadEncoding($txt));
        } catch (Throwable $e) {
            $transmission = $this->storeTransmission(
                strutturaId: $struttura->id,
                userId: $request->user()?->id,
                exportId: null,
                mode: $mode,
                dal: $dal,
                al: $al,
                schedinaIds: $schedinaIds,
                righeCount: substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0),
                payload: $payload,
                result: ['exception' => $e::class],
                status: 'error',
                responseMessage: $e->getMessage(),
            );

            return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])->withErrors([
                'questura_ws' => strtoupper($mode) . ' Questura non riuscito: ' . $e->getMessage(),
            ]);
        }

        $transmission = $this->storeTransmission(
            strutturaId: $struttura->id,
            userId: $request->user()?->id,
            exportId: null,
            mode: $mode,
            dal: $dal,
            al: $al,
            schedinaIds: $schedinaIds,
            righeCount: substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0),
            payload: $payload,
            result: Arr::except($result, ['receipt_binary']),
            status: ($result['ok'] ?? false) ? 'success' : 'error',
            responseCode: $result['response_code'] ?? null,
            responseMessage: $result['message'] ?? null,
            responseDetail: $result['detail'] ?? null,
        );

        if ($mode === 'send' && ($result['ok'] ?? false) && !empty($schedinaIds)) {
            Schedina::query()
                ->whereIn('id', $schedinaIds)
                ->update([
                    'questura_sent_at' => now(),
                    'questura_send_count' => DB::raw('COALESCE(questura_send_count, 0) + 1'),
                    'last_questura_transmission_id' => $transmission->id,
                ]);
        }

        return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])->with(($result['ok'] ?? false) ? 'success' : 'error', $this->buildWsFlashMessage($mode, $result));
    }

    private function buildPeriodoTxt(int $strutturaId, Request $request): array
    {
        [$dal, $al] = $this->resolvePeriodo($request);
        $schedine = $this->service->schedinePerPeriodo($strutturaId, $dal, $al);
        $analisi = $this->service->analizzaSchedine($schedine);

        try {
            $txt = $this->service->buildTxt($analisi);
        } catch (ValidationException $e) {
            throw ValidationException::withMessages($e->errors());
        }

        return [$dal, $al, $txt, $analisi];
    }

    private function resolvePeriodo(Request $request): array
    {
        $dal = $request->filled('dal') ? Carbon::parse($request->input('dal')) : now();
        $al = $request->filled('al') ? Carbon::parse($request->input('al')) : $dal->copy();

        if ($al->lessThan($dal)) {
            [$dal, $al] = [$al, $dal];
        }

        return [$dal->startOfDay(), $al->startOfDay()];
    }

    private function toDownloadEncoding(string $txt): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $txt) ?: $txt;
    }

    private function resolveStruttura(Request $request): ?Struttura
    {
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        return $strutturaId ? Struttura::query()->find($strutturaId) : null;
    }

    private function storeExport(int $strutturaId, ?int $userId, Carbon $dal, Carbon $al, string $filename, string $txt, $schedine): QuesturaExport
    {
        $timestamp = now();
        $storedBasename = $timestamp->format('Ymd_His_u') . '_' . $filename;
        $path = 'questura/struttura_' . $strutturaId . '/' . $storedBasename;
        Storage::disk('local')->put($path, $this->toDownloadEncoding($txt));

        $righeCount = substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0);
        $schedinaIds = $schedine->pluck('id')->filter()->values()->all();

        $export = QuesturaExport::query()->create([
            'struttura_id' => $strutturaId,
            'user_id' => $userId,
            'dal' => $dal->toDateString(),
            'al' => $al->toDateString(),
            'filename' => $filename,
            'path' => $path,
            'schedine_count' => count($schedinaIds),
            'righe_count' => $righeCount,
            'schedina_ids' => $schedinaIds,
        ]);

        if (!empty($schedinaIds)) {
            Schedina::query()
                ->whereIn('id', $schedinaIds)
                ->update([
                    'questura_exported_at' => $timestamp,
                    'questura_export_count' => DB::raw('COALESCE(questura_export_count, 0) + 1'),
                    'last_questura_export_id' => $export->id,
                ]);
        }

        return $export;
    }

    private function storeTransmission(
        int $strutturaId,
        ?int $userId,
        ?int $exportId,
        string $mode,
        Carbon $dal,
        Carbon $al,
        array $schedinaIds,
        int $righeCount,
        array $payload,
        array $result,
        string $status,
        ?string $responseCode = null,
        ?string $responseMessage = null,
        ?string $responseDetail = null,
    ): QuesturaTransmission {
        return QuesturaTransmission::query()->create([
            'struttura_id' => $strutturaId,
            'user_id' => $userId,
            'questura_export_id' => $exportId,
            'mode' => $mode,
            'scope_type' => 'periodo',
            'dal' => $dal->toDateString(),
            'al' => $al->toDateString(),
            'schedina_ids' => $schedinaIds,
            'schedine_count' => count($schedinaIds),
            'righe_count' => $righeCount,
            'status' => $status,
            'response_code' => $responseCode,
            'response_message' => $responseMessage,
            'response_detail' => $responseDetail,
            'payload' => $payload,
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

    private function updateTransmissionWithFailure(QuesturaTransmission $transmission, string $message, array $result = []): void
    {
        $transmission->update([
            'status' => 'error',
            'response_message' => $message,
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

    private function buildWsFlashMessage(string $mode, array $result): string
    {
        $prefix = $mode === 'verify' ? 'Verifica invio diretto Questura' : 'Invio diretto Questura';
        return $prefix . ': ' . ($result['message'] ?? (($result['ok'] ?? false) ? 'Operazione completata.' : 'Operazione non completata.'));
    }
}
