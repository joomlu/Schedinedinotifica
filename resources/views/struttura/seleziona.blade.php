@extends('layouts.master')

@section('title', 'Seleziona struttura')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Strutture @endslot
        @slot('title') Seleziona struttura @endslot
    @endcomponent

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h4 class="card-title mb-1">Seleziona struttura</h4>
            <p class="text-muted mb-0">Questa schermata sceglie la struttura corrente della sessione. Superadmin e admin possono cambiarla quando devono lavorare operativamente su una struttura diversa.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Proprietario</th>
                            <th>Attiva</th>
                            <th>Scadenza servizio</th>
                            <th>Piano</th>
                            <th>Stato pagamento</th>
                            <th>Corrente</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($strutture as $struttura)
                            <tr>
                                <td>{{ $struttura->id }}</td>
                                <td>{{ $struttura->nome_struttura }}</td>
                                <td>{{ optional($struttura->proprietario)->nome ?? '—' }}</td>
                                <td>{{ $struttura->attiva ? 'Si' : 'No' }}</td>
                                <td>{{ $struttura->scadenza_servizio }}</td>
                                <td>{{ $struttura->piano }}</td>
                                <td>{{ $struttura->stato_pagamento }}</td>
                                <td>{{ $currentId === $struttura->id ? '✓' : '' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('strutture.seleziona', $struttura->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Seleziona</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">Nessuna struttura disponibile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
