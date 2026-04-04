@extends('layouts.master')
@section('title') Strutture @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuova struttura' : 'Modifica struttura' }} @endslot
    @endcomponent

    <form method="POST" action="{{ $mode === 'create' ? route('admin.strutture.store') : route('admin.strutture.update', $struttura->id) }}">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        @include('shared.strutture.admin-form-panels-admin')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('admin.strutture.index') }}" class="btn btn-light me-2">Annulla</a>
            <button type="submit" class="btn btn-success">Salva struttura</button>
        </div>
    </form>
@endsection
