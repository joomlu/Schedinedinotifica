@extends('layouts.master')
@section('title') Proprietari @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('title') Proprietari @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">I miei proprietari</h4>
        <a href="{{ route('admin.proprietari.create') }}" class="btn btn-primary">Nuovo proprietario</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="card-title mb-2">Assegnazione automatica</h6>
            <p class="text-muted mb-0">Ogni proprietario creato da questo pannello viene assegnato automaticamente all admin corrente. Da qui l admin costruisce il proprio perimetro: proprietari prima, strutture dopo.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Proprietari</div>
                    <div class="fw-bold fs-4">{{ $proprietari->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Attivi</div>
                    <div class="fw-bold fs-4 text-success">{{ $proprietari->where('attivo', true)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Strutture collegate</div>
                    <div class="fw-bold fs-4 text-primary">{{ $proprietari->sum('strutture_count') }}</div>
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
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Strutture</th>
                            <th>Attivo</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proprietari as $proprietario)
                            <tr ondblclick="window.location.href='{{ route('admin.proprietari.edit', $proprietario->id) }}'" style="cursor:pointer;">
                                <td>
                                    <div class="fw-semibold">{{ $proprietario->nome }}</div>
                                    <div class="small text-muted">ID {{ $proprietario->id }}</div>
                                </td>
                                <td>{{ $proprietario->email }}</td>
                                <td>{{ $proprietario->telefono }}</td>
                                <td>{{ $proprietario->strutture_count }}</td>
                                <td>
                                    <span class="badge {{ $proprietario->attivo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $proprietario->attivo ? 'Attivo' : 'Disattivo' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.proprietari.edit', $proprietario->id) }}" class="btn btn-soft-secondary btn-sm" title="Accedi proprietario">
                                        <i class="ri-external-link-line"></i>
                                    </a>
                                    <form action="{{ route('admin.proprietari.disable', $proprietario->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-soft-danger btn-sm" title="Disabilita proprietario">
                                            <i class="ri-user-unfollow-line"></i>
                                        </button>
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
