<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\RilasciatoDa;

class RilasciatoDaController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = RilasciatoDa::query();

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('name') . ' LIKE ?', [$like]);
                }
            });
        }

        $rilasciati = $query->orderBy('name')->paginate(10)->withQueryString();
        return view('rilasciato.list', ['rilasciati' => $rilasciati]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('rilasciato_da', 'name')],
        ]);

        RilasciatoDa::create($data);

        return redirect()->back()->with('success', 'Rilasciato da creato con successo.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('rilasciato_da', 'name')->ignore($id)],
        ]);

        $rilasciato = RilasciatoDa::findOrFail($id);
        $rilasciato->update($data);

        return redirect()->back()->with('success', 'Rilasciato da aggiornato con successo.');
    }

    public function destroy($id)
    {
        $rilasciato = RilasciatoDa::findOrFail($id);
        $rilasciato->delete();

        return redirect()->back()->with('success', 'Rilasciato da eliminato con successo.');
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
