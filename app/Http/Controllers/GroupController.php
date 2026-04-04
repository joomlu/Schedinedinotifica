<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Gruppo;
use App\Services\CestinoService;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $qRaw = trim((string) $request->query('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);

        $query = Gruppo::with('parent');

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q)
                        ->orWhere('livello', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('nome') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('tipo') . ' LIKE ?', [$like])
                        ->orWhereHas('parent', function ($parent) use ($like) {
                            $parent->whereRaw($this->normalizedFieldExpr('nome') . ' LIKE ?', [$like]);
                        });
                }
            });
        }

        $gruppi = $query
            ->orderBy('livello')
            ->orderBy('tipo')
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $gruppiLivello1 = Gruppo::where('livello', 1)->orderBy('nome')->get();
        $gruppiLivello2 = Gruppo::where('livello', 2)->orderBy('nome')->get();

        return view('gruppi.index', [
            'gruppi' => $gruppi,
            'gruppiLivello1' => $gruppiLivello1,
            'gruppiLivello2' => $gruppiLivello2,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'livello' => ['required', 'in:1,2,3'],
            'nome' => ['required', 'string', 'max:100'],
            'descrizione' => ['nullable', 'string', 'max:191'],
            'parent_id' => ['nullable', 'integer', 'exists:gruppi,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validateHierarchy($validator, (int) $request->input('livello'), $request->input('parent_id'));
        });

        $data = $validator->validate();

        $data['tipo'] = Gruppo::tipoFromLivello((int) $data['livello']);
        $data['parent_id'] = $data['parent_id'] ?? null;

        Gruppo::create($data);

        return redirect()->back()->with('success', 'Gruppo creato con successo.');
    }

    public function update(Request $request, $id)
    {
        $gruppo = Gruppo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'livello' => ['required', 'in:1,2,3'],
            'nome' => ['required', 'string', 'max:100'],
            'descrizione' => ['nullable', 'string', 'max:191'],
            'parent_id' => ['nullable', 'integer', 'exists:gruppi,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validateHierarchy($validator, (int) $request->input('livello'), $request->input('parent_id'));
        });

        $data = $validator->validate();

        $data['tipo'] = Gruppo::tipoFromLivello((int) $data['livello']);
        $data['parent_id'] = $data['parent_id'] ?? null;

        $gruppo->update($data);

        return redirect()->back()->with('success', 'Gruppo aggiornato con successo.');
    }

    public function destroy($id)
    {
        $gruppo = Gruppo::findOrFail($id);
        app(CestinoService::class)->archiveModel($gruppo, [
            'source' => 'Gruppi',
        ]);
        $gruppo->delete();

        return redirect()->back()->with('success', 'Gruppo spostato nel cestino.');
    }

    private function validateHierarchy($validator, int $livello, $parentId): void
    {
        $parentId = $parentId ? (int) $parentId : null;

        if ($livello === 1) {
            if ($parentId !== null) {
                $validator->errors()->add('parent_id', 'Il livello 1 non può avere un padre.');
            }
            return;
        }

        if ($parentId === null) {
            $validator->errors()->add('parent_id', 'Seleziona un padre per questo livello.');
            return;
        }

        $parent = Gruppo::find($parentId);

        if (! $parent) {
            $validator->errors()->add('parent_id', 'Il padre selezionato non esiste.');
            return;
        }

        if ($livello === 2 && (int) $parent->livello !== 1) {
            $validator->errors()->add('parent_id', 'Per il livello 2 il padre deve essere un Gruppo I.');
        }

        if ($livello === 3 && (int) $parent->livello !== 2) {
            $validator->errors()->add('parent_id', 'Per il livello 3 il padre deve essere un Gruppo II.');
        }
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
