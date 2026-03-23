<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AdminFatturazione;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
use App\Models\Struttura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PagamentiController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'attiva' => trim((string) $request->query('attiva', '')),
            'stato_pagamento' => trim((string) $request->query('stato_pagamento', '')),
            'scadenza' => trim((string) $request->query('scadenza', '')),
        ];
        $contoFilters = [
            'admin_id' => $request->integer('conto_admin_id') ?: null,
            'proprietario_id' => $request->integer('conto_proprietario_id') ?: null,
            'struttura_id' => $request->integer('conto_struttura_id') ?: null,
        ];

        $query = Struttura::with(['proprietario.admin'])->orderBy('nome_struttura');

        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $query->where(function ($subquery) use ($term) {
                $subquery->where('nome_struttura', 'like', '%' . $term . '%')
                    ->orWhere('citta', 'like', '%' . $term . '%')
                    ->orWhere('provincia', 'like', '%' . $term . '%')
                    ->orWhere('piano', 'like', '%' . $term . '%')
                    ->orWhere('stato_pagamento', 'like', '%' . $term . '%')
                    ->orWhereHas('proprietario', function ($ownerQuery) use ($term) {
                        $ownerQuery->where('nome', 'like', '%' . $term . '%')
                            ->orWhereHas('admin', function ($adminQuery) use ($term) {
                                $adminQuery->where('name', 'like', '%' . $term . '%')
                                    ->orWhere('email', 'like', '%' . $term . '%');
                            });
                    });
            });
        }

        if ($filters['attiva'] !== '') {
            $query->where('attiva', $filters['attiva'] === '1');
        }

        if ($filters['stato_pagamento'] !== '') {
            $query->where('stato_pagamento', $filters['stato_pagamento']);
        }

        if ($filters['scadenza'] === 'scadute') {
            $query->whereDate('scadenza_servizio', '<', now()->toDateString());
        } elseif ($filters['scadenza'] === 'entro_30') {
            $query->whereBetween('scadenza_servizio', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($filters['scadenza'] === 'senza_data') {
            $query->whereNull('scadenza_servizio');
        }

        $strutture = $query->get();
        $statiPagamento = Struttura::query()
            ->whereNotNull('stato_pagamento')
            ->where('stato_pagamento', '!=', '')
            ->distinct()
            ->orderBy('stato_pagamento')
            ->pluck('stato_pagamento');

        $articoli = LicenzaArticolo::with('parent')->orderBy('ordine')->orderBy('nome')->get();
        $assegnazioni = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])
            ->orderByDesc('attiva')
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->get();

        $proprietari = Proprietario::with('admin')->orderBy('nome')->get();
        $struttureDisponibili = Struttura::with('proprietario')->orderBy('nome_struttura')->get();
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        $today = now()->startOfDay();
        $summary = [
            'totale' => $strutture->count(),
            'attive' => $strutture->where('attiva', true)->count(),
            'scadute' => $strutture->filter(fn (Struttura $struttura) => $struttura->scadenza_servizio && $struttura->scadenza_servizio->lt($today))->count(),
            'entro_30' => $strutture->filter(fn (Struttura $struttura) => $struttura->scadenza_servizio && $struttura->scadenza_servizio->between($today, (clone $today)->addDays(30)))->count(),
            'senza_data' => $strutture->whereNull('scadenza_servizio')->count(),
            'articoli_attivi' => $articoli->where('attivo', true)->count(),
            'licenze_attive' => $assegnazioni->where('attiva', true)->count(),
        ];

        return view('superadmin.pagamenti.index', [
            'strutture' => $strutture,
            'filters' => $filters,
            'statiPagamento' => $statiPagamento,
            'summary' => $summary,
            'articoli' => $articoli,
            'assegnazioni' => $assegnazioni,
            'proprietari' => $proprietari,
            'struttureDisponibili' => $struttureDisponibili,
            'admins' => $admins,
            'canManageArticoli' => false,
            'pagamentiBaseRoute' => 'superadmin.pagamenti',
            'servizioRoutePrefix' => 'superadmin.strutture',
            'strutturaEditRoute' => 'superadmin.strutture.edit',
            'contoFilters' => $contoFilters,
            'statoConto' => $this->buildStatoConto($assegnazioni, $contoFilters),
            'adminProformeConto' => $this->buildAdminProformeConto($contoFilters),
        ]);
    }

    private function buildStatoConto($assegnazioni, array $filters): array
    {
        $licenze = collect($assegnazioni)
            ->filter(function (LicenzaAssegnazione $assegnazione) use ($filters) {
                if ($filters['admin_id'] && (int) $assegnazione->admin_id !== (int) $filters['admin_id']) {
                    return false;
                }
                if ($filters['proprietario_id'] && (int) $assegnazione->proprietario_id !== (int) $filters['proprietario_id']) {
                    return false;
                }
                if ($filters['struttura_id'] && (int) $assegnazione->struttura_id !== (int) $filters['struttura_id']) {
                    return false;
                }

                return true;
            })
            ->map(function (LicenzaAssegnazione $assegnazione) {
                return [
                    'tipo' => $assegnazione->struttura_id ? 'Licenza struttura' : 'Licenza da riallineare',
                    'data' => $assegnazione->data_inizio,
                    'admin' => $assegnazione->admin?->name,
                    'proprietario' => $assegnazione->proprietario?->nome,
                    'struttura' => $assegnazione->struttura?->nome_struttura,
                    'descrizione' => $assegnazione->articolo?->nome ?: 'Licenza',
                    'documento' => $assegnazione->numero_licenza ?: '—',
                    'stato' => $assegnazione->stato_pagamento,
                    'scadenza' => $assegnazione->data_scadenza,
                    'totale' => (float) $assegnazione->prezzo,
                    'tracking' => $assegnazione->codice_tracking,
                ];
            });

        $proforme = ProprietarioFatturazione::with(['proprietario.admin', 'righe.struttura'])
            ->when($filters['proprietario_id'], fn ($query, $proprietarioId) => $query->where('proprietario_id', $proprietarioId))
            ->when($filters['admin_id'], function ($query, $adminId) {
                $query->whereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId));
            })
            ->get()
            ->filter(function (ProprietarioFatturazione $fatturazione) use ($filters) {
                if (!$filters['struttura_id']) {
                    return true;
                }

                return $fatturazione->righe->contains(fn ($riga) => (int) $riga->struttura_id === (int) $filters['struttura_id']);
            })
            ->map(function (ProprietarioFatturazione $fatturazione) {
                $strutture = $fatturazione->righe->pluck('struttura.nome_struttura')->filter()->unique()->values();

                return [
                    'tipo' => 'Proforma proprietario',
                    'data' => $fatturazione->data_documento,
                    'admin' => $fatturazione->proprietario?->admin?->name,
                    'proprietario' => $fatturazione->proprietario?->nome,
                    'struttura' => $strutture->isNotEmpty() ? $strutture->join(', ') : 'Servizi generali',
                    'descrizione' => 'Documento proprietario',
                    'documento' => $fatturazione->numero,
                    'stato' => $fatturazione->stato,
                    'scadenza' => null,
                    'totale' => (float) $fatturazione->totale,
                    'tracking' => null,
                ];
            });

        $righe = $licenze->concat($proforme)->sortByDesc(function ($row) {
            return optional($row['data'])->timestamp ?: 0;
        })->values();

        return [
            'righe' => $righe,
            'totale' => $righe->sum('totale'),
            'licenze' => $licenze->sum('totale'),
            'proforme' => $proforme->sum('totale'),
        ];
    }

    private function buildAdminProformeConto(array $filters)
    {
        return AdminFatturazione::with('amministratore')
            ->when($filters['admin_id'], fn ($query, $adminId) => $query->where('user_id', $adminId))
            ->orderByDesc('data_documento')
            ->orderByDesc('id')
            ->get();
    }

    public function storeArticolo(Request $request)
    {
        $data = $this->validateArticolo($request);

        LicenzaArticolo::create($data);

        return redirect()->route('superadmin.pagamenti.index', ['tab' => 'articoli'])->with('status', 'Articolo licenza creato.');
    }

    public function updateArticolo(Request $request, int $id)
    {
        $articolo = LicenzaArticolo::findOrFail($id);
        $data = $this->validateArticolo($request, $articolo->id);
        $articolo->update($data);

        return redirect()->route('superadmin.pagamenti.index', ['tab' => 'articoli'])->with('status', 'Articolo licenza aggiornato.');
    }

    public function storeAssegnazione(Request $request)
    {
        $data = $this->validateAssegnazione($request);

        $assegnazione = LicenzaAssegnazione::create($this->buildAssegnazionePayload($data));
        $this->syncStrutturaCommercialState($assegnazione->struttura_id);

        return $this->redirectAfterLicenza($request, 'Licenza assegnata correttamente.');
    }

    public function updateAssegnazione(Request $request, int $id)
    {
        $assegnazione = LicenzaAssegnazione::findOrFail($id);
        $oldStrutturaId = $assegnazione->struttura_id;
        $data = $this->validateAssegnazione($request, $assegnazione->id);
        $assegnazione->update($this->buildAssegnazionePayload(array_merge($data, [
            'numero_licenza' => $assegnazione->numero_licenza ?: $this->nextLicenzaNumber(),
        ])));
        $this->syncStrutturaCommercialState($oldStrutturaId);
        $this->syncStrutturaCommercialState($assegnazione->struttura_id);

        return $this->redirectAfterLicenza($request, 'Licenza aggiornata correttamente.');
    }

    public function destroyAssegnazione(Request $request, int $id)
    {
        $assegnazione = LicenzaAssegnazione::findOrFail($id);
        $strutturaId = $assegnazione->struttura_id;
        $assegnazione->delete();
        $this->syncStrutturaCommercialState($strutturaId);

        return $this->redirectAfterLicenza($request, 'Licenza eliminata correttamente.');
    }

    public function printAssegnazione(int $id)
    {
        $assegnazione = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])->findOrFail($id);

        return view('superadmin.pagamenti.licenza-print', [
            'assegnazione' => $assegnazione,
        ]);
    }

    private function validateArticolo(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:licenza_articoli,id'],
            'nome' => ['required', 'string', 'max:160'],
            'codice' => ['nullable', 'string', 'max:80', Rule::unique('licenza_articoli', 'codice')->ignore($ignoreId)],
            'accesso_key' => ['nullable', 'string', 'max:120'],
            'descrizione' => ['nullable', 'string'],
            'prezzo_base' => ['required', 'numeric', 'min:0'],
            'attivo' => ['nullable', 'boolean'],
            'ordine' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'note' => ['nullable', 'string'],
        ]);
    }

    private function validateAssegnazione(Request $request, ?int $ignoreAssegnazioneId = null): array
    {
        $validator = Validator::make($request->all(), [
            'articolo_id' => ['required', 'integer', 'exists:licenza_articoli,id'],
            'proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'struttura_id' => ['required', 'integer', 'exists:struttura,id'],
            'admin_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('ruolo', 'admin'))],
            'quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'prezzo' => ['required', 'numeric', 'min:0'],
            'stato_pagamento' => ['required', 'string', 'max:40'],
            'data_inizio' => ['nullable', 'date'],
            'data_scadenza' => ['nullable', 'date'],
            'attiva' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request, $ignoreAssegnazioneId) {
            $struttura = $request->filled('struttura_id') ? Struttura::with('proprietario')->find($request->integer('struttura_id')) : null;
            $articolo = $request->filled('articolo_id') ? LicenzaArticolo::find($request->integer('articolo_id')) : null;
            $proprietarioId = $request->integer('proprietario_id');
            $attiva = filter_var($request->input('attiva', true), FILTER_VALIDATE_BOOLEAN);

            if (!$struttura) {
                $validator->errors()->add('struttura_id', 'Seleziona una struttura valida per la licenza.');
                return;
            }

            if ($proprietarioId && (int) $struttura->proprietario_id !== $proprietarioId) {
                $validator->errors()->add('proprietario_id', 'Il proprietario deve coincidere con quello della struttura selezionata.');
            }

            if ($articolo && $attiva && $articolo->parent_id === null) {
                $exists = LicenzaAssegnazione::query()
                    ->where('struttura_id', $struttura->id)
                    ->where('attiva', true)
                    ->whereHas('articolo', fn ($query) => $query->whereNull('parent_id'))
                    ->when($ignoreAssegnazioneId, fn ($query) => $query->where('id', '!=', $ignoreAssegnazioneId))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('articolo_id', 'Questa struttura ha già una licenza principale attiva. Disattiva o sostituisci quella esistente prima di assegnarne un altra.');
                }
            }
        });

        return $validator->validate();
    }

    private function buildAssegnazionePayload(array $data): array
    {
        $articolo = LicenzaArticolo::findOrFail($data['articolo_id']);
        $struttura = !empty($data['struttura_id']) ? Struttura::find($data['struttura_id']) : null;
        $proprietarioId = $data['proprietario_id'] ?? null;

        if ($struttura && $struttura->proprietario_id) {
            $proprietarioId = $struttura->proprietario_id;
        }

        if ($proprietarioId && empty($data['admin_id'])) {
            $proprietario = Proprietario::find($proprietarioId);
            $data['admin_id'] = $proprietario?->admin_id;
        }

        $prezzo = (float) ($data['prezzo'] ?? 0);
        if ($prezzo <= 0) {
            $prezzo = (float) ($articolo->prezzo_base ?? 0);
        }

        return [
            'numero_licenza' => $data['numero_licenza'] ?? $this->nextLicenzaNumber(),
            'articolo_id' => $data['articolo_id'],
            'proprietario_id' => $proprietarioId,
            'struttura_id' => $data['struttura_id'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
            'quantita' => $data['quantita'] ?? 1,
            'prezzo' => $prezzo,
            'stato_pagamento' => $data['stato_pagamento'],
            'data_inizio' => $data['data_inizio'] ?? null,
            'data_scadenza' => $data['data_scadenza'] ?? null,
            'attiva' => $data['attiva'] ?? true,
            'note' => $data['note'] ?? null,
        ];
    }

    private function nextLicenzaNumber(): string
    {
        $latest = LicenzaAssegnazione::query()
            ->whereNotNull('numero_licenza')
            ->where('numero_licenza', 'like', 'LIC-%')
            ->orderByDesc('numero_licenza')
            ->pluck('numero_licenza')
            ->first();

        $next = 1;
        if ($latest && preg_match('/LIC-(\d+)/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = 'LIC-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $exists = LicenzaAssegnazione::where('numero_licenza', $candidate)->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }

    private function redirectAfterLicenza(Request $request, string $status)
    {
        $structureId = $request->integer('return_to_structure_id');
        $activeTab = $request->input('active_tab', 'licenze');

        if ($structureId > 0) {
            return redirect()
                ->route('superadmin.strutture.edit', ['id' => $structureId, 'tab' => $activeTab])
                ->with('status', $status);
        }

        return redirect()->route('superadmin.pagamenti.index', ['tab' => 'licenze'])->with('status', $status);
    }

    private function syncStrutturaCommercialState(?int $strutturaId): void
    {
        if (!$strutturaId) {
            return;
        }

        $struttura = Struttura::find($strutturaId);
        if (!$struttura) {
            return;
        }

        $primaryLicenza = LicenzaAssegnazione::query()
            ->with('articolo')
            ->where('struttura_id', $strutturaId)
            ->where('attiva', true)
            ->whereHas('articolo', fn ($query) => $query->whereNull('parent_id'))
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->first();

        if (!$primaryLicenza) {
            $struttura->forceFill([
                'scadenza_servizio' => null,
                'stato_pagamento' => 'da_pagare',
                'piano' => null,
                'attiva' => false,
            ])->save();

            return;
        }

        $isOperativa = (bool) $primaryLicenza->attiva
            && (!$primaryLicenza->data_scadenza || !$primaryLicenza->data_scadenza->isPast());

        $struttura->forceFill([
            'scadenza_servizio' => $primaryLicenza->data_scadenza,
            'stato_pagamento' => $primaryLicenza->stato_pagamento,
            'piano' => $primaryLicenza->articolo?->nome ?: $struttura->piano,
            'attiva' => $isOperativa,
        ])->save();
    }
}
