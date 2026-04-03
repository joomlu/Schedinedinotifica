@extends('layouts.master')
@section('title') Amministratori @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo amministratore' : 'Modifica amministratore' }} @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('superadmin.amministratori.store') : route('superadmin.amministratori.update', $admin->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password @if($mode==='edit')<small class="text-muted">(lascia vuoto per non cambiare)</small>@endif</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('superadmin.amministratori.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
@endsection
