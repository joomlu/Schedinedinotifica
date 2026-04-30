@extends('layouts.master')
@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Strutture @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Strutture</h4>
        <a href="{{ route('superadmin.strutture.create') }}" class="btn btn-primary">Nuova struttura</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

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
                            <th>Avviso</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($strutture as $struttura)
                            <tr>
                                <td>{{ $struttura->nome_struttura }}</td>
                                <td>{{ $struttura->citta }}</td>
                                <td>{{ $struttura->provincia }}</td>
                                <td>{{ optional($struttura->proprietario)->nome }}</td>
                                <td>{{ $struttura->attiva ? 'Sì' : 'No' }}</td>
                                <td>{{ $struttura->scadenza_servizio }}</td>
                                <td>{{ $struttura->piano }}</td>
                                <td>{{ $struttura->stato_pagamento }}</td>
                                <td>{{ $struttura->avviso ?: 'attivo' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.strutture.edit', $struttura->id) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                                    <form action="{{ route('superadmin.strutture.destroy', $struttura->id) }}" method="POST" class="d-inline" data-confirm-label="{{ 'la struttura ' . $struttura->nome_struttura }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Cestino</button>
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
