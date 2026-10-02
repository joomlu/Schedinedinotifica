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
use App\Services\EsitoTrasmissioneQuestura;
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
        // No remote artifacts or catalog writes while their validation is unavailable.
        return redirect()->route('questura.index')->withErrors([
            'questura_ws' => EsitoTrasmissioneQuestura::crea('unavailable', 'tables')['message'],
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
        // Historical artifacts stay untouched, but are not served without validation.
        return redirect()->route('questura.index')->withErrors([
            'questura_ws' => EsitoTrasmissioneQuestura::crea('unavailable', 'receipt')['message'],
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
        } catch (Throwable) {
            $result = EsitoTrasmissioneQuestura::crea('technical_error', $mode === 'send' ? 'send' : 'test');
        }
        $result = EsitoTrasmissioneQuestura::sanifica($result);

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
            result: $result,
            status: $result['state'],
            responseCode: $result['response_code'] ?? null,
            responseMessage: $result['message'] ?? null,
            responseDetail: $result['detail'] ?? null,
        );

        // No official acceptance parser: attempts never increment confirmed-send counters.
        return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])
            ->with(in_array($result['state'], ['technical_error', 'rejected'], true) ? 'error' : 'warning', $result['message']);
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
        $result = EsitoTrasmissioneQuestura::sanifica($result);
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
            'status' => $result['state'],
            'response_code' => null,
            'response_message' => $result['message'],
            'response_detail' => null,
            'payload' => $payload,
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

}
