<?php

namespace App\Http\Controllers;

use App\Models\Gruppo;
use App\Services\CestinoService;
use App\Services\GruppiStrutturaService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:191']]);
        app(GruppiStrutturaService::class)->initialize((int) StrutturaCorrente::getId());
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
                    $like = '%'.$term.'%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('nome').' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione').' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('tipo').' LIKE ?', [$like])
                        ->orWhereHas('parent', function ($parent) use ($like) {
                            $parent->whereRaw($this->normalizedFieldExpr('nome').' LIKE ?', [$like]);
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
        return DB::transaction(function () use ($request) {
            $this->lockCatalog();
            $validator = Validator::make($request->all(), [
                'livello' => ['required', 'in:1,2,3'],
                'nome' => ['required', 'string', 'max:100'],
                'descrizione' => ['nullable', 'string', 'max:191'],
                'parent_id' => ['nullable', 'integer', Rule::exists('gruppi', 'id')->where('struttura_id', StrutturaCorrente::getId())],
            ]);

            $validator->after(function ($validator) use ($request) {
                $this->validateHierarchy($validator, (int) $request->input('livello'), $request->input('parent_id'));
            });

            $data = $validator->validate();

            $data['tipo'] = Gruppo::tipoFromLivello((int) $data['livello']);
            $data['parent_id'] = $data['parent_id'] ?? null;

            $this->validateDuplicate($data);
            Gruppo::create($data);

            return redirect()->back()->with('success', 'Gruppo creato con successo.');
        });
    }

    public function update(Request $request, $id)
    {
        abort_unless(preg_match('/^[1-9][0-9]*$/D', (string) $id), 404);

        return DB::transaction(function () use ($request, $id) {
            $this->lockCatalog();
            $gruppo = Gruppo::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'livello' => ['required', 'in:1,2,3'],
                'nome' => ['required', 'string', 'max:100'],
                'descrizione' => ['nullable', 'string', 'max:191'],
                'parent_id' => ['nullable', 'integer', Rule::exists('gruppi', 'id')->where('struttura_id', StrutturaCorrente::getId())],
            ]);

            $validator->after(function ($validator) use ($request) {
                $this->validateHierarchy($validator, (int) $request->input('livello'), $request->input('parent_id'));
            });

            $data = $validator->validate();

            $data['tipo'] = Gruppo::tipoFromLivello((int) $data['livello']);
            $data['parent_id'] = $data['parent_id'] ?? null;

            $this->validateDuplicate($data, $gruppo->id);
            $ancestor = $data['parent_id'];
            $visited = [$gruppo->id];
            while ($ancestor !== null) {
                if (in_array((int) $ancestor, $visited, true)) {
                    throw ValidationException::withMessages(['parent_id' => 'Il padre selezionato crea un ciclo nella gerarchia.']);
                }
                $visited[] = (int) $ancestor;
                $ancestor = Gruppo::find($ancestor)?->parent_id;
            }
            if ((int) $data['livello'] !== $gruppo->livello && $gruppo->children()->exists()) {
                throw ValidationException::withMessages(['livello' => 'Il livello non può cambiare finché il gruppo ha figli.']);
            }
            if (((int) $data['livello'] !== $gruppo->livello || $data['parent_id'] != $gruppo->parent_id)
                && app(GruppiStrutturaService::class)->references($gruppo)) {
                throw ValidationException::withMessages(['livello' => 'Il gruppo o i suoi figli sono utilizzati: conserva livello e padre.']);
            }

            if ($data['nome'] !== $gruppo->nome) {
                app(GruppiStrutturaService::class)->renameReferences($gruppo, $data['nome']);
            }
            $gruppo->update($data);

            return redirect()->back()->with('success', 'Gruppo aggiornato con successo.');
        });
    }

    public function destroy($id)
    {
        abort_unless(preg_match('/^[1-9][0-9]*$/D', (string) $id), 404);

        return DB::transaction(function () use ($id) {
            $this->lockCatalog();
            $gruppo = Gruppo::findOrFail($id);
            if ($gruppo->children()->exists() || app(GruppiStrutturaService::class)->references($gruppo)) {
                throw ValidationException::withMessages(['gruppo' => 'Il gruppo ha figli oppure è utilizzato da clienti o schedine e non può essere eliminato.']);
            }
            app(CestinoService::class)->archiveModel($gruppo, [
                'source' => 'Gruppi',
            ]);
            $gruppo->delete();

            return redirect()->back()->with('success', 'Gruppo spostato nel cestino.');
        });
    }

    private function lockCatalog(): void
    {
        DB::table('struttura')->where('id', StrutturaCorrente::getId())->lockForUpdate()->first();
        Gruppo::query()->orderBy('id')->lockForUpdate()->get();
    }

    private function validateDuplicate(array $data, ?int $except = null): void
    {
        if (Gruppo::where('livello', $data['livello'])->where('parent_id', $data['parent_id'])->where('nome', $data['nome'])
            ->when($except, fn ($q) => $q->where('id', '<>', $except))->exists()) {
            throw ValidationException::withMessages(['nome' => 'Esiste già un gruppo con questo nome, livello e padre nella struttura.']);
        }
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
        if (! is_string($clean) || $clean === '') {
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
