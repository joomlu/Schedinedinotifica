<?php

namespace App\Http\Controllers;

use App\Exceptions\ComponentiImportException;
use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\ComponentiImportService;
use App\Support\Componenti\SingleNodeBatchLock;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ComponentiImportController extends Controller
{
    private const CONSUMED_TTL_SECONDS = 86400;

    private const MSG_BATCH_NOT_VALID = 'Batch non valido, scaduto o non autorizzato. Genera una nuova preview e riprova.';
    private const MSG_LOCK_BUSY = 'Importazione già in corso o già completata. Attendi e aggiorna la pagina.';
    private const MSG_CATALOG_NOT_READY = 'Impossibile completare l importazione: catalogo Tipo alloggiato non pronto. Nessun componente è stato importato.';
    private const MSG_UNEXPECTED = 'Impossibile completare l importazione. Nessun componente è stato importato.';

    public function __construct(private readonly ComponentiImportService $service)
    {
    }

    public function prepare(Request $request, int $schedina)
    {
        $this->loadOwnedSchedina($schedina);

        // La schedina esistente non deve sovrascrivere la bozza di una nuova schedina.
        session()->put('active_tab', 'schedina-step-comp');

        return redirect()
            ->route('schedina.componenti.import.index', ['schedina' => $schedina])
            ->with('active_tab', 'schedina-step-comp');
    }

    public function newPrepare(Request $request)
    {
        $this->currentStruttura();

        session()->put($this->newDraftStateKey(), $request->except(['_token', '_method', 'id', 'schedina_id', 'schedina']));
        session()->put('active_tab', 'schedina-step-comp');

        return redirect()->route('schedina.componenti.import.new.index')->with('active_tab', 'schedina-step-comp');
    }

    public function index(int $schedina)
    {
        $schedina = $this->loadOwnedSchedina($schedina);

        return view('schedina.componenti.import', [
            'schedina' => $schedina,
            'struttura' => $this->currentStruttura(),
            'preview' => null,
            'templateHeaders' => $this->service->headersTemplate(),
            'formatiSupportati' => $this->service->formatiSupportati(),
            'maxBytes' => ComponentiImportService::MAX_BYTES,
            'maxRows' => ComponentiImportService::MAX_RIGHE,
        ]);
    }

    public function newIndex()
    {
        $struttura = $this->currentStruttura();

        return view('schedina.componenti.import', [
            'schedina' => null,
            'struttura' => $struttura,
            'preview' => null,
            'templateHeaders' => $this->service->headersTemplate(),
            'formatiSupportati' => $this->service->formatiSupportati(),
            'maxBytes' => ComponentiImportService::MAX_BYTES,
            'maxRows' => ComponentiImportService::MAX_RIGHE,
        ]);
    }

    public function template(int $schedina, string $format)
    {
        $this->loadOwnedSchedina($schedina);
        $format = strtolower(trim($format));

        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            abort(404);
        }

        $content = $this->service->contenutoTemplateVuoto($format);

        $callback = static function () use ($content) {
            echo $content;
        };

        return response()->streamDownload($callback, $this->service->nomeFileTemplate($format), [
            'Content-Type' => $this->service->contentTypePerFormato($format),
        ]);
    }

    public function newTemplate(string $format)
    {
        $this->currentStruttura();
        $format = strtolower(trim($format));

        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            abort(404);
        }

        $content = $this->service->contenutoTemplateVuoto($format);

        $callback = static function () use ($content) {
            echo $content;
        };

        return response()->streamDownload($callback, $this->service->nomeFileTemplate($format), [
            'Content-Type' => $this->service->contentTypePerFormato($format),
        ]);
    }

    public function newPreview(Request $request)
    {
        $struttura = $this->currentStruttura();

        $validated = $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:' . (int) (ComponentiImportService::MAX_BYTES / 1024)],
        ], [
            'file_import.required' => 'Seleziona un file CSV, TXT o XLSX da importare.',
            'file_import.mimes' => 'Il file deve essere un CSV, un TXT delimitato o un XLSX.',
            'file_import.max' => 'Il file importato è troppo grande.',
        ]);

        $file = $validated['file_import'];
        $format = strtolower((string) $file->getClientOriginalExtension());

        if ($format === 'xls') {
            return back()->withInput()->withErrors([
                'file_import' => 'Formato .xls non supportato. Usa XLSX, CSV o TXT.',
            ]);
        }

        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            return back()->withInput()->withErrors([
                'file_import' => 'Estensione file non supportata. Usa CSV, TXT o XLSX.',
            ]);
        }

        try {
            $preview = $this->service->previewDaContenuto((string) file_get_contents($file->getRealPath()), $format);
        } catch (ComponentiImportException $exception) {
            return back()->withInput()->withErrors([
                'file_import' => $exception->issues !== [] ? implode(' ', $exception->issues) : $exception->getMessage(),
            ]);
        }

        $batchToken = (string) Str::uuid();
        $batch = [
            'token' => $batchToken,
            'user_id' => (int) auth()->id(),
            'struttura_id' => (int) $struttura->id,
            'schedina_id' => null,
            'context' => 'new_schedina',
            'formato' => $preview['formato'],
            'raw_headers' => $preview['raw_headers'] ?? [],
            'raw_rows' => $preview['raw_rows'] ?? [],
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(ComponentiImportService::BATCH_TTL_MINUTES)->timestamp,
            'confirmed_at' => null,
            'status' => 'pending',
        ];

        $this->storeImportBatch($batch);
        $preview['batch_token'] = $batchToken;
        $preview['confirmable_rows'] = (int) ($preview['righe_valide'] ?? 0);

        return view('schedina.componenti.import', [
            'schedina' => null,
            'struttura' => $struttura,
            'preview' => $preview,
            'templateHeaders' => $this->service->headersTemplate(),
            'formatiSupportati' => $this->service->formatiSupportati(),
            'maxBytes' => ComponentiImportService::MAX_BYTES,
            'maxRows' => ComponentiImportService::MAX_RIGHE,
        ]);
    }

    public function preview(Request $request, int $schedina)
    {
        $schedina = $this->loadOwnedSchedina($schedina);

        $validated = $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:' . (int) (ComponentiImportService::MAX_BYTES / 1024)],
        ], [
            'file_import.required' => 'Seleziona un file CSV, TXT o XLSX da importare.',
            'file_import.mimes' => 'Il file deve essere un CSV, un TXT delimitato o un XLSX.',
            'file_import.max' => 'Il file importato è troppo grande.',
        ]);

        $file = $validated['file_import'];
        $format = strtolower((string) $file->getClientOriginalExtension());

        if ($format === 'xls') {
            return back()->withInput()->withErrors([
                'file_import' => 'Formato .xls non supportato. Usa XLSX, CSV o TXT.',
            ]);
        }

        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            return back()->withInput()->withErrors([
                'file_import' => 'Estensione file non supportata. Usa CSV, TXT o XLSX.',
            ]);
        }

        try {
            $preview = $this->service->previewDaContenuto((string) file_get_contents($file->getRealPath()), $format);
        } catch (ComponentiImportException $exception) {
            return back()->withInput()->withErrors([
                'file_import' => $exception->issues !== [] ? implode(' ', $exception->issues) : $exception->getMessage(),
            ]);
        }

        $batchToken = (string) Str::uuid();
        $batch = [
            'token' => $batchToken,
            'user_id' => (int) auth()->id(),
            'struttura_id' => (int) $schedina->struttura_id,
            'schedina_id' => (int) $schedina->id,
            'formato' => $preview['formato'],
            'raw_headers' => $preview['raw_headers'] ?? [],
            'raw_rows' => $preview['raw_rows'] ?? [],
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(ComponentiImportService::BATCH_TTL_MINUTES)->timestamp,
            'confirmed_at' => null,
            'status' => 'pending',
        ];

        $this->storeImportBatch($batch);
        $preview['batch_token'] = $batchToken;
        $preview['confirmable_rows'] = (int) ($preview['righe_valide'] ?? 0);

        return view('schedina.componenti.import', [
            'schedina' => $schedina,
            'struttura' => $this->currentStruttura(),
            'preview' => $preview,
            'templateHeaders' => $this->service->headersTemplate(),
            'formatiSupportati' => $this->service->formatiSupportati(),
            'maxBytes' => ComponentiImportService::MAX_BYTES,
            'maxRows' => ComponentiImportService::MAX_RIGHE,
        ]);
    }

    public function confirm(Request $request, int $schedina)
    {
        $schedina = $this->loadOwnedSchedina($schedina);

        $validated = $request->validate([
            'import_batch_token' => ['required', 'string'],
        ]);

        $token = trim((string) $validated['import_batch_token']);

        try {
            return $this->singleNodeLockForToken($token)->execute(function () use ($schedina, $token) {
                if ($this->isBatchConsumed($token)) {
                    return redirect()
                        ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                        ->with('warning', 'Batch già confermato. Nessuna nuova importazione eseguita.');
                }

                $batch = $this->loadImportBatch($token);
                if ($batch === null) {
                    return redirect()
                        ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                        ->withErrors(['import_batch_token' => self::MSG_BATCH_NOT_VALID]);
                }

                if (($batch['status'] ?? 'pending') === 'confirmed') {
                    return redirect()
                        ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                        ->with('warning', 'Batch già confermato. Nessuna nuova importazione eseguita.');
                }

                $conferma = $this->service->preparaConfermaBatch(
                    $batch,
                    $schedina->id,
                    (int) $schedina->struttura_id,
                    (int) auth()->id()
                );

                if (($conferma['valid_count'] ?? 0) < 1) {
                    return redirect()
                        ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                        ->withErrors(['import_batch_token' => 'Non ci sono righe valide da importare.']);
                }

                DB::transaction(function () use ($conferma) {
                    foreach ($conferma['payloads'] as $payload) {
                        Componenti::query()->create($payload);
                    }
                });

                $this->markBatchConsumed($token);

                $batch['status'] = 'confirmed';
                $batch['confirmed_at'] = now()->timestamp;
                $batch['imported_count'] = (int) ($conferma['valid_count'] ?? 0);

                if (!$this->tryStoreImportBatch($batch)) {
                    return redirect()
                        ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-comp'])
                        ->with('warning', 'Importazione completata, ma stato batch non aggiornato. Evita di ripetere l operazione.');
                }

                return redirect()
                    ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-comp'])
                    ->with('success', 'Importazione completata: ' . (int) $conferma['valid_count'] . ' componenti aggiunti.');
            });
        } catch (ComponentiImportException $exception) {
            return redirect()
                ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                ->withErrors(['import_batch_token' => self::MSG_BATCH_NOT_VALID]);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === SingleNodeBatchLock::LOCK_NOT_ACQUIRED) {
                return redirect()
                    ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                    ->withErrors(['import_batch_token' => self::MSG_LOCK_BUSY]);
            }

            report($exception);

            return redirect()
                ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                ->withErrors(['import_batch_token' => self::MSG_CATALOG_NOT_READY]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('schedina.componenti.import.index', ['schedina' => $schedina->id])
                ->withErrors(['import_batch_token' => self::MSG_UNEXPECTED]);
        }
    }

    public function newConfirm(Request $request)
    {
        $validated = $request->validate([
            'import_batch_token' => ['required', 'string'],
        ]);

        $token = trim((string) $validated['import_batch_token']);

        $struttura = $this->currentStruttura();

        try {
            return $this->singleNodeLockForToken($token)->execute(function () use ($token, $struttura) {
                if ($this->isBatchConsumed($token)) {
                    return redirect()->route('schedina.componenti.import.new.index')->with('warning', 'Batch già confermato. Nessuna nuova importazione eseguita.');
                }

                $batch = $this->loadImportBatch($token);
                if ($batch === null || (($batch['context'] ?? null) !== 'new_schedina')) {
                    return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => self::MSG_BATCH_NOT_VALID]);
                }

                if (($batch['status'] ?? 'pending') === 'confirmed') {
                    return redirect()->route('schedina.componenti.import.new.index')->with('warning', 'Batch già confermato. Nessuna nuova importazione eseguita.');
                }

                $conferma = $this->service->preparaConfermaBatch($batch, 0, (int) $struttura->id, (int) auth()->id());
                if (($conferma['valid_count'] ?? 0) < 1) {
                    return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => 'Non ci sono righe valide da importare.']);
                }

                $currentDraft = $this->loadNewDraftComponenti();
                $merged = array_values(array_merge($currentDraft, $conferma['payloads']));
                $this->storeNewDraftComponenti($merged);

                $this->markBatchConsumed($token);

                $batch['status'] = 'confirmed';
                $batch['confirmed_at'] = now()->timestamp;
                $batch['imported_count'] = (int) ($conferma['valid_count'] ?? 0);
                if (!$this->tryStoreImportBatch($batch)) {
                    return redirect()->route('newschedina', ['active_tab' => 'schedina-step-comp'])
                        ->withInput(['componenti' => $merged, 'active_tab' => 'schedina-step-comp'])
                        ->with('warning', 'Importazione completata, ma stato batch non aggiornato. Evita di ripetere l operazione.');
                }

                return redirect()->route('newschedina', ['active_tab' => 'schedina-step-comp'])
                    ->withInput(['componenti' => $merged, 'active_tab' => 'schedina-step-comp'])
                    ->with('success', (int) $conferma['valid_count'] . ' componenti importati. Controlla i dati prima di salvare la schedina.');
            });
        } catch (ComponentiImportException $exception) {
            return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => self::MSG_BATCH_NOT_VALID]);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === SingleNodeBatchLock::LOCK_NOT_ACQUIRED) {
                return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => self::MSG_LOCK_BUSY]);
            }

            report($exception);

            return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => self::MSG_CATALOG_NOT_READY]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('schedina.componenti.import.new.index')->withErrors(['import_batch_token' => self::MSG_UNEXPECTED]);
        }
    }

    private function currentStruttura(): Struttura
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        abort_if(!$strutturaId, 403, 'Nessuna struttura corrente disponibile.');

        return Struttura::query()->findOrFail($strutturaId);
    }

    private function loadOwnedSchedina(int $schedinaId): Schedina
    {
        $struttura = $this->currentStruttura();

        return Schedina::query()
            ->where('struttura_id', $struttura->id)
            ->findOrFail($schedinaId);
    }

    private function batchSessionKey(string $token): string
    {
        return 'componenti_import_batches.' . $token;
    }

    private function newDraftStateKey(): string
    {
        $userId = (int) (auth()->id() ?? 0);
        $strutturaId = (int) (StrutturaCorrente::getId() ?? auth()->user()?->struttura_id ?? 0);

        return 'componenti_import_new_schedina.' . $userId . '.' . $strutturaId;
    }

    private function loadNewDraftComponenti(): array
    {
        $draft = session()->get($this->newDraftStateKey(), []);

        if (!is_array($draft)) {
            return [];
        }

        $componenti = $draft['componenti'] ?? $draft;
        if (!is_array($componenti)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($row) {
            if (!is_array($row)) {
                return null;
            }

            return $row;
        }, $componenti), fn ($row) => is_array($row)));
    }

    private function storeNewDraftComponenti(array $componenti): void
    {
        $draft = session()->get($this->newDraftStateKey(), []);
        $draft = is_array($draft) ? $draft : [];
        $draft['componenti'] = array_values($componenti);
        session()->put($this->newDraftStateKey(), $draft);
    }

    private function batchLockKey(string $token): string
    {
        return 'componenti_import_batches.lock.' . $token;
    }

    private function batchConsumedKey(string $token): string
    {
        return 'componenti_import_batches.consumed.' . $token;
    }

    /**
     * Lock locale single-node: non fornisce exactly-once distribuito multi-node.
     */
    private function singleNodeLockForToken(string $token): SingleNodeBatchLock
    {
        return new SingleNodeBatchLock(
            fn () => Cache::add($this->batchLockKey($token), (int) auth()->id(), ComponentiImportService::BATCH_TTL_MINUTES * 60),
            fn () => Cache::forget($this->batchLockKey($token))
        );
    }

    private function isBatchConsumed(string $token): bool
    {
        try {
            return Cache::has($this->batchConsumedKey($token));
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function markBatchConsumed(string $token): void
    {
        try {
            Cache::put($this->batchConsumedKey($token), now()->timestamp, self::CONSUMED_TTL_SECONDS);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param array<string, mixed> $batch
     */
    private function storeImportBatch(array $batch): void
    {
        session()->put($this->batchSessionKey((string) $batch['token']), $batch);
    }

    /**
     * @param array<string, mixed> $batch
     */
    private function tryStoreImportBatch(array $batch): bool
    {
        try {
            $this->storeImportBatch($batch);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function loadImportBatch(string $token): ?array
    {
        $batch = session()->get($this->batchSessionKey($token));

        if (!is_array($batch)) {
            return null;
        }

        if (($batch['expires_at'] ?? 0) < now()->timestamp) {
            session()->forget($this->batchSessionKey($token));

            return null;
        }

        return $batch;
    }
}
