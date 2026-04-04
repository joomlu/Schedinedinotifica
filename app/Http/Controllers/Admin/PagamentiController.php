<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
use App\Models\Struttura;
use App\Services\CestinoService;
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
            'admin_id' => (int) $request->user()->id,
            'proprietario_id' => $request->integer('conto_proprietario_id') ?: null,
            'struttura_id' => $request->integer('conto_struttura_id') ?: null,
        ];

        $adminId = (int) $request->user()->id;
        $struttureQuery = Struttura::with(['proprietario.admin'])
            ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $adminId))
            ->orderBy('nome_struttura');

        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $struttureQuery->where(function ($subquery) use ($term) {
                $subquery->where('nome_struttura', 'like', '%' . $term . '%')
                    ->orWhere('citta', 'like', '%' . $term . '%')
                    ->orWhere('provincia', 'like', '%' . $term . '%')
                    ->orWhere('piano', 'like', '%' . $term . '%')
                    ->orWhere('stato_pagamento', 'like', '%' . $term . '%')
                    ->orWhereHas('proprietario', function ($ownerQuery) use ($term) {
                        $ownerQuery->where('nome', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($filters['attiva'] !== '') {
            $struttureQuery->where('attiva', $filters['attiva'] === '1');
        }

        if ($filters['stato_pagamento'] !== '') {
            $struttureQuery->where('stato_pagamento', $filters['stato_pagamento']);
        }

        if ($filters['scadenza'] === 'scadute') {
            $struttureQuery->whereDate('scadenza_servizio', '<', now()->toDateString());
        } elseif ($filters['scadenza'] === 'entro_30') {
            $struttureQuery->whereBetween('scadenza_servizio', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        } elseif ($filters['scadenza'] === 'senza_data') {
            $struttureQuery->whereNull('scadenza_servizio');
        }

        $strutture = $struttureQuery->get();
        $statiPagamento = Struttura::query()
            ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $adminId))
            ->whereNotNull('stato_pagamento')
            ->where('stato_pagamento', '!=', '')
            ->distinct()
            ->orderBy('stato_pagamento')
            ->pluck('stato_pagamento');

        $articoli = LicenzaArticolo::with('parent')->where('attivo', true)->orderBy('ordine')->orderBy('nome')->get();
        $assegnazioni = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])
            ->where(function ($query) use ($adminId) {
                $query->where('admin_id', $adminId)
                    ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId))
                    ->orWhereHas('struttura.proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId));
            })
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(function ($subquery) use ($term) {
                    $subquery->where('numero_licenza', 'like', '%' . $term . '%')
                        ->orWhere('stato_pagamento', 'like', '%' . $term . '%')
                        ->orWhereHas('articolo', fn ($itemQuery) => $itemQuery->where('nome', 'like', '%' . $term . '%')->orWhere('codice', 'like', '%' . $term . '%'))
                        ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('nome', 'like', '%' . $term . '%')->orWhere('ragione_sociale', 'like', '%' . $term . '%'))
                        ->orWhereHas('struttura', fn ($structureQuery) => $structureQuery->where('nome_struttura', 'like', '%' . $term . '%')->orWhere('citta', 'like', '%' . $term . '%'));
                });
            })
            ->when($filters['attiva'] !== '', fn ($query) => $query->where('attiva', $filters['attiva'] === '1'))
            ->when($filters['stato_pagamento'] !== '', fn ($query) => $query->where('stato_pagamento', $filters['stato_pagamento']))
            ->when($filters['scadenza'] === 'scadute', fn ($query) => $query->whereDate('data_scadenza', '<', now()->toDateString()))
            ->when($filters['scadenza'] === 'entro_30', fn ($query) => $query->whereBetween('data_scadenza', [now()->toDateString(), now()->addDays(30)->toDateString()]))
            ->when($filters['scadenza'] === 'senza_data', fn ($query) => $query->whereNull('data_scadenza'))
            ->orderByDesc('attiva')
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->get();

        $proprietari = Proprietario::with('admin')->where('admin_id', $adminId)->orderBy('nome')->get();
        $struttureDisponibili = Struttura::with('proprietario')
            ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $adminId))
            ->orderBy('nome_struttura')
            ->get();

        $today = now()->startOfDay();
        $summary = [
            'totale' => $strutture->count(),
            'attive' => $strutture->where('attiva', true)->count(),
            'scadute' => $strutture->filter(fn (Struttura $struttura) => $struttura->scadenza_servizio && $struttura->scadenza_servizio->lt($today))->count(),
            'entro_30' => $strutture->filter(fn (Struttura $struttura) => $struttura->scadenza_servizio && $struttura->scadenza_servizio->between($today, (clone $today)->addDays(30)))->count(),
            'senza_data' => $strutture->whereNull('scadenza_servizio')->count(),
            'articoli_attivi' => $articoli->count(),
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
            'admins' => collect([$request->user()]),
            'canManageArticoli' => false,
            'showArticoliCatalogo' => true,
            'pagamentiBaseRoute' => 'admin.pagamenti',
            'servizioRoutePrefix' => 'admin.strutture',
            'strutturaEditRoute' => 'admin.strutture.edit',
            'contoFilters' => $contoFilters,
            'statoConto' => $this->buildStatoConto($assegnazioni, array_merge($contoFilters, $filters)),
            'adminProformeConto' => collect(),
        ]);
    }

    private function buildStatoConto($assegnazioni, array $filters): array
    {
        $licenze = collect($assegnazioni)
            ->filter(function (LicenzaAssegnazione $assegnazione) use ($filters) {
                if ($filters['proprietario_id'] && (int) $assegnazione->proprietario_id !== (int) $filters['proprietario_id']) {
                    return false;
                }
                if ($filters['struttura_id'] && (int) $assegnazione->struttura_id !== (int) $filters['struttura_id']) {
                    return false;
                }
                if (($filters['attiva'] ?? '') !== '' && (bool) $assegnazione->attiva !== (($filters['attiva'] ?? '') === '1')) {
                    return false;
                }
                if (($filters['stato_pagamento'] ?? '') !== '' && (string) $assegnazione->stato_pagamento !== (string) $filters['stato_pagamento']) {
                    return false;
                }
                if (!$this->matchesScadenzaFilter($assegnazione->data_scadenza, (string) ($filters['scadenza'] ?? ''))) {
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
                    'licenza_id' => $assegnazione->id,
                    'proforma_id' => null,
                    'proprietario_id' => $assegnazione->proprietario_id,
                ];
            });

        $proforme = ProprietarioFatturazione::with(['proprietario.admin', 'righe.struttura'])
            ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $filters['admin_id']))
            ->when($filters['proprietario_id'], fn ($query, $proprietarioId) => $query->where('proprietario_id', $proprietarioId))
            ->get()
            ->filter(function (ProprietarioFatturazione $fatturazione) use ($filters) {
                if (!$filters['struttura_id']) {
                    return true;
                }

                if (!$fatturazione->righe->contains(fn ($riga) => (int) $riga->struttura_id === (int) $filters['struttura_id'])) {
                    return false;
                }

                return true;
            })
            ->filter(function (ProprietarioFatturazione $fatturazione) use ($filters) {
                if (!$this->matchesProformaStatoFilter($fatturazione, (string) ($filters['stato_pagamento'] ?? ''))) {
                    return false;
                }
                if (($filters['attiva'] ?? '') !== '' && !$this->matchesProformaAttivaFilter($fatturazione, ($filters['attiva'] ?? '') === '1')) {
                    return false;
                }
                if (!$this->matchesProformaScadenzaFilter($fatturazione, (string) ($filters['scadenza'] ?? ''))) {
                    return false;
                }

                return true;
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
                    'licenza_id' => null,
                    'proforma_id' => $fatturazione->id,
                    'proprietario_id' => $fatturazione->proprietario_id,
                ];
            });

        $righe = $licenze->concat($proforme)->filter(function (array $row) use ($filters) {
            $term = trim((string) ($filters['q'] ?? ''));
            if ($term === '') {
                return true;
            }

            $haystack = mb_strtolower(implode(' ', array_filter([
                $row['tipo'] ?? '',
                $row['proprietario'] ?? '',
                $row['struttura'] ?? '',
                $row['descrizione'] ?? '',
                $row['documento'] ?? '',
                $row['stato'] ?? '',
            ])));

            return str_contains($haystack, mb_strtolower($term));
        })->sortByDesc(function ($row) {
            return optional($row['data'])->timestamp ?: 0;
        })->values();

        return [
            'righe' => $righe,
            'totale' => $righe->sum('totale'),
            'licenze' => $licenze->sum('totale'),
            'proforme' => $proforme->sum('totale'),
        ];
    }

    private function matchesScadenzaFilter($date, string $filter): bool
    {
        if ($filter === '') {
            return true;
        }

        if (blank($date)) {
            return $filter === 'senza_data';
        }

        $today = now()->startOfDay();
        $value = $date instanceof \Illuminate\Support\Carbon
            ? $date->copy()->startOfDay()
            : \Illuminate\Support\Carbon::parse($date)->startOfDay();

        return match ($filter) {
            'scadute' => $value->lt($today),
            'entro_30' => $value->between($today, $today->copy()->addDays(30)),
            'senza_data' => false,
            default => true,
        };
    }

    private function matchesProformaStatoFilter(ProprietarioFatturazione $fatturazione, string $filter): bool
    {
        if ($filter === '') {
            return true;
        }

        $isPaid = in_array($fatturazione->stato, ['pagata', 'fatturata'], true);

        return match ($filter) {
            'ok', 'pagato' => $isPaid,
            'da_pagare' => !$isPaid,
            'sospeso' => false,
            default => true,
        };
    }

    private function matchesProformaAttivaFilter(ProprietarioFatturazione $fatturazione, bool $expected): bool
    {
        $strutture = $fatturazione->righe->pluck('struttura')->filter();

        if ($strutture->isEmpty()) {
            return true;
        }

        return $strutture->contains(fn ($struttura) => (bool) $struttura->attiva === $expected);
    }

    private function matchesProformaScadenzaFilter(ProprietarioFatturazione $fatturazione, string $filter): bool
    {
        if ($filter === '') {
            return true;
        }

        $strutture = $fatturazione->righe->pluck('struttura')->filter();

        if ($strutture->isEmpty()) {
            return $filter === 'senza_data';
        }

        return $strutture->contains(fn ($struttura) => $this->matchesScadenzaFilter($struttura->scadenza_servizio, $filter));
    }

    public function storeAssegnazione(Request $request)
    {
        $data = $this->validateAssegnazione($request);
        $assegnazione = LicenzaAssegnazione::create($this->buildPayload($request, $data));
        $this->syncStrutturaCommercialState($request, $assegnazione->struttura_id);

        return $this->redirectAfterLicenza($request, 'Licenza assegnata correttamente.');
    }

    public function updateAssegnazione(Request $request, int $id)
    {
        $assegnazione = $this->findOwnedAssegnazione($request, $id);
        $oldStrutturaId = $assegnazione->struttura_id;
        $data = $this->validateAssegnazione($request, $assegnazione->id);
        $assegnazione->update($this->buildPayload($request, array_merge($data, [
            'numero_licenza' => $assegnazione->numero_licenza,
        ])));
        $this->syncStrutturaCommercialState($request, $oldStrutturaId);
        $this->syncStrutturaCommercialState($request, $assegnazione->struttura_id);

        return $this->redirectAfterLicenza($request, 'Licenza aggiornata correttamente.');
    }

    public function destroyAssegnazione(Request $request, int $id)
    {
        $assegnazione = $this->findOwnedAssegnazione($request, $id);
        $strutturaId = $assegnazione->struttura_id;
        app(CestinoService::class)->archiveModel($assegnazione, [
            'source' => 'Licenze',
        ]);
        $assegnazione->delete();
        $this->syncStrutturaCommercialState($request, $strutturaId);

        return $this->redirectAfterLicenza($request, 'Licenza spostata nel cestino.');
    }

    public function printAssegnazione(Request $request, int $id)
    {
        $assegnazione = $this->findOwnedAssegnazione($request, $id);

        return view('superadmin.pagamenti.licenza-print', [
            'assegnazione' => $assegnazione,
        ]);
    }

    private function validateAssegnazione(Request $request, ?int $ignoreAssegnazioneId = null): array
    {
        $adminId = (int) $request->user()->id;
        $proprietariIds = Proprietario::where('admin_id', $adminId)->pluck('id')->all();
        $struttureIds = Struttura::whereHas('proprietario', fn ($query) => $query->where('admin_id', $adminId))->pluck('id')->all();

        $validator = Validator::make($request->all(), [
            'articolo_id' => ['required', 'integer', 'exists:licenza_articoli,id'],
            'proprietario_id' => ['nullable', Rule::in($proprietariIds)],
            'struttura_id' => ['required', Rule::in($struttureIds)],
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

    private function buildPayload(Request $request, array $data): array
    {
        $articolo = LicenzaArticolo::findOrFail($data['articolo_id']);
        $struttura = !empty($data['struttura_id']) ? Struttura::find($data['struttura_id']) : null;
        $proprietarioId = $data['proprietario_id'] ?? null;

        if ($struttura && $struttura->proprietario_id) {
            $proprietarioId = $struttura->proprietario_id;
        }

        $prezzo = (float) ($data['prezzo'] ?? 0);
        if ($prezzo <= 0) {
            $prezzo = (float) ($articolo->prezzo_base ?? 0);
        }

        return [
            'numero_licenza' => $data['numero_licenza'] ?? $this->nextLicenzaNumber(),
            'articolo_id' => $articolo->id,
            'proprietario_id' => $proprietarioId,
            'struttura_id' => $data['struttura_id'] ?? null,
            'admin_id' => (int) $request->user()->id,
            'quantita' => $data['quantita'] ?? 1,
            'prezzo' => $prezzo,
            'stato_pagamento' => $data['stato_pagamento'],
            'data_inizio' => $data['data_inizio'] ?? null,
            'data_scadenza' => $data['data_scadenza'] ?? null,
            'attiva' => $data['attiva'] ?? true,
            'note' => $data['note'] ?? null,
        ];
    }

    private function findOwnedAssegnazione(Request $request, int $id): LicenzaAssegnazione
    {
        $adminId = (int) $request->user()->id;

        return LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])
            ->where(function ($query) use ($adminId) {
                $query->where('admin_id', $adminId)
                    ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId))
                    ->orWhereHas('struttura.proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId));
            })
            ->findOrFail($id);
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
                ->route('admin.strutture.edit', ['id' => $structureId, 'tab' => $activeTab])
                ->with('status', $status);
        }

        return redirect()->route('admin.pagamenti.index', ['tab' => 'licenze'])->with('status', $status);
    }

    private function syncStrutturaCommercialState(Request $request, ?int $strutturaId): void
    {
        if (!$strutturaId) {
            return;
        }

        $struttura = Struttura::whereHas('proprietario', fn ($query) => $query->where('admin_id', $request->user()->id))
            ->find($strutturaId);

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
