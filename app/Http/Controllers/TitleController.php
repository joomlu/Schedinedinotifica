<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Titolo;
use App\Services\CestinoService;

class TitleController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = Titolo::query();

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('nome') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione') . ' LIKE ?', [$like]);
                }
            });
        }

        $titoli = $query->orderBy('nome')->paginate(10)->withQueryString();
        return view('titolo.list', ['titoli' => $titoli]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:50',
            'descrizione' => 'nullable|string|max:150',
            'attivo' => 'nullable|boolean',
        ]);

        $data['attivo'] = $request->has('attivo') ? $request->boolean('attivo') : true;

        Titolo::create($data);
        return redirect()->back()->with('success', 'Titolo creato con successo.');
    }

    public function update(Request $request, Titolo $titolo)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:50',
            'descrizione' => 'nullable|string|max:150',
            'attivo' => 'nullable|boolean',
        ]);

        $data['attivo'] = $request->has('attivo') ? $request->boolean('attivo') : ($titolo->attivo ?? true);

        $titolo->fill($data)->save();
        return redirect()->back()->with('success', 'Titolo aggiornato con successo.');
    }

    public function destroy(Titolo $titolo)
    {
        app(CestinoService::class)->archiveModel($titolo, [
            'source' => 'Titoli',
        ]);
        $titolo->delete();
        return redirect()->back()->with('success', 'Titolo spostato nel cestino.');
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
