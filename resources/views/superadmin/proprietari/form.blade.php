@extends('layouts.master')
@section('title') Proprietari @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo proprietario' : 'Modifica proprietario' }} @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('superadmin.proprietari.store') : route('superadmin.proprietari.update', $proprietario->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" value="{{ old('nome', $proprietario->nome) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $proprietario->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $proprietario->telefono) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admin associato</label>
                        <x-ui.select name="admin_id">
                            <option value="">-- Nessuno --</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('admin_id', $proprietario->admin_id) == $admin->id ? 'selected' : '' }}>{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="3">{{ old('note', $proprietario->note) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('superadmin.proprietari.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
@endsection
