<?php

namespace App\Http\Controllers;

use App\Models\Comuni;
use App\Models\TassaEssente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TassaEssenteController extends Controller
{
    /**
     * Return esenzioni list for a given comune_id as JSON
     */
    public function indexByComune(Request $request)
    {
        $comuneId = $request->query('comune_id');
        if (empty($comuneId)) {
            return response()->json([
                'success' => false,
                'message' => 'Parametro comune_id mancante',
                'items' => [],
            ], 400);
        }

        $items = TassaEssente::byComune($comuneId)
            ->orderByDesc('id')
            ->get(['id', 'cod_esenz', 'nome', 'descrizione', 'active', 'is_system']);

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        // Authorization: only admin can create
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Azione non autorizzata',
                ], 403);
            }
            abort(403, 'Azione non autorizzata');
        }

        // Determine comune id (resolve by name if provided, do NOT create)
        $comuneId = $request->input('comune_id');
        if (empty($comuneId) && $request->filled('comune_name')) {
            $comuneName = trim($request->input('comune_name'));
            $comune = Comuni::whereRaw('LOWER(denominazione) = ?', [mb_strtolower($comuneName)])
                ->first();
            if (! $comune) {
                $comune = Comuni::where('denominazione', 'like', '%'.$comuneName.'%')->first();
            }
            if ($comune) {
                $comuneId = $comune->id;
            }
        }

        if (empty($comuneId)) {
            $message = 'Comune non trovato. Seleziona una città valida nella sezione Tassa di Soggiorno.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['comune_id' => [$message]],
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['comune_id' => $message], 'tassaessente')
                ->withInput()
                ->with('active_tab', 'product1');
        }

        $validator = Validator::make($request->all(), [
            'cod_esenz' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tassa_essenti')->where(function ($query) use ($comuneId) {
                    return $query->where('comuni_tassa_esenti_id', $comuneId);
                }),
            ],
            'nome' => 'required|string|max:100',
            'descrizione' => 'nullable|string',
            'active' => 'nullable|in:on,1',
        ], [
            'cod_esenz.unique' => 'Questo codice esenzione esiste già per questo comune',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator, 'tassaessente')
                ->withInput()
                ->with('active_tab', 'product1');
        }

        $data = $validator->validated();

        $t = TassaEssente::create([
            'comuni_tassa_esenti_id' => $comuneId,
            'cod_esenz' => $data['cod_esenz'],
            'nome' => $data['nome'],
            'descrizione' => $data['descrizione'] ?? null,
            'active' => isset($data['active']) ? true : false,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Esenzione creata',
                'esenzione' => $t,
            ]);
        }

        return redirect()->back()->with('active_tab', 'product1')->with('message', 'Esenzione creata');
    }

    public function update(Request $request, $id)
    {
        // Authorization: only admin can update
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Azione non autorizzata',
                ], 403);
            }
            abort(403, 'Azione non autorizzata');
        }

        $t = TassaEssente::findOrFail($id);

        // Prevent updating system records
        if ($t->is_system) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non è possibile modificare le esenzioni di sistema',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Non è possibile modificare le esenzioni di sistema')
                ->with('active_tab', 'product1');
        }

        $comuneId = $t->comuni_tassa_esenti_id;

        $validator = Validator::make($request->all(), [
            'cod_esenz' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tassa_essenti')->ignore($id)->where(function ($query) use ($comuneId) {
                    return $query->where('comuni_tassa_esenti_id', $comuneId);
                }),
            ],
            'nome' => 'required|string|max:100',
            'descrizione' => 'nullable|string',
            'active' => 'nullable|in:on,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_tab', 'product1');
        }

        $data = $validator->validated();

        $t->cod_esenz = $data['cod_esenz'];
        $t->nome = $data['nome'];
        $t->descrizione = $data['descrizione'] ?? null;
        $t->active = isset($data['active']) ? true : false;
        $t->save();

        return redirect()->back()->with('active_tab', 'product1')->with('message', 'Esenzione aggiornata');
    }

    public function destroy($id)
    {
        // Authorization: only admin can delete
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Azione non autorizzata',
                ], 403);
            }
            abort(403, 'Azione non autorizzata');
        }

        $t = TassaEssente::find($id);
        if ($t) {
            // Prevent deleting system records
            if ($t->is_system) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Non è possibile eliminare le esenzioni di sistema',
                    ], 403);
                }

                return redirect()->back()
                    ->with('error', 'Non è possibile eliminare le esenzioni di sistema')
                    ->with('active_tab', 'product1');
            }

            $t->delete();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'id' => (int) $id,
                'message' => 'Esenzione rimossa',
            ]);
        }

        return redirect()->back()
            ->with('active_tab', 'product1')
            ->with('message', 'Esenzione rimossa');
    }

    public function checkCodEsenz(Request $request)
    {
        $comuneId = $request->input('comune_id');
        if (empty($comuneId) && $request->filled('comune_name')) {
            $comune = Comuni::where('denominazione', $request->input('comune_name'))->first();
            if ($comune) {
                $comuneId = $comune->id;
            }
        }

        $exists = TassaEssente::where('comuni_tassa_esenti_id', $comuneId)
            ->where('cod_esenz', $request->input('cod_esenz'))
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
