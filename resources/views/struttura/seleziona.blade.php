@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h4>Seleziona struttura</h4>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
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
                        <td colspan="8">Nessuna struttura disponibile.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
