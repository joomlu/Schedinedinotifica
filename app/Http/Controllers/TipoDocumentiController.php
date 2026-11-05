<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoDocumento;
use App\Services\CestinoService;

class TipoDocumentiController extends Controller
{
    public function index()
    {
        $tipo_documenti = TipoDocumento::all();
        return view('tipo_documenti.list', compact('tipo_documenti'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Codice' => 'required|string|max:255',
            'descrizione' => 'required|string|max:255',
        ]);
        TipoDocumento::create($data);
        return redirect()->route('tipo_documenti.index');
    }

    public function update(Request $request, $id)
    {
        $tipo_documento = TipoDocumento::findOrFail($id);
        $data = $request->validate([
            'Codice' => 'required|string|max:255',
            'descrizione' => 'required|string|max:255',
        ]);
        $tipo_documento->update($data);
        return redirect()->route('tipo_documenti.index');
    }

    public function destroy($id)
    {
        $tipo_documento = TipoDocumento::findOrFail($id);
        app(CestinoService::class)->archiveModel($tipo_documento, [
            'source' => 'Tipo Documento',
        ]);
        $tipo_documento->delete();
        return redirect()->route('tipo_documenti.index');
    }
}
