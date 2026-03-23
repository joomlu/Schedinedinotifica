<?php

namespace App\Http\Controllers;

use App\Models\TassaEsenzione;
use App\Support\StrutturaCorrente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TassaEsenzioneController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $strutturaId = StrutturaCorrente::getId();
        if (!$strutturaId) {
            return back()->withErrors(['struttura_id' => 'Seleziona prima una struttura.']);
        }

        $data = $this->validatePayload($request, $strutturaId);
        $data['struttura_id'] = $strutturaId;

        TassaEsenzione::create($data);

        return back()->with('success', 'Esenzione creata.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $strutturaId = StrutturaCorrente::getId();
        if (!$strutturaId) {
            return back()->withErrors(['struttura_id' => 'Seleziona prima una struttura.']);
        }

        $esenzione = TassaEsenzione::findOrFail($id);
        $data = $this->validatePayload($request, $strutturaId, $esenzione->id);

        $esenzione->update($data);

        return back()->with('success', 'Esenzione aggiornata.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->ensureAdminAccess(request());

        $esenzione = TassaEsenzione::findOrFail($id);
        $esenzione->delete();

        return back()->with('success', 'Esenzione eliminata.');
    }

    private function validatePayload(Request $request, int $strutturaId, ?int $ignoreId = null): array
    {
        $request->merge([
            'codice' => strtoupper(trim((string) $request->input('codice'))),
        ]);

        $data = $request->validate([
            'codice' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tassa_esenzioni')->where(fn($q) => $q->where('struttura_id', $strutturaId))->ignore($ignoreId),
            ],
            'descrizione' => ['required', 'string', 'max:255'],
            'ordine' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'attivo' => ['nullable', 'boolean'],
            'richiede_nota' => ['nullable', 'boolean'],
        ]);

        if (($data['codice'] ?? null) === '777') {
            throw ValidationException::withMessages([
                'codice' => 'Il codice 777 è riservato dal sistema per i pernottamenti oltre il limite massimo e non va configurato come esenzione.',
            ]);
        }

        return $data;
    }

    private function ensureAdminAccess(Request $request): void
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            throw new AuthorizationException('Operazione riservata ad admin e super admin.');
        }
    }
}
