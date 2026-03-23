@extends('layouts.master')
@section('title') QA Sessione @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') QA @endslot
        @slot('title') Stato sessione @endslot
    @endcomponent

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Utente</h5>
            <p class="mb-1"><strong>ID:</strong> {{ $user->id ?? 'n/d' }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $user->email ?? 'n/d' }}</p>
            <p class="mb-0"><strong>Ruolo:</strong> {{ $user->ruolo ?? 'n/d' }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Struttura corrente</h5>
            <p class="mb-1"><strong>Session struttura_corrente_id:</strong> {{ $sessionStrutturaId ?? '—' }}</p>
            <p class="mb-1"><strong>StrutturaCorrente::getId():</strong> {{ $currentId ?? '—' }}</p>
            @if($struttura)
                <p class="mb-1"><strong>Nome:</strong> {{ $struttura->nome_struttura }}</p>
                <p class="mb-0"><strong>Città:</strong> {{ $struttura->citta }} {{ $struttura->provincia ? '(' . $struttura->provincia . ')' : '' }}</p>
            @else
                <p class="mb-0 text-muted">Nessuna struttura caricata.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Impersonazione</h5>
            <p class="mb-1"><strong>impersonator_id:</strong> {{ $impersonation['impersonator_id'] ?? '—' }}</p>
            <p class="mb-0"><strong>impersonated_id:</strong> {{ $impersonation['impersonated_id'] ?? '—' }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Verifica servizio struttura</h5>
            @if($servizio)
                <p class="mb-1"><strong>Attiva:</strong> {{ $servizio['attiva'] ? 'Sì' : 'No' }}</p>
                <p class="mb-1"><strong>Scadenza:</strong> {{ $servizio['scadenza_servizio'] ?? '—' }}</p>
                <p class="mb-1"><strong>Piano:</strong> {{ $servizio['piano'] ?? '—' }}</p>
                <p class="mb-1"><strong>Pagamento:</strong> {{ $servizio['stato_pagamento'] ?? '—' }}</p>
                <p class="mb-0"><strong>Servizio attivo (check middleware):</strong> {{ $servizio['servizio_attivo'] ? 'Sì' : 'No' }}</p>
            @else
                <p class="mb-0 text-muted">Nessuna struttura corrente.</p>
            @endif
        </div>
    </div>
@endsection
