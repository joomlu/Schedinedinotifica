@extends('layouts.master')
@section('title') QA Demo Map @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') QA @endslot
        @slot('title') Demo Map @endslot
    @endcomponent

    <p class="text-muted">Mappa read-only admin → proprietario → struttura con stato servizio. Solo super_admin.</p>
    <p class="text-muted">StrutturaCorrente ID: {{ $currentStrutturaId ?? 'nessuna' }}</p>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Proprietario</th>
                    <th>Struttura</th>
                    <th>Servizio</th>
                    <th>Località</th>
                    <th>CIR / CIN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $s = $row['structure'];
                    @endphp
                    <tr>
                        <td>{{ $row['admin']->email ?? 'n/d' }}</td>
                        <td>{{ $row['owner']->email ?? 'n/d' }}</td>
                        <td>
                            @if($s)
                                <div class="fw-semibold">{{ $s->nome_struttura ?? $s->name ?? 'n/d' }}</div>
                                <div class="text-muted small">{{ $s->email ?? 'n/d' }} (id {{ $s->id ?? 'n/d' }})</div>
                            @else
                                n/d
                            @endif
                        </td>
                        <td>
                            @if($s)
                                <div>{{ $s->piano ?? 'n/d' }} / {{ $s->stato_pagamento ?? 'n/d' }}</div>
                                <div class="text-muted small">Scadenza: {{ $s->scadenza_servizio ?? 'n/d' }} | Attiva: {{ $s->attiva ? 'sì' : 'no' }}</div>
                            @else
                                n/d
                            @endif
                        </td>
                        <td>{{ $row['location'] ?: 'n/d' }}</td>
                        <td>
                            @if($s)
                                <div>CIR: {{ $s->cir ?? 'n/d' }}</div>
                                <div>CIN: {{ $s->cin ?? 'n/d' }}</div>
                            @else
                                n/d
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">Nessun dato demo disponibile.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
