<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TipoVia;

class TipoViaController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = TipoVia::query();

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('abbr') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('nome') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione') . ' LIKE ?', [$like]);
                }
            });
        }

        $tipovia = $query->orderBy('abbr')->orderBy('nome')->paginate(10)->withQueryString();
        return view('tipovia.list', ['tipovia' => $tipovia]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'abbr' => ['required', 'string', 'max:20', Rule::unique('tipo_via', 'abbr')],
            'descrizione' => ['nullable', 'string', 'max:191'],
            'attivo' => ['nullable', 'boolean'],
        ]);

        $abbr = $data['abbr'];
        $descrizione = $data['descrizione'] ?? $abbr;
        $attivo = $request->boolean('attivo', true);

        TipoVia::create([
            'abbr' => $abbr,
            'nome' => $abbr,
            'descrizione' => $descrizione,
            'attivo' => $attivo,
        ]);
        return redirect()->back()->with('success', 'Tipo via creato con successo.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'abbr' => ['required', 'string', 'max:20', Rule::unique('tipo_via', 'abbr')->ignore($id)],
            'descrizione' => ['nullable', 'string', 'max:191'],
            'attivo' => ['nullable', 'boolean'],
        ]);

        $tipovia = TipoVia::findOrFail($id);
        $abbr = $data['abbr'];
        $descrizione = $data['descrizione'] ?? $abbr;

        $tipovia->abbr = $abbr;
        $tipovia->nome = $abbr;
        $tipovia->descrizione = $descrizione;
        $tipovia->attivo = array_key_exists('attivo', $data)
            ? $request->boolean('attivo')
            : $tipovia->attivo;
        $tipovia->save();
        return redirect()->back()->with('success', 'Tipo via aggiornato con successo.');
    }

    public function destroy($id)
    {
        $tipovia = TipoVia::findOrFail($id);
        $tipovia->delete();
        return redirect()->back()->with('success', 'Tipo via eliminato con successo.');
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
