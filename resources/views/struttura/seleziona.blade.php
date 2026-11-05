@extends('layouts.master')

@section('title') Seleziona struttura @endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Strutture @endslot
    @slot('title') Seleziona struttura @endslot
@endcomponent
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-striped mb-0">
                <thead class="table-light">
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
</div>
@endsection
