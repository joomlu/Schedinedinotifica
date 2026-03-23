@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col">
            <h4>Nuovo utente per {{ $struttura->nome_struttura }}</h4>
        </div>
        <div class="col text-end">
            <a class="btn btn-light" href="{{ route('strutture.utenti.index') }}">Indietro</a>
        </div>
    </div>

    <form method="POST" action="{{ route('strutture.utenti.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
</div>
@endsection
