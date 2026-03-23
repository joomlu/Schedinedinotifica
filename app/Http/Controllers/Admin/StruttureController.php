<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
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
            'articoli' => LicenzaArticolo::with('parent')->where('attivo', true)->orderBy('ordine')->orderBy('nome')->get(),
            'licenzeAssegnate' => collect(),
            'movimentiStruttura' => collect(),
            'statiLicenza' => ['da_pagare', 'pagato', 'parziale', 'sospeso'],
            'mode' => 'create',
            'activeTab' => request('tab', 'dati'),
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
            'active_tab' => ['nullable', 'string', 'max:50'],
        ]);

        if (!empty($data['proprietario_id']) && !in_array($data['proprietario_id'], $proprietariIds, true)) {
            abort(403);
        }

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

        return redirect()
            ->route('admin.strutture.edit', ['id' => $struttura->id, 'tab' => $data['active_tab'] ?? 'dati'])
            ->with('status', 'Struttura creata');
    }

    public function edit(Request $request, int $id)
    {
        $struttura = $this->baseQuery($request)->with(['proprietario.admin'])->findOrFail($id);
        $proprietari = Proprietario::where('admin_id', $request->user()->id)->orderBy('nome')->get();

        return view('admin.strutture.form', [
            'struttura' => $struttura,
            'proprietari' => $proprietari,
            'articoli' => LicenzaArticolo::with('parent')->where('attivo', true)->orderBy('ordine')->orderBy('nome')->get(),
            'licenzeAssegnate' => LicenzaAssegnazione::with(['articolo.parent', 'admin', 'proprietario'])
                ->where('struttura_id', $struttura->id)
                ->where(function ($query) use ($request) {
                    $adminId = (int) $request->user()->id;
                    $query->where('admin_id', $adminId)
                        ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId));
                })
                ->orderByDesc('attiva')
                ->orderByDesc('data_scadenza')
                ->get(),
            'movimentiStruttura' => ProprietarioFatturazione::with(['proprietario', 'righe'])
                ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $request->user()->id))
                ->whereHas('righe', fn ($query) => $query->where('struttura_id', $struttura->id))
                ->orderByDesc('data_documento')
                ->orderByDesc('id')
                ->get(),
            'statiLicenza' => ['da_pagare', 'pagato', 'parziale', 'sospeso'],
            'mode' => 'edit',
            'activeTab' => request('tab', 'dati'),
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
            'active_tab' => ['nullable', 'string', 'max:50'],
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

        return redirect()
            ->route('admin.strutture.edit', ['id' => $struttura->id, 'tab' => $data['active_tab'] ?? 'dati'])
            ->with('status', 'Struttura aggiornata');
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
