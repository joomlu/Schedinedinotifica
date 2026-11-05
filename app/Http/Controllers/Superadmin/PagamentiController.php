<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
use App\Models\Struttura;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $cityColumn = $this->resolveStrutturaCityColumn();

        $struttureQuery = Struttura::with(['proprietario.admin'])->orderBy('nome_struttura');

        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $struttureQuery->where(function ($subquery) use ($term, $cityColumn) {
                $subquery->where('nome_struttura', 'like', '%' . $term . '%')
                    ->orWhere($cityColumn, 'like', '%' . $term . '%')
                    ->orWhere('provincia', 'like', '%' . $term . '%')
                    ->orWhere('piano', 'like', '%' . $term . '%')
                    ->orWhere('stato_pagamento', 'like', '%' . $term . '%')
                    ->orWhereHas('proprietario', function ($ownerQuery) use ($term) {
                        $ownerQuery->where('nome', 'like', '%' . $term . '%')
                            ->orWhere('ragione_sociale', 'like', '%' . $term . '%')
                            ->orWhereHas('admin', fn ($adminQuery) => $adminQuery->where('name', 'like', '%' . $term . '%')->orWhere('ragione_sociale', 'like', '%' . $term . '%'));
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

        if ($contoFilters['admin_id']) {
            $struttureQuery->whereHas('proprietario', fn ($query) => $query->where('admin_id', $contoFilters['admin_id']));
        }
        if ($contoFilters['proprietario_id']) {
            $struttureQuery->where('proprietario_id', $contoFilters['proprietario_id']);
        }
        if ($contoFilters['struttura_id']) {
            $struttureQuery->whereKey($contoFilters['struttura_id']);
        }

        $strutture = $struttureQuery->get();

        $statiPagamento = Struttura::query()
            ->whereNotNull('stato_pagamento')
            ->where('stato_pagamento', '!=', '')
            ->distinct()
            ->orderBy('stato_pagamento')
            ->pluck('stato_pagamento');

        $articoli = LicenzaArticolo::with('parent')->where('attivo', true)->orderBy('ordine')->orderBy('nome')->get();

        $assegnazioni = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])
            ->when($filters['q'] !== '', function ($query) use ($filters, $cityColumn) {
                $term = $filters['q'];
                $query->where(function ($subquery) use ($term, $cityColumn) {
                    $subquery->where('numero_licenza', 'like', '%' . $term . '%')
                        ->orWhere('stato_pagamento', 'like', '%' . $term . '%')
                        ->orWhereHas('articolo', fn ($itemQuery) => $itemQuery->where('nome', 'like', '%' . $term . '%')->orWhere('codice', 'like', '%' . $term . '%'))
                        ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('nome', 'like', '%' . $term . '%')->orWhere('ragione_sociale', 'like', '%' . $term . '%'))
                        ->orWhereHas('struttura', fn ($structureQuery) => $structureQuery->where('nome_struttura', 'like', '%' . $term . '%')->orWhere($cityColumn, 'like', '%' . $term . '%'));
                });
            })
            ->when($filters['attiva'] !== '', fn ($query) => $query->where('attiva', $filters['attiva'] === '1'))
            ->when($filters['stato_pagamento'] !== '', fn ($query) => $query->where('stato_pagamento', $filters['stato_pagamento']))
            ->when($filters['scadenza'] === 'scadute', fn ($query) => $query->whereDate('data_scadenza', '<', now()->toDateString()))
            ->when($filters['scadenza'] === 'entro_30', fn ($query) => $query->whereBetween('data_scadenza', [now()->toDateString(), now()->addDays(30)->toDateString()]))
            ->when($filters['scadenza'] === 'senza_data', fn ($query) => $query->whereNull('data_scadenza'))
            ->when($contoFilters['admin_id'], function ($query, $adminId) {
                $query->where(function ($subquery) use ($adminId) {
                    $subquery->where('admin_id', $adminId)
                        ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId))
                        ->orWhereHas('struttura.proprietario', fn ($ownerQuery) => $ownerQuery->where('admin_id', $adminId));
                });
            })
            ->when($contoFilters['proprietario_id'], fn ($query, $proprietarioId) => $query->where('proprietario_id', $proprietarioId))
            ->when($contoFilters['struttura_id'], fn ($query, $strutturaId) => $query->where('struttura_id', $strutturaId))
            ->orderByDesc('attiva')
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->get();

        $proprietari = Proprietario::with('admin')->orderBy('nome')->get();
        $struttureDisponibili = Struttura::with('proprietario')->orderBy('nome_struttura')->get();
        $admins = User::query()->whereIn('ruolo', ['admin', 'admin_disabled'])->orderBy('name')->get();

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
            'admins' => $admins,
            'canManageArticoli' => false,
            'showArticoliCatalogo' => true,
            'pagamentiBaseRoute' => 'superadmin.pagamenti',
            'servizioRoutePrefix' => 'superadmin.strutture',
            'strutturaEditRoute' => 'superadmin.strutture.edit',
            'contoFilters' => $contoFilters,
            'statoConto' => $this->buildStatoConto($assegnazioni, array_merge($contoFilters, $filters)),
            'adminProformeConto' => collect(),
        ]);
    }

    public function printAssegnazione(int $id)
    {
        $assegnazione = LicenzaAssegnazione::with(['articolo.parent', 'proprietario.admin', 'struttura'])->findOrFail($id);

        return view('superadmin.pagamenti.licenza-print', [
            'assegnazione' => $assegnazione,
        ]);
    }

    private function buildStatoConto($assegnazioni, array $filters): array
    {
        $licenze = collect($assegnazioni)
            ->filter(function (LicenzaAssegnazione $assegnazione) use ($filters) {
                if (($filters['admin_id'] ?? null) && (int) ($assegnazione->proprietario?->admin_id ?? $assegnazione->admin_id) !== (int) $filters['admin_id']) {
                    return false;
                }
                if (($filters['proprietario_id'] ?? null) && (int) $assegnazione->proprietario_id !== (int) $filters['proprietario_id']) {
                    return false;
                }
                if (($filters['struttura_id'] ?? null) && (int) $assegnazione->struttura_id !== (int) $filters['struttura_id']) {
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
                    'admin' => $assegnazione->admin?->name ?? $assegnazione->proprietario?->admin?->name,
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
            ->when($filters['admin_id'] ?? null, fn ($query, $adminId) => $query->whereHas('proprietario', fn ($subquery) => $subquery->where('admin_id', $adminId)))
            ->when($filters['proprietario_id'] ?? null, fn ($query, $proprietarioId) => $query->where('proprietario_id', $proprietarioId))
            ->get()
            ->filter(function (ProprietarioFatturazione $fatturazione) use ($filters) {
                if (($filters['struttura_id'] ?? null) && !$fatturazione->righe->contains(fn ($riga) => (int) $riga->struttura_id === (int) $filters['struttura_id'])) {
                    return false;
                }
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
                $row['admin'] ?? '',
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

    private function resolveStrutturaCityColumn(): string
    {
        if (Schema::hasColumn('struttura', 'citta')) {
            return 'citta';
        }

        if (Schema::hasColumn('struttura', 'città')) {
            return 'città';
        }

        return 'citta';
    }
}
