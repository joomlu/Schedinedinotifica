@extends('layouts.master')
@section('title') QA Tenancy @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') QA @endslot
        @slot('title') Tenancy e conteggi @endslot
    @endcomponent

    <p class="text-muted">Verifica rapida di fuga dati per struttura. Mostra conteggi per struttura_id e record legacy (NULL).</p>

    @foreach($summary as $table => $data)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Tabella: {{ $table }}</h5>
                @if(!$data['exists'])
                    <p class="text-warning mb-0">Tabella non presente.</p>
                    @continue
                @endif
                <p class="mb-1"><strong>Totale:</strong> {{ $data['total'] }}</p>
                <p class="mb-1"><strong>Con struttura_id NULL:</strong> {{ $data['null_struttura'] }}</p>
                @if($currentId)
                    <p class="mb-1"><strong>Struttura corrente (ID {{ $currentId }}):</strong> {{ $data['current'] }}</p>
                @endif
                <div class="table-responsive mt-2">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>struttura_id</th>
                                <th>conteggio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['by_struttura'] as $row)
                                <tr>
                                    <td>{{ $row->struttura_id ?? 'NULL' }}</td>
                                    <td>{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">Nessun dato.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
