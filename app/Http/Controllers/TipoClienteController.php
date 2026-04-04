<?php

namespace App\Http\Controllers;

use App\Models\TipoCliente;
use App\Services\CestinoService;
use Illuminate\Http\Request;

class TipoClienteController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = TipoCliente::query();

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('codice') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione') . ' LIKE ?', [$like]);
                }
            });
        }

        $tipiClienti = $query->orderBy('id')->paginate(10)->withQueryString();

        return view('tipo_cliente.index', compact('tipiClienti'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'required|string|max:50|unique:tipo_cliente,codice',
            'descrizione' => 'required|string|max:191',
            'attivo' => 'nullable|boolean',
        ]);

        $data['attivo'] = $request->has('attivo') ? $request->boolean('attivo') : true;

        TipoCliente::create($data);

        return redirect()->back()->with('success', 'Tipo cliente creato con successo.');
    }

    public function update(Request $request, TipoCliente $tipo_cliente)
    {
        $data = $request->validate([
            'codice' => 'required|string|max:50|unique:tipo_cliente,codice,' . $tipo_cliente->id,
            'descrizione' => 'required|string|max:191',
            'attivo' => 'nullable|boolean',
        ]);

        $data['attivo'] = $request->has('attivo') ? $request->boolean('attivo') : ($tipo_cliente->attivo ?? true);

        $tipo_cliente->fill($data)->save();

        return redirect()->back()->with('success', 'Tipo cliente aggiornato con successo.');
    }

    public function destroy(TipoCliente $tipo_cliente)
    {
        app(CestinoService::class)->archiveModel($tipo_cliente, [
            'source' => 'Tipo Cliente',
        ]);
        $tipo_cliente->delete();

        return redirect()->back()->with('success', 'Tipo cliente spostato nel cestino.');
    }

    private function normalizedTerms(string $value): array
    {
        $clean = mb_strtolower(trim($value));
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean);
        if (!is_string($clean) || $clean === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $clean)));
    }

    private function normalizedFieldExpr(string $column): string
    {
        $expr = "LOWER(COALESCE($column, ''))";
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($expr, '''', ''), '.', ' '), ',', ' '), '-', ' '), '/', ' '), '(', ' '), ')', ' ')";
    }
}
