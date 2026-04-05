@extends('layouts.master')
@section('title') Amministratori @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Amministratori @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Amministratori</h4>
        <a href="{{ route('superadmin.amministratori.create') }}" class="btn btn-primary">Nuovo amministratore</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Amministratori</div>
                    <div class="fw-semibold fs-4">{{ $summary['totale_admin'] ?? 0 }}</div>
                    <div class="small text-muted">{{ $summary['attivi'] ?? 0 }} attivi · {{ $summary['disattivi'] ?? 0 }} disattivi</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Proprietari gestiti</div>
                    <div class="fw-semibold fs-4">{{ $summary['proprietari'] ?? 0 }}</div>
                    <div class="small text-muted">Collegati agli amministratori censiti.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Servizi</div>
                    <div class="fw-semibold fs-4">{{ $summary['servizi'] ?? 0 }}</div>
                    <div class="small text-muted">Servizi commerciali fatturati agli admin.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Proforme / fatture</div>
                    <div class="fw-semibold fs-4">{{ $summary['fatture'] ?? 0 }}</div>
                    <div class="small text-muted">Documenti economici legati agli amministratori.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.amministratori.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-10">
                    <label class="form-label">Ricerca rapida superadmin</label>
                    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Admin, email, username, ragione sociale, P.IVA...">
                </div>
                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary">Cerca</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Proprietari</th>
                            <th>Strutture</th>
                            <th>Servizi</th>
                            <th>Fatture</th>
                            <th>Stato</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->username ?: '—' }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->proprietari_count ?? 0 }}</td>
                                <td>{{ $admin->proprietariGestiti()->withCount('strutture')->get()->sum('strutture_count') }}</td>
                                <td>{{ $admin->servizi_count ?? 0 }}</td>
                                <td>{{ $admin->fatture_count ?? 0 }}</td>
                                <td>{{ $admin->attivo ? 'Attivo' : 'Disattivo' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.amministratori.edit', ['id' => $admin->id, 'tab' => 'profilo']) }}" class="btn btn-sm btn-outline-secondary">Apri scheda</a>
                                    <form action="{{ route('superadmin.amministratori.disable', $admin->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Disabilita</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
