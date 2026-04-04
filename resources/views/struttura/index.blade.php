@extends('layouts.master')
@section('title')
    @lang('translation.strutture')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Anagrafica
        @endslot
        @slot('title')
            Strutture Ricettive
        @endslot
    @endcomponent

    <div class="alert alert-info">
        Questa pagina non è più disponibile. Gestire la struttura principale da
        <a href="{{ route('struttura.edit') }}">qui</a>.
    </div>
@endsection
