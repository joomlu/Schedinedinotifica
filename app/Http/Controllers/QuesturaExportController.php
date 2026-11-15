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
use Illuminate\Support\Facades\Cache;
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
        $request->validate(['q' => ['nullable', 'string', 'max:200'], 'filtro' => ['nullable', 'string', 'max:30']]);
        $q = trim((string) $request->query('q', ''));
        $filtro = (string) $request->query('filtro', 'tutte');
        $schedine = $this->service->schedinePerPeriodo($struttura->id, $dal, $al);
        $riservate = DB::table('questura_send_reservations')->where('struttura_id', $struttura->id)
            ->where('transport_mode', 'live')->whereIn('schedina_id', $schedine->pluck('id'))->pluck('schedina_id')->flip();
        $completa = $this->service->analizzaSchedine($schedine)->map(function (array $item) use ($riservate) {
            $item['gia_trasmessa'] = $riservate->has($item['schedina']->id) || $item['schedina']->questura_send_count > 0;
            $item['preview_hash'] = $item['valida'] ? hash('sha256', implode("\r\n", $item['righe'])) : null;
            return $item;
        });
        $previewTxt = null;
        $previewError = null;
        try {
            $previewTxt = $this->service->buildTxt($completa);
        } catch (ValidationException $e) {
            $previewError = $e->validator->errors()->first();
        }
        $analisi = $completa
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

        $avviso = Cache::get('questura:avviso-serale:'.$struttura->id);
        $avvisoAutomatico = $avviso
            ? 'Invio automatico del '.$avviso['giorno'].': '.$avviso['schedine'].' schedine da verificare. Controlla i dati, le esportazioni TXT e lo storico prima di ripetere un invio.'
            : null;
        if ($avviso['errore'] ?? false) {
            $avvisoAutomatico .= ' Elaborazione incompleta.';
        }

        return response()->view('questura.index', [
            'avvisoAutomatico' => $avvisoAutomatico,
            'preview_txt' => $previewTxt,
            'preview_hash' => $previewTxt !== null ? hash('sha256', $previewTxt) : null,
            'preview_error' => $previewError,
            'preview_righe' => $completa->sum(fn (array $item) => count($item['righe'])),
            'struttura' => $struttura,
            'credStatus' => $credStatus,
            'questuraWsEnabled' => $this->webService->transportEnabled($struttura),
            'questuraTxtEnabled' => $this->webService->txtEnabled(),
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
            'tab' => in_array($request->query('tab'), ['export', 'elettronico'], true) ? $request->query('tab') : ($request->has('transmissions_page') ? 'elettronico' : ($request->has('exports_page') ? 'export' : 'operativita')),
            'latestTableSnapshot' => $latestTableSnapshot,
            'ricevute' => QuesturaReceipt::where('struttura_id', $struttura->id)->latest('id')->paginate(15, ['*'], 'receipts_page'),
        ], $questuraError === null ? 200 : 409, ['Cache-Control' => 'private, no-store']);
    }

    public function downloadPeriodo(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if (!$this->webService->txtEnabled() && ($blocked = $this->blockedTransportResponse($request, $struttura))) {
            return $blocked;
        }

        try {
            [$dal, $al, $txt, $analisi] = $this->buildPeriodoTxt($struttura->id, $request);
            $this->assertPreview($request, $txt);
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

        if (!$this->webService->txtEnabled() && ($blocked = $this->blockedTransportResponse($request, $struttura))) {
            return $blocked;
        }

        $schedina = Schedina::query()->effettive()
            ->with('componenti')
            ->where('struttura_id', $struttura->id)
            ->where('circuito', 'schedina')
            ->findOrFail($id);

        try {
            $txt = $this->service->buildTxtPerSchedina($schedina);
            $this->assertPreview($request, $txt);
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
        if (!$this->webService->txtEnabled() && ($blocked = $this->blockedTransportResponse($request, $struttura))) {
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

    public function sendSchedina(Request $request, int $id)
    {
        return $this->runWsAction($request, 'send', $id);
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
        if ($blocked = $this->blockedTransportResponse($request, $struttura)) {
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
        if ($blocked = $this->blockedTransportResponse($request, $struttura)) {
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
        if (!$this->webService->txtEnabled() && ($blocked = $this->blockedTransportResponse($request, $struttura))) {
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

    private function blockedTransportResponse(Request $request, Struttura $struttura): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null
    {
        try {
            $this->webService->assertTransportEnabled($struttura);
        } catch (QuesturaTransportDisabledException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => QuesturaTransportDisabledException::MESSAGE], 409);
            }

            return redirect()->route('questura.index')->withErrors(['questura_ws' => QuesturaTransportDisabledException::MESSAGE])
                ->header(QuesturaTransportDisabledException::RESPONSE_HEADER, '1');
        }

        return null;
    }

    private function runWsAction(Request $request, string $mode, ?int $id = null)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if ($blocked = $this->blockedTransportResponse($request, $struttura)) {
            return $blocked;
        }

        $credStatus = $this->webService->credentialsStatus($struttura);
        if (!$credStatus['configured']) {
            return redirect()->route('questura.index')->withErrors([
                'questura_ws' => 'Credenziali per invio diretto Questura incomplete: ' . implode(', ', $credStatus['missing']) . '.',
            ]);
        }

        try {
            if ($id === null) {
                [$dal, $al, $txt, $analisi] = $this->buildPeriodoTxt($struttura->id, $request);
            } else {
                $schedina = Schedina::query()->effettive()->where('struttura_id', $struttura->id)
                    ->where('circuito', 'schedina')->findOrFail($id);
                $dal = $al = Carbon::parse($schedina->arrive)->startOfDay();
                $analisi = $this->service->analizzaSchedine(
                    collect([$this->service->schedinaDelGiorno($struttura->id, $dal, $id)])->filter()
                );
                $txt = $this->service->buildTxt($analisi);
            }
            $this->assertPreview($request, $txt);
        } catch (ValidationException $e) {
            [$dal, $al] = $this->resolvePeriodo($request);
            return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])->withErrors($e->errors());
        }

        try {
            $trasmissioni = app(\App\Services\QuesturaTrasmissioni::class);
            [$transmission, $result] = $id !== null
                ? $trasmissioni->verificaEInvia($struttura, $request->user()?->id, $dal, $txt, $analisi)
                : $trasmissioni->esegui($struttura, $request->user()?->id, $mode, $dal, $al, $txt, $analisi);
        } catch (ValidationException $e) {
            return redirect()->route('questura.index')->withErrors($e->errors());
        }

        // Il Test positivo qualifica il tracciato, senza attestare una comunicazione ufficiale.
        if ($mode === 'verify' && $result['state'] === 'unknown' && (int) $transmission->righe_count > 0
            && ($result['valid_rows'] ?? null) === (int) $transmission->righe_count && ($result['row_errors'] ?? null) === []) {
            $righe = (int) $transmission->righe_count;
            return redirect()->route('questura.index', ['dal' => $dal->format('Y-m-d'), 'al' => $al->format('Y-m-d')])
                ->with('success', 'Verifica tracciato positiva su '.$righe.($righe === 1 ? ' riga. ' : ' righe. ').'Il Test non costituisce l’invio ufficiale.');
        }
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

    private function assertPreview(Request $request, string $txt): void
    {
        $hash = $request->input('preview_hash');
        if (!is_string($hash) || !preg_match('/\A[a-f0-9]{64}\z/', $hash) || !hash_equals(hash('sha256', $txt), $hash)) {
            throw ValidationException::withMessages(['questura_preview' => 'Anteprima mancante o dati cambiati: aggiorna Questura e ricontrolla l’elenco prima di procedere.']);
        }
    }

    private function resolvePeriodo(Request $request): array
    {
        $request->validate(['dal' => ['nullable', 'date_format:Y-m-d'], 'al' => ['nullable', 'date_format:Y-m-d']]);
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


}
