<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\RilasciatoDa;
use App\Services\CestinoService;

class RilasciatoDaController extends Controller
{
    public function index(Request $request)
    {
        abort_if(is_array($request->query('q')), 400);
        $request->validate(['q' => ['nullable', 'string', 'max:191']]);
        app(\App\Services\CataloghiIndirizziStrutturaService::class)->initialize((int) \App\Support\StrutturaCorrente::getId());
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = RilasciatoDa::query();

        if ($q !== '' && $terms === []) $query->whereRaw('1 = 0');
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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $this->lockCatalog();
            RilasciatoDa::create($this->validated($request));
            return redirect()->back()->with('success', 'Record creato nella struttura corrente.');
        });
    }

    public function update(Request $request, $id)
    {
        abort_unless(preg_match('/^[1-9][0-9]*$/D', (string) $id), 404);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
            $this->lockCatalog();
            $record = RilasciatoDa::findOrFail($id);
            $data = $this->validated($request, $record);
            if ($record->name !== $data['name'] && app(\App\Services\CataloghiIndirizziStrutturaService::class)->references($record)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'Il record è utilizzato: conserva il valore per non alterare i riferimenti esistenti.']);
            }
            $record->update($data);
            return redirect()->back()->with('success', 'Record aggiornato nella struttura corrente.');
        });
    }

    public function destroy($id)
    {
        abort_unless(preg_match('/^[1-9][0-9]*$/D', (string) $id), 404);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $this->lockCatalog();
            $record = RilasciatoDa::findOrFail($id);
            if (app(\App\Services\CataloghiIndirizziStrutturaService::class)->references($record)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'Il record è utilizzato e non può essere eliminato.']);
            }
            app(CestinoService::class)->archiveModel($record);
            $record->delete();
            return redirect()->back()->with('success', 'Record spostato nel cestino della struttura corrente.');
        });
    }

    private function lockCatalog(): void
    {
        \Illuminate\Support\Facades\DB::table('struttura')->where('id', \App\Support\StrutturaCorrente::getId())->lockForUpdate()->first();
        app(\App\Services\CataloghiIndirizziStrutturaService::class)->initialize((int) \App\Support\StrutturaCorrente::getId());
    }

    private function validated(Request $request, ?RilasciatoDa $record = null): array
    {
        $sid = \App\Support\StrutturaCorrente::getId();
        $id = $record?->id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('rilasciato_da', 'name')->where('struttura_id', $sid)->ignore($id)],
        ]);
        return $data;
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
