@extends('layouts.master')
@section('title') Impersonazione @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') Impersonazione @endslot
    @endcomponent

    @if(session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if($impersonatorId)
        <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
            <div>
                Sei in impersonazione. Utente loggato: {{ auth()->user()->name ?? 'n/d' }} (ID {{ $impersonatedId }})
            </div>
            <form method="POST" action="{{ route('superadmin.impersona.stop') }}" class="ms-3">
                @csrf
                <button class="btn btn-sm btn-outline-dark" type="submit">Esci impersonazione</button>
            </form>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-3">Scegli utente da impersonare</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Struttura</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->ruolo }}</td>
                                <td>{{ $user->struttura_id }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('superadmin.impersona.start', $user->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Impersona</button>
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
