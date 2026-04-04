@extends('layouts.master')
@section('title') {{ $pageTitle ?? 'Proforme' }} @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel ?? 'Admin' }} @endslot
        @slot('title') {{ $pageTitle ?? 'Proforme' }} @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $pageTitle ?? 'Proforme' }}</h4>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}" class="row g-3 align-items-end">
                <div class="col-lg-10">
                    <label class="form-label">Ricerca rapida</label>
                    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Numero proforma, proprietario, struttura, intestazione...">
                </div>
                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary">Cerca</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($proforme->isEmpty())
                <div class="text-muted">Nessuna proforma trovata.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Data</th>
                                <th>Stato</th>
                                <th>Proprietario</th>
                                <th>Strutture</th>
                                <th>Intestazione</th>
                                <th>Totale</th>
                                <th class="text-end">Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proforme as $proforma)
                                @php
                                    $strutture = $proforma->righe->pluck('struttura.nome_struttura')->filter()->unique()->values();
                                    $statusLabel = in_array(($proforma->stato ?? ''), ['pagata', 'fatturata', 'ok'], true) ? 'Pagata' : ucfirst($proforma->stato ?? 'Proforma');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $proforma->numero ?: '—' }}</td>
                                    <td>{{ optional($proforma->data_documento)->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $statusLabel }}</td>
                                    <td>{{ $proforma->proprietario?->nome ?: '—' }}</td>
                                    <td>{{ $strutture->isNotEmpty() ? $strutture->join(', ') : 'Servizi generali' }}</td>
                                    <td>
                                        <div>{{ $proforma->intestazione ?: ($proforma->proprietario->ragione_sociale ?? $proforma->proprietario->nome ?? '—') }}</div>
                                        @if($statusLabel === 'Pagata' && ($proforma->data_pagamento || $proforma->numero_fattura))
                                            <div class="small text-muted">
                                                {{ optional($proforma->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                                                @if($proforma->numero_fattura)
                                                    · Fatt. {{ $proforma->numero_fattura }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ number_format((float) $proforma->totale, 2, ',', '.') }} €</td>
                                    <td class="text-end">
                                        @if($proforma->proprietario_id)
                                            <a href="{{ route($ownerRoutePrefix . '.proforme.show', ['id' => $proforma->proprietario_id, 'fatturazione' => $proforma->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                Apri
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
