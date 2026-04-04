@extends('layouts.master')
@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuova struttura' : 'Modifica struttura' }} @endslot
    @endcomponent

    <form method="POST" action="{{ $mode === 'create' ? route('superadmin.strutture.store') : route('superadmin.strutture.update', $struttura->id) }}">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        @include('shared.strutture.admin-form-panels')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('superadmin.strutture.index') }}" class="btn btn-light me-2">Annulla</a>
            <button type="submit" class="btn btn-success">Salva struttura</button>
        </div>
    </form>
@endsection
