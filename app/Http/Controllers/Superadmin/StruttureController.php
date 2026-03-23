<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
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
            'struttura' => new Struttura([
                'proprietario_id' => request()->integer('proprietario_id') ?: null,
            ]),
            'proprietari' => $proprietari,
            'articoli' => LicenzaArticolo::with('parent')->orderBy('ordine')->orderBy('nome')->get(),
            'licenzeAssegnate' => collect(),
            'movimentiStruttura' => collect(),
            'statiLicenza' => ['da_pagare', 'pagato', 'parziale', 'sospeso'],
            'mode' => 'create',
            'returnToOwnerId' => request()->integer('return_to_owner_id') ?: null,
            'activeTab' => request('tab', 'dati'),
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
            'return_to_owner_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'active_tab' => ['nullable', 'string', 'max:50'],
        ]);

        $struttura = Struttura::create([
            'nome_struttura' => $data['nome_struttura'],
            'citta' => $data['citta'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'proprietario_id' => $data['proprietario_id'] ?? null,
            'attiva' => $data['attiva'] ?? true,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? null,
            'piano' => $data['piano'] ?? null,
            'stato_pagamento' => $data['stato_pagamento'] ?? null,
        ]);

        if (!empty($data['return_to_owner_id'])) {
            return redirect()
                ->route('superadmin.proprietari.edit', ['id' => $data['return_to_owner_id'], 'tab' => 'strutture'])
                ->with('success', 'Struttura creata e assegnata correttamente.');
        }

        return redirect()
            ->route('superadmin.strutture.edit', ['id' => $struttura->id, 'tab' => $data['active_tab'] ?? 'dati'])
            ->with('status', 'Struttura creata');
    }

    public function edit(int $id)
    {
        $struttura = Struttura::with(['proprietario.admin'])->findOrFail($id);
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.form', [
            'struttura' => $struttura,
            'proprietari' => $proprietari,
            'articoli' => LicenzaArticolo::with('parent')->orderBy('ordine')->orderBy('nome')->get(),
            'licenzeAssegnate' => LicenzaAssegnazione::with(['articolo.parent', 'admin', 'proprietario'])
                ->where('struttura_id', $struttura->id)
                ->orderByDesc('attiva')
                ->orderByDesc('data_scadenza')
                ->get(),
            'movimentiStruttura' => ProprietarioFatturazione::with(['proprietario', 'righe'])
                ->whereHas('righe', fn ($query) => $query->where('struttura_id', $struttura->id))
                ->orderByDesc('data_documento')
                ->orderByDesc('id')
                ->get(),
            'statiLicenza' => ['da_pagare', 'pagato', 'parziale', 'sospeso'],
            'mode' => 'edit',
            'returnToOwnerId' => request()->integer('return_to_owner_id') ?: null,
            'activeTab' => request('tab', 'dati'),
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
            'return_to_owner_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'active_tab' => ['nullable', 'string', 'max:50'],
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

        if (!empty($data['return_to_owner_id'])) {
            return redirect()
                ->route('superadmin.proprietari.edit', ['id' => $data['return_to_owner_id'], 'tab' => 'strutture'])
                ->with('success', 'Struttura aggiornata.');
        }

        return redirect()
            ->route('superadmin.strutture.edit', ['id' => $struttura->id, 'tab' => $data['active_tab'] ?? 'dati'])
            ->with('status', 'Struttura aggiornata');
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
