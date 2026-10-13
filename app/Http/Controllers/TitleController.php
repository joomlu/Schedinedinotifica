<?php

namespace App\Http\Controllers;

use App\Models\Titolo;
use App\Services\CestinoService;
use App\Services\TitoliStrutturaService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TitleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:191']]);
        app(TitoliStrutturaService::class)->initialize((int) StrutturaCorrente::getId());
        $raw = trim((string) $request->query('q', ''));
        $q = $this->normalize($raw);
        $query = Titolo::query();
        if ($raw !== '') {
            $query->where(function ($inner) use ($raw, $q) {
                if (ctype_digit($raw)) $inner->orWhere('id', (int) $raw);
                if ($q !== '') {
                    $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
                    foreach (['nome', 'descrizione'] as $field) {
                        $inner->orWhereRaw($this->field($field)." LIKE ? ESCAPE '!'", [$like]);
                    }
                } elseif (! ctype_digit($raw)) $inner->whereRaw('1 = 0');
            });
        }
        return view('titolo.list', ['titoli' => $query->orderBy('nome')->orderBy('id')->paginate(10)->withQueryString()]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $this->lockCatalog();
            Titolo::create($this->validated($request));
            return redirect()->back()->with('success', 'Titolo creato nella struttura corrente.');
        });
    }

    public function update(Request $request, $titolo)
    {
        $this->validateId($titolo);
        return DB::transaction(function () use ($request, $titolo) {
            $this->lockCatalog();
            $record = Titolo::findOrFail($titolo);
            $data = $this->validated($request, $record->id);
            if ($record->nome !== $data['nome']) app(TitoliStrutturaService::class)->renameReferences($record, $data['nome']);
            $record->update($data);
            return redirect()->back()->with('success', 'Titolo aggiornato nella struttura corrente.');
        });
    }

    public function destroy($titolo)
    {
        $this->validateId($titolo);
        return DB::transaction(function () use ($titolo) {
            $this->lockCatalog();
            $record = Titolo::findOrFail($titolo);
            if (app(TitoliStrutturaService::class)->references($record)) {
                throw ValidationException::withMessages(['titolo' => 'Il titolo è utilizzato da clienti, schedine o archivi. Puoi disattivarlo, ma non eliminarlo.']);
            }
            app(CestinoService::class)->archiveModel($record, ['source' => 'Titoli']);
            $record->delete();
            return redirect()->back()->with('success', 'Titolo della struttura spostato nel cestino.');
        });
    }

    public function recover(Request $request)
    {
        $request->validate(['conferma' => ['required', 'accepted']]);
        $result = app(TitoliStrutturaService::class)->recoverBase((int) StrutturaCorrente::getId());
        $response = redirect()->back()->with('success', 'Recupero completato: '.$result['aggiunti'].' titoli mancanti aggiunti. Personalizzazioni e relazioni conservate.');
        if ($result['conflitti']) $response->with('warning', 'Nomi già utilizzati, conservati senza sovrascrittura: '.implode(', ', $result['conflitti']).'.');
        return $response;
    }

    private function validated(Request $request, ?int $except = null): array
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:50', Rule::unique('titolo', 'nome')->where('struttura_id', StrutturaCorrente::getId())->ignore($except)],
            'descrizione' => ['nullable', 'string', 'max:150'],
            'attivo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'Inserisci il nome del titolo.', 'nome.max' => 'Il nome può contenere al massimo 50 caratteri.',
            'nome.unique' => 'Esiste già un titolo con questo nome nella struttura.', 'nome.string' => 'Il nome deve essere un testo valido.',
            'descrizione.max' => 'La descrizione può contenere al massimo 150 caratteri.', 'descrizione.string' => 'La descrizione deve essere un testo valido.',
            'attivo.boolean' => 'Lo stato del titolo non è valido.',
        ]);
        $data['attivo'] = $request->boolean('attivo');
        return $data;
    }

    private function lockCatalog(): void
    {
        app(TitoliStrutturaService::class)->initialize((int) StrutturaCorrente::getId());
        DB::table('struttura')->where('id', StrutturaCorrente::getId())->lockForUpdate()->first();
    }

    private function validateId($id): void
    {
        abort_unless(preg_match('/^[1-9][0-9]*$/D', (string) $id), 404);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($value));
    }

    private function field(string $field): string
    {
        $expr = "LOWER(COALESCE($field, ''))";
        foreach (["'", '.', ',', '-', '/', '(', ')', ' '] as $char) {
            $expr = "REPLACE($expr, '".str_replace("'", "''", $char)."', '')";
        }
        return $expr;
    }
}
