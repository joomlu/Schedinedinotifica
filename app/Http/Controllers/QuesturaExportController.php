<?php

namespace App\Http\Controllers;

use App\Exceptions\QuesturaTransportDisabledException;
use App\Models\QuesturaExport;
use App\Models\QuesturaReceipt;
use App\Models\QuesturaTransmission;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\QuesturaTxtExportService;
use App\Services\QuesturaWebService;
use App\Services\QuesturaRetentionService;
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
        private QuesturaRetentionService $retention,
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
            ->paginate(15, ['*'], 'exports_page');

        $trasmissioni = QuesturaTransmission::query()
            ->where('struttura_id', $struttura->id)
            ->latest('id')
            ->paginate(15, ['*'], 'transmissions_page');

        $latestTableSnapshot = collect(Storage::disk('local')->allFiles('questura/tabelle/struttura_' . $struttura->id))
            ->filter(fn (string $path) => str_ends_with($path, '/manifest.json'))
            ->sortDesc()
            ->map(function (string $path) {
                $data = json_decode(Storage::disk('local')->get($path), true);
                return is_array($data) ? $data + ['manifest_path' => $path] : null;
            })
            ->filter()
            ->first();

        $questuraError = null;
        try {
            $credStatus = $this->webService->credentialsStatus($struttura);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Configurazione non utilizzabile: diagnostica Q3, nessun recupero
            // o riscrittura delle credenziali e nessun retry della trasmissione.
            report($e);
            $questuraError = 'Verificare lo storico prima di ripetere l’operazione.';
            $credStatus = ['configured' => false, 'simulation' => false, 'missing' => [], 'blocked' => true];
        }

        return response()->view('questura.index', [
            'struttura' => $struttura,
            'credStatus' => $credStatus,
            'questuraError' => $questuraError,
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
            'ricevute' => QuesturaReceipt::where('struttura_id', $struttura->id)->latest('id')->paginate(15, ['*'], 'receipts_page'),
        ], $questuraError === null ? 200 : 409);
    }

    public function downloadPeriodo(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
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
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
        ]);
    }

    public function downloadSchedina(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
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
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
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
        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
        }
        $path = 'questura/tabelle/struttura_'.$struttura->id.'/'.now()->format('Ymd_His').'_'.\Illuminate\Support\Str::uuid();
        $manifest = json_decode(file_get_contents(base_path('reference/questura/manifest.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($manifest as $source) {
            $bytes = file_get_contents(base_path('reference/questura/'.$source['file']));
            abort_unless(hash_equals($source['sha256'], hash('sha256', $bytes)), 409);
            abort_unless(Storage::disk('local')->put($path.'/'.$source['file'], $bytes), 500);
        }
        abort_unless(Storage::disk('local')->put($path.'/manifest.json', json_encode(['sources' => $manifest, 'archived_at' => now()->toIso8601String(), 'user_id' => $request->user()->id])), 500);
        return redirect()->route('questura.index')->with('success', 'Snapshot delle tabelle pubbliche ufficiali del 06/10/2026 archiviato.');
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

        abort_if($export->finalized_at, 410, 'TXT eliminato dopo la ricevuta; restano metadati tecnici.');
        abort_unless(Storage::disk('local')->exists($export->path), 404);
        $bytes = Storage::disk('local')->get($export->path);
        abort_unless($export->sha256 && hash_equals($export->sha256, hash('sha256', $bytes)), 409, 'Integrità archivio Questura non verificata.');

        return Storage::disk('local')->download($export->path, $export->filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function downloadPayload(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $tx = QuesturaTransmission::where('struttura_id', $struttura->id)->findOrFail($id);
        abort_if($tx->finalized_at, 410, 'Payload eliminato dopo la ricevuta.');
        $bytes = base64_decode($tx->payload['txt_base64'] ?? '', true);
        abort_unless(is_string($bytes) && $tx->sha256 && hash_equals($tx->sha256, hash('sha256', $bytes)), 409);
        return response($bytes, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="questura_payload_'.$id.'.txt"', 'Cache-Control' => 'private, no-store']);
    }

    public function downloadReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $transmission = QuesturaTransmission::where('struttura_id', $struttura->id)->findOrFail($id);
        $receipt = QuesturaReceipt::where('struttura_id', $struttura->id)
            ->whereDate('remote_date', $transmission->executed_at->toDateString())->firstOrFail();
        return $this->downloadArchivedReceipt($request, $receipt->id);
    }

    public function acquireReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $transmission = QuesturaTransmission::where('struttura_id', $struttura->id)->findOrFail($id);
        abort_unless($transmission->mode === 'send' && in_array($transmission->status, ['sent', 'uncertain', 'partial', 'in_progress'], true), 409);
        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
        }
        $date = $transmission->executed_at->copy()->startOfDay();
        $existing = QuesturaReceipt::where('struttura_id', $struttura->id)->whereDate('remote_date', $date)->first();
        if ($existing) {
            $this->retention->finalizeDay($struttura->id, $existing->id, $request->user()->id);
            return redirect()->route('questura.ws.receipt', ['id' => $id]);
        }
        $result = $this->webService->receipt($struttura, $date);
        if (($result['state'] ?? null) !== 'receipt_available') {
            return redirect()->route('questura.index')->withErrors(['questura_ws' => EsitoTrasmissioneQuestura::sanifica($result)['message']]);
        }
        $receipt = $this->retention->archiveReceipt($struttura, $date, $result['bytes'], $id);
        $this->retention->finalizeDay($struttura->id, $receipt->id, $request->user()->id);
        return redirect()->route('questura.ws.receipt', ['id' => $id]);
    }

    public function acquireManualReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $export = QuesturaExport::where('struttura_id', $struttura->id)->findOrFail($id);
        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
        }
        $data = $request->validate(['communication_date' => 'required|date_format:Y-m-d|before:today', 'communication_confirmed' => 'required|accepted']);
        $date = Carbon::createFromFormat('!Y-m-d', $data['communication_date']);
        abort_unless($export->created_at->lte($date->copy()->endOfDay()), 409);
        $receipt = QuesturaReceipt::where('struttura_id', $struttura->id)->whereDate('remote_date', $date)->first();
        if (!$receipt) {
            $result = $this->webService->receipt($struttura, $date);
            if (($result['state'] ?? '') !== 'receipt_available') {
                return redirect()->route('questura.index')->withErrors(['questura_ws' => EsitoTrasmissioneQuestura::sanifica($result)['message']]);
            }
            $receipt = $this->retention->archiveReceipt($struttura, $date, $result['bytes'], null);
        }
        $this->retention->finalizeManual($struttura->id, $id, $receipt->id, $request->user()->id, $date);
        $this->retention->finalizeDay($struttura->id, $receipt->id, $request->user()->id);
        return redirect()->route('questura.receipts.download', ['id' => $receipt->id]);
    }

    public function finalizeTransmission(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $tx = QuesturaTransmission::where('struttura_id', $struttura->id)->findOrFail($id);
        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
        }
        $request->validate(['reconciled' => 'required|accepted']);
        abort_unless($tx->executed_at, 409);
        $receipt = QuesturaReceipt::where('struttura_id', $struttura->id)->whereDate('remote_date', $tx->executed_at)->firstOrFail();
        $this->retention->finalizeTransmission($struttura->id, $id, $receipt->id, $request->user()->id, true);
        return redirect()->route('questura.index')->with('success', 'Copie Questura minimizzate; ricevuta conservata.');
    }

    public function downloadArchivedReceipt(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        $receipt = QuesturaReceipt::where('struttura_id', $struttura->id)->findOrFail($id);
        $this->retention->verifyReceipt($receipt, $struttura->id);
        return response(Storage::disk('local')->get($receipt->path), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.$receipt->filename.'"', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    private function blockedTransportResponse(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null
    {
        try {
            $this->webService->assertTransportEnabled();
        } catch (QuesturaTransportDisabledException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => QuesturaTransportDisabledException::MESSAGE], 409);
            }

            return redirect()->route('questura.index')->withErrors(['questura_ws' => QuesturaTransportDisabledException::MESSAGE])
                ->header(QuesturaTransportDisabledException::RESPONSE_HEADER, '1');
        }

        return null;
    }

    private function runWsAction(Request $request, string $mode)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if ($blocked = $this->blockedTransportResponse($request)) {
            return $blocked;
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
            'txt_base64' => base64_encode($txt),
            'transport_mode' => $credStatus['simulation'] ? 'simulation' : 'live',
            'sha256' => hash('sha256', $txt),
            'byte_size' => strlen($txt),
            'charset' => 'UTF-8',
            'component_ids' => $analisi->pluck('schedina')->flatMap(fn ($s) => $s->componenti->pluck('id'))->values()->all(),
        ];

        $result = EsitoTrasmissioneQuestura::crea('in_progress', $mode === 'send' ? 'send' : 'test');

        try {
            $transmission = DB::transaction(function () use ($struttura, $request, $mode, $dal, $al, $schedinaIds, $txt, $payload, $result) {
                if ($mode === 'send') {
                    $this->requireVerifiedPayload($struttura->id, $dal, $al, $txt, $payload);
                }
                if ($mode === 'send' && $payload['transport_mode'] === 'live') {
                    // Gli archivi precedenti senza identità non diventano reinviabili
                    // dopo la migration, neppure se il payload è già minimizzato.
                    if (QuesturaTransmission::where('struttura_id', $struttura->id)
                        ->where('mode', 'send')->whereNull('identity_reserved_at')
                        ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'simulation'))->exists()) {
                        throw ValidationException::withMessages(['questura_ws' => 'Invii precedenti senza riserva delle Schedine: occorre riconciliare lo storico prima di un nuovo Send.']);
                    }
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
                    result: $result,
                    status: $result['state'],
                    responseCode: $result['response_code'] ?? null,
                    responseMessage: $result['message'] ?? null,
                    responseDetail: $result['detail'] ?? null,
                );

                if ($mode === 'send') {
                    $ids = array_values(array_unique($schedinaIds));
                    sort($ids, SORT_NUMERIC);
                    DB::table('questura_send_reservations')->insert(array_map(fn ($id) => [
                        'struttura_id' => $struttura->id,
                        'schedina_id' => $id,
                        'transport_mode' => $payload['transport_mode'],
                        'questura_transmission_id' => $transmission->id,
                        'created_at' => now(),
                    ], $ids));
                }

                DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $transmission->id, 'status' => $result['state'], 'result' => json_encode($result), 'created_at' => now()]);
                return $transmission;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return redirect()->route('questura.index')->withErrors(['questura_ws' => 'Una Schedina o il payload sono già riservati per invio: verificare lo storico senza ripetere Send.']);
        } catch (ValidationException $e) {
            return redirect()->route('questura.index')->withErrors($e->errors());
        }
        try {
            $result = $mode === 'verify'
                ? $this->webService->verify($struttura, $txt)
                : $this->webService->send($struttura, $txt);
        } catch (Throwable) {
            $result = EsitoTrasmissioneQuestura::crea($mode === 'send' ? 'uncertain' : 'technical_error', $mode === 'send' ? 'send' : 'test');
        }
        $result = EsitoTrasmissioneQuestura::sanifica($result);
        DB::transaction(function () use ($transmission, $result) {
            $transmission->update(['status' => $result['state'], 'result' => $result, 'response_message' => $result['message']]);
            DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $transmission->id, 'status' => $result['state'], 'result' => json_encode($result), 'created_at' => now()]);
            if ($transmission->mode === 'send' && ($result['transmission_excluded'] ?? false) === true) {
                // Solo una prova positiva del trasporto escluso libera il retry.
                // Crash prima di questo commit: resta in_progress e bloccato.
                DB::table('questura_send_reservations')->where('questura_transmission_id', $transmission->id)->delete();
                DB::table('questura_transmissions')->where('id', $transmission->id)->update(['send_key' => null]);
            }
        });

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

    private function requireVerifiedPayload(int $strutturaId, Carbon $dal, Carbon $al, string $txt, array $payload): void
    {
        // La verifica più recente del contesto prevale anche se fallita o minimizzata.
        // Nessun ID/hash fornito dal browser autorizza il trasporto.
        $verify = QuesturaTransmission::where('struttura_id', $strutturaId)
            ->where('mode', 'verify')->whereDate('dal', $dal)->whereDate('al', $al)
            ->where('payload->transport_mode', $payload['transport_mode'])
            ->latest('id')->lockForUpdate()->first();
        $rows = substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0);
        $positive = $verify && ($payload['transport_mode'] === 'simulation'
            ? $verify->status === 'simulation'
            : ($verify->status === 'unknown'
                && ($verify->result['valid_rows'] ?? null) === $rows
                && ($verify->result['row_errors'] ?? null) === []));
        $bytes = $verify ? base64_decode($verify->payload['txt_base64'] ?? '', true) : false;
        if (!$positive || $verify->finalized_at || $verify->payload_deleted_at
            || $verify->scope_type !== 'periodo'
            || $verify->schedina_ids !== $payload['schedina_ids']
            || $verify->component_ids !== $payload['component_ids']
            || (int) $verify->righe_count !== $rows
            || (int) $verify->byte_size !== strlen($txt)
            || $verify->charset !== 'UTF-8'
            || !is_string($verify->sha256) || !hash_equals($verify->sha256, $payload['sha256'])
            || $bytes !== $txt) {
            throw ValidationException::withMessages(['questura_ws' => 'WS Test positivo assente o non più valido per questo elenco: i dati possono essere cambiati dopo il test. Ripetere WS Test prima di Send.']);
        }
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
        return $txt;
    }

    private function resolveStruttura(Request $request): ?Struttura
    {
        return \App\Support\StrutturaAccess::resolve($request);
    }

    private function storeExport(int $strutturaId, ?int $userId, Carbon $dal, Carbon $al, string $filename, string $txt, $schedine): QuesturaExport
    {
        $timestamp = now();
        $storedBasename = \Illuminate\Support\Str::uuid() . '_' . $filename;
        $path = 'questura/struttura_' . $strutturaId . '/' . $storedBasename;
        abort_unless(Storage::disk('local')->put($path, $this->toDownloadEncoding($txt)), 500, 'Archiviazione Questura non riuscita.');

        $righeCount = substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0);
        $schedinaIds = $schedine->pluck('id')->filter()->values()->all();

        try {
            return DB::transaction(function () use ($strutturaId, $userId, $dal, $al, $filename, $path, $txt, $schedine, $schedinaIds, $righeCount, $timestamp) {
                $export = QuesturaExport::query()->create([
                    'struttura_id' => $strutturaId,
                    'user_id' => $userId,
                    'dal' => $dal->toDateString(),
                    'al' => $al->toDateString(),
                    'filename' => $filename,
                    'path' => $path,
                    'sha256' => hash('sha256', $txt),
                    'byte_size' => strlen($txt),
                    'charset' => 'UTF-8',
                    'status' => 'generated',
                    'component_ids' => $schedine->flatMap(fn ($s) => $s->componenti->pluck('id'))->values()->all(),
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
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
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
        $sourceIds = array_values(array_unique($schedinaIds));
        sort($sourceIds, SORT_NUMERIC);
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
            'sha256' => $payload['sha256'] ?? null,
            'byte_size' => $payload['byte_size'] ?? null,
            'charset' => $payload['charset'] ?? null,
            'component_ids' => $payload['component_ids'] ?? null,
            // L'hash resta controllo dei byte; l'identità include le sorgenti PMS.
            'send_key' => $mode === 'send' ? hash('sha256', ($payload['transport_mode'] ?? 'live') . ':' . $strutturaId . ':' . implode(',', $sourceIds) . ':' . ($payload['sha256'] ?? '')) : null,
            'identity_reserved_at' => $mode === 'send' ? now() : null,
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

}
