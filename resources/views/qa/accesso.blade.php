@extends('layouts.master')
@section('title') QA Accesso @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') QA @endslot
        @slot('title') Accesso atteso per ruolo @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Matrice attesa (OK/403) per le principali sezioni. Non esegue chiamate HTTP, è una guida rapida per QA manuale.</p>
            @foreach($matrix as $ruolo => $righe)
                <h5 class="mt-3">Ruolo: {{ $ruolo }}</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Sezione</th>
                                <th>Route</th>
                                <th>Atteso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($righe as $r)
                                <tr>
                                    <td>{{ $r['name'] }}</td>
                                    <td>{{ $r['route'] }}</td>
                                    <td><span class="badge bg-{{ $r['expected'] === 'OK' ? 'success' : 'danger' }}">{{ $r['expected'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
