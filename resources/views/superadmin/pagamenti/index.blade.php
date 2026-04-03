@extends('layouts.master')
@section('title') Pagamenti @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Pagamenti e Licenze @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-3">Situazione licenze</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Struttura</th>
                            <th>Città</th>
                            <th>Provincia</th>
                            <th>Attiva</th>
                            <th>Scadenza</th>
                            <th>Piano</th>
                            <th>Stato pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($strutture as $struttura)
                            <tr>
                                <td>{{ $struttura->nome_struttura }}</td>
                                <td>{{ $struttura->citta }}</td>
                                <td>{{ $struttura->provincia }}</td>
                                <td>{{ $struttura->attiva ? 'Sì' : 'No' }}</td>
                                <td>{{ $struttura->scadenza_servizio }}</td>
                                <td>{{ $struttura->piano }}</td>
                                <td>{{ $struttura->stato_pagamento }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
