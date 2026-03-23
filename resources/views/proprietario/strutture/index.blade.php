@extends('layouts.master')
@section('title') Le mie strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Proprietario @endslot
        @slot('title') Le mie strutture @endslot
    @endcomponent

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        @forelse($strutture as $struttura)
            <div class="col-lg-4 col-md-6">
                <div class="card {{ $currentId === $struttura->id ? 'border-success' : '' }}">
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ $struttura->nome_struttura }}</h5>
                        <p class="text-muted mb-2">{{ $struttura->citta }} @if($struttura->provincia) ({{ $struttura->provincia }}) @endif</p>
                        <ul class="list-unstyled mb-3">
                            <li><strong>Attiva:</strong> {{ $struttura->attiva ? 'Sì' : 'No' }}</li>
                            <li><strong>Scadenza servizio:</strong> {{ $struttura->scadenza_servizio ?? '—' }}</li>
                            <li><strong>Piano:</strong> {{ $struttura->piano ?? '—' }}</li>
                            <li><strong>Pagamento:</strong> {{ $struttura->stato_pagamento ?? '—' }}</li>
                        </ul>
                        <form method="POST" action="{{ route('strutture.seleziona', $struttura->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                @if($currentId === $struttura->id)
                                    Struttura corrente
                                @else
                                    Entra nella struttura
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning">Nessuna struttura disponibile.</div>
            </div>
        @endforelse
    </div>
@endsection
