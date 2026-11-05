<?php

namespace App\Http\Controllers;

use App\Models\TipoCliente;
use Illuminate\Http\Request;

class TipoClienteController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = TipoCliente::query()->diSistema();

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
        abort(403, 'Classificazioni predefinite del sistema, non modificabili.');
    }

    public function update(Request $request, TipoCliente $tipo_cliente)
    {
        abort(403, 'Classificazioni predefinite del sistema, non modificabili.');
    }

    public function destroy(TipoCliente $tipo_cliente)
    {
        abort(403, 'Classificazioni predefinite del sistema, non modificabili.');
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
