@extends('layouts.master')
@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('title') Strutture @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Strutture dei miei proprietari</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('strutture.seleziona.index') }}" class="btn btn-light">Seleziona struttura</a>
            <a href="{{ route('admin.strutture.create') }}" class="btn btn-primary">Nuova struttura</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="card-title mb-2">Perimetro admin</h6>
            <p class="text-muted mb-0">Qui l admin vede solo le strutture appartenenti ai propri proprietari. Può modificarle, aggiornarne il servizio e selezionare quale struttura usare nei moduli operativi.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Strutture</div>
                    <div class="fw-bold fs-4">{{ $strutture->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Attive</div>
                    <div class="fw-bold fs-4 text-success">{{ $strutture->where('attiva', true)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">In scadenza</div>
                    <div class="fw-bold fs-4 text-warning">{{ $strutture->filter(fn($s) => $s->scadenza_servizio && $s->scadenza_servizio->between(now()->startOfDay(), now()->copy()->addDays(30)->endOfDay()))->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Scadute</div>
                    <div class="fw-bold fs-4 text-danger">{{ $strutture->filter(fn($s) => $s->scadenza_servizio && $s->scadenza_servizio->isPast())->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Città</th>
                            <th>Provincia</th>
                            <th>Proprietario</th>
                            <th>Attiva</th>
                            <th>Scadenza</th>
                            <th>Piano</th>
                            <th>Pagamento</th>
                            <th>Corrente</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($strutture as $struttura)
                            <tr ondblclick="window.location.href='{{ route('admin.strutture.edit', $struttura->id) }}'" style="cursor:pointer;">
                                <td>
                                    <div class="fw-semibold">{{ $struttura->nome_struttura }}</div>
                                    <div class="small text-muted">ID {{ $struttura->id }}</div>
                                </td>
                                <td>{{ $struttura->citta }}</td>
                                <td>{{ $struttura->provincia }}</td>
                                <td>{{ optional($struttura->proprietario)->nome }}</td>
                                <td>
                                    <span class="badge {{ $struttura->attiva ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $struttura->attiva ? 'Attiva' : 'Offline' }}
                                    </span>
                                </td>
                                <td>
                                    @if($struttura->scadenza_servizio)
                                        <span class="badge {{ $struttura->scadenza_servizio->isPast() ? 'bg-danger-subtle text-danger' : ($struttura->scadenza_servizio->diffInDays(now()) <= 30 ? 'bg-warning-subtle text-warning' : 'bg-light text-body') }}">
                                            {{ $struttura->scadenza_servizio->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $struttura->piano }}</td>
                                <td>
                                    <span class="badge bg-light text-body">{{ $struttura->stato_pagamento ?: '—' }}</span>
                                </td>
                                <td>{!! session('struttura_corrente_id') == $struttura->id ? '<span class="badge bg-primary-subtle text-primary">Selezionata</span>' : '<span class="text-muted">—</span>' !!}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('strutture.seleziona', $struttura->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-success btn-sm" title="Seleziona struttura">
                                            <i class="ri-login-box-line"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.strutture.edit', $struttura->id) }}" class="btn btn-soft-secondary btn-sm" title="Accedi struttura">
                                        <i class="ri-external-link-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
