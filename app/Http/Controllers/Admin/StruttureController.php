<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proprietario;
use App\Models\Struttura;
use Illuminate\Http\Request;

class StruttureController extends Controller
{
    protected function baseQuery(Request $request)
    {
        return Struttura::whereHas('proprietario', function ($q) use ($request) {
            $q->where('admin_id', $request->user()->id);
        });
    }

    public function index(Request $request)
    {
        $strutture = $this->baseQuery($request)->with('proprietario')->orderBy('nome_struttura')->get();
        $proprietari = Proprietario::where('admin_id', $request->user()->id)->orderBy('nome')->get();

        return view('admin.strutture.index', [
            'strutture' => $strutture,
            'proprietari' => $proprietari,
        ]);
    }

    public function create(Request $request)
    {
        $proprietari = Proprietario::where('admin_id', $request->user()->id)->orderBy('nome')->get();

        return view('admin.strutture.form', [
            'struttura' => new Struttura(),
            'proprietari' => $proprietari,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $proprietariIds = Proprietario::where('admin_id', $request->user()->id)->pluck('id')->all();

        $data = $request->validate([
            'nome_struttura' => ['required', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'proprietario_id' => ['nullable', 'integer'],
            'attiva' => ['nullable', 'boolean'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        if (!empty($data['proprietario_id']) && !in_array($data['proprietario_id'], $proprietariIds, true)) {
            abort(403);
        }

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

        return redirect()->route('admin.strutture.index')->with('status', 'Struttura creata');
    }

    public function edit(Request $request, int $id)
    {
        $struttura = $this->baseQuery($request)->findOrFail($id);
        $proprietari = Proprietario::where('admin_id', $request->user()->id)->orderBy('nome')->get();

        return view('admin.strutture.form', [
            'struttura' => $struttura,
            'proprietari' => $proprietari,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $struttura = $this->baseQuery($request)->findOrFail($id);
        $proprietariIds = Proprietario::where('admin_id', $request->user()->id)->pluck('id')->all();

        $data = $request->validate([
            'nome_struttura' => ['required', 'string', 'max:255'],
            'citta' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'proprietario_id' => ['nullable', 'integer'],
            'attiva' => ['nullable', 'boolean'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        if (!empty($data['proprietario_id']) && !in_array($data['proprietario_id'], $proprietariIds, true)) {
            abort(403);
        }

        $struttura->update([
            'nome_struttura' => $data['nome_struttura'],
            'citta' => $data['citta'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'proprietario_id' => $data['proprietario_id'] ?? null,
            'attiva' => $data['attiva'] ?? $struttura->attiva,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? $struttura->scadenza_servizio,
            'piano' => $data['piano'] ?? $struttura->piano,
            'stato_pagamento' => $data['stato_pagamento'] ?? $struttura->stato_pagamento,
        ]);

        return redirect()->route('admin.strutture.index')->with('status', 'Struttura aggiornata');
    }

    public function updateServizio(Request $request, int $id)
    {
        $struttura = $this->baseQuery($request)->findOrFail($id);

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

        return redirect()->route('admin.strutture.index')->with('status', 'Servizio aggiornato');
    }
}
