@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h4>Utenti struttura: {{ $struttura->nome_struttura }}</h4>
        </div>
        <div class="col text-end">
            <a class="btn btn-primary" href="{{ route('strutture.utenti.create') }}">Nuovo utente</a>
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
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($utenti as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->ruolo }}</td>
                        <td>
                            <form class="d-flex align-items-center gap-2" method="POST" action="{{ route('strutture.utenti.reset', $u->id) }}">
                                @csrf
                                <input type="password" name="password" class="form-control form-control-sm" placeholder="Nuova password" required minlength="8">
                                <button type="submit" class="btn btn-sm btn-secondary">Reset</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nessun utente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
