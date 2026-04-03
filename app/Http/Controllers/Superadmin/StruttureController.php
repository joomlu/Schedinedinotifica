<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Proprietario;
use App\Models\Struttura;
use Illuminate\Http\Request;

class StruttureController extends Controller
{
    public function index()
    {
        $strutture = Struttura::with('proprietario')->orderBy('nome_struttura')->get();
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.index', [
            'strutture' => $strutture,
            'proprietari' => $proprietari,
        ]);
    }

    public function create()
    {
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.form', [
            'struttura' => new Struttura(),
            'proprietari' => $proprietari,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome_struttura' => ['required', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'attiva' => ['nullable', 'boolean'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        Struttura::create([
            'nome_struttura' => $data['nome_struttura'],
            'citta' => $data['citta'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'proprietario_id' => $data['proprietario_id'] ?? null,
            'attiva' => $data['attiva'] ?? true,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? null,
            'piano' => $data['piano'] ?? null,
            'stato_pagamento' => $data['stato_pagamento'] ?? null,
        ]);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Struttura creata');
    }

    public function edit(int $id)
    {
        $struttura = Struttura::findOrFail($id);
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.form', [
            'struttura' => $struttura,
            'proprietari' => $proprietari,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $struttura = Struttura::findOrFail($id);

        $data = $request->validate([
            'nome_struttura' => ['required', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'attiva' => ['nullable', 'boolean'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        $struttura->update([
            'nome_struttura' => $data['nome_struttura'],
            'citta' => $data['citta'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'proprietario_id' => $data['proprietario_id'] ?? null,
            'attiva' => $data['attiva'] ?? $struttura->attiva,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? null,
            'piano' => $data['piano'] ?? null,
            'stato_pagamento' => $data['stato_pagamento'] ?? null,
        ]);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Struttura aggiornata');
    }

    public function updateServizio(Request $request, int $id)
    {
        $struttura = Struttura::findOrFail($id);

        $data = $request->validate([
            'attiva' => ['nullable', 'boolean'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        $struttura->update([
            'attiva' => $data['attiva'] ?? $struttura->attiva,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? $struttura->scadenza_servizio,
            'piano' => $data['piano'] ?? $struttura->piano,
            'stato_pagamento' => $data['stato_pagamento'] ?? $struttura->stato_pagamento,
        ]);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Servizio aggiornato');
    }
}
