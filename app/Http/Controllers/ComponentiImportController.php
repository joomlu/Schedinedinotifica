<?php

namespace App\Http\Controllers;

use App\Exceptions\ComponentiImportException;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\ComponentiImportService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;

class ComponentiImportController extends Controller
{
    public function __construct(private readonly ComponentiImportService $service)
    {
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

    public function template(int $schedina, string $format)
    {
        $this->loadOwnedSchedina($schedina);
        $format = strtolower(trim($format));

        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            abort(404);
        }

        $headers = $this->service->headersTemplate();
        $example = $this->service->templateExampleRow();
        $delimiter = $this->service->delimitatorePerFormato($format);

        $callback = function () use ($headers, $example, $delimiter) {
            $handle = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";
            fputcsv($handle, $headers, $delimiter);
            fputcsv($handle, $example, $delimiter);
            fclose($handle);
        };

        return response()->streamDownload($callback, $this->service->nomeFileTemplate($format), [
            'Content-Type' => $this->service->contentTypePerFormato($format),
        ]);
    }

    public function preview(Request $request, int $schedina)
    {
        $schedina = $this->loadOwnedSchedina($schedina);

        $validated = $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt', 'max:' . (int) (ComponentiImportService::MAX_BYTES / 1024)],
        ], [
            'file_import.required' => 'Seleziona un file CSV o TXT da importare.',
            'file_import.mimes' => 'Il file deve essere un CSV o un TXT delimitato.',
            'file_import.max' => 'Il file importato è troppo grande.',
        ]);

        $file = $validated['file_import'];
        $format = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($format, $this->service->formatiSupportati(), true)) {
            return back()->withInput()->withErrors([
                'file_import' => 'Estensione file non supportata. Usa CSV o TXT.',
            ]);
        }

        try {
            $preview = $this->service->previewDaContenuto(
                (string) file_get_contents($file->getRealPath()),
                $format
            );
        } catch (ComponentiImportException $exception) {
            return back()->withInput()->withErrors([
                'file_import' => $exception->issues !== [] ? implode(' ', $exception->issues) : $exception->getMessage(),
            ]);
        }

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
}