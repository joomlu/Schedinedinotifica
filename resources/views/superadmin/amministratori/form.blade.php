@extends('layouts.master')
@section('title') Amministratori @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo amministratore' : 'Modifica amministratore' }} @endslot
    @endcomponent

    <form method="POST" action="{{ $mode === 'create' ? route('superadmin.amministratori.store') : route('superadmin.amministratori.update', $admin->id) }}">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        @include('shared.amministratori.form-panels')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('superadmin.amministratori.index') }}" class="btn btn-light me-2">Annulla</a>
            <button type="submit" class="btn btn-success">Salva amministratore</button>
        </div>
    </form>
@endsection
