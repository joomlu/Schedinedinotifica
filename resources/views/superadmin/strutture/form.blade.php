@extends('layouts.master')
@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuova struttura' : 'Modifica struttura' }} @endslot
    @endcomponent

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('superadmin.strutture.store') : route('superadmin.strutture.update', $struttura->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome struttura</label>
                        <input type="text" name="nome_struttura" class="form-control" value="{{ old('nome_struttura', $struttura->nome_struttura) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Città</label>
                        <input type="text" name="citta" class="form-control" value="{{ old('citta', $struttura->citta) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Provincia</label>
                        <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $struttura->provincia) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Proprietario</label>
                        <x-ui.select name="proprietario_id">
                            <option value="">-- Nessuno --</option>
                            @foreach($proprietari as $proprietario)
                                <option value="{{ $proprietario->id }}" {{ old('proprietario_id', $struttura->proprietario_id) == $proprietario->id ? 'selected' : '' }}>{{ $proprietario->nome }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Attiva</label>
                        <x-ui.select name="attiva">
                            <option value="1" {{ old('attiva', $struttura->attiva ?? true) ? 'selected' : '' }}>Sì</option>
                            <option value="0" {{ old('attiva', $struttura->attiva ?? true) ? '' : 'selected' }}>No</option>
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Scadenza servizio</label>
                        <x-calendario name="scadenza_servizio" variant="single" :value="old('scadenza_servizio', $struttura->scadenza_servizio)" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Piano</label>
                        <input type="text" name="piano" class="form-control" value="{{ old('piano', $struttura->piano) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stato pagamento</label>
                        <input type="text" name="stato_pagamento" class="form-control" value="{{ old('stato_pagamento', $struttura->stato_pagamento) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('superadmin.strutture.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
@endsection
