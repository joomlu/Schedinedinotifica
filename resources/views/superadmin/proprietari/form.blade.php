@extends('layouts.master')
@section('title') Proprietari @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo proprietario' : 'Modifica proprietario' }} @endslot
    @endcomponent

    <form method="POST" action="{{ $mode === 'create' ? route('superadmin.proprietari.store') : route('superadmin.proprietari.update', $proprietario->id) }}">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        @include('shared.proprietari.form-panels')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('superadmin.proprietari.index') }}" class="btn btn-light me-2">Annulla</a>
            <button type="submit" class="btn btn-success">Salva proprietario</button>
        </div>
    </form>
@endsection
