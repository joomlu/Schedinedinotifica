<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Services\CestinoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticoliController extends Controller
{
    public function index()
    {
        $articoli = LicenzaArticolo::with(['parent', 'children'])
            ->withCount('assegnazioni')
            ->orderBy('ordine')
            ->orderBy('nome')
            ->get();

        return view('superadmin.articoli.index', [
            'articoli' => $articoli,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateArticolo($request);
        LicenzaArticolo::create($data);

        return redirect()->route('superadmin.articoli.index', ['tab' => 'nuovo'])->with('status', 'Articolo creato correttamente.');
    }

    public function update(Request $request, int $id)
    {
        $articolo = LicenzaArticolo::findOrFail($id);
        $data = $this->validateArticolo($request, $articolo->id);
        $articolo->update($data);

        return redirect()->route('superadmin.articoli.index')->with('status', 'Articolo aggiornato correttamente.');
    }

    public function destroy(int $id)
    {
        $articolo = LicenzaArticolo::withCount('assegnazioni')->findOrFail($id);

        if ($articolo->assegnazioni_count > 0) {
            return redirect()->route('superadmin.articoli.index')
                ->with('warning', 'Questo articolo e gia usato in licenze esistenti. Disattivalo invece di eliminarlo.');
        }

        app(CestinoService::class)->archiveModel($articolo, [
            'source' => 'Articoli',
        ]);
        $articolo->delete();

        return redirect()->route('superadmin.articoli.index')->with('status', 'Articolo spostato nel cestino.');
    }

    private function validateArticolo(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:licenza_articoli,id'],
            'nome' => ['required', 'string', 'max:160'],
            'codice' => ['nullable', 'string', 'max:80', Rule::unique('licenza_articoli', 'codice')->ignore($ignoreId)],
            'accesso_key' => ['nullable', 'string', 'max:120'],
            'descrizione' => ['nullable', 'string'],
            'prezzo_base' => ['required', 'numeric', 'min:0'],
            'attivo' => ['nullable', 'boolean'],
            'ordine' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'note' => ['nullable', 'string'],
        ]);
    }
}
