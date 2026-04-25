@extends('layouts.master')
@section('title', 'Correggi riga import')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Clienti
        @endslot
        @slot('title')
            Correggi riga import
        @endslot
    @endcomponent

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-1">Cliente {{ $row->row_number - 1 }}</h4>
            <p class="text-muted mb-0">Completa la riga usando la stessa UI del cliente. La logica di verifica resta separata dall'anagrafica reale.</p>
        </div>
        <a href="{{ route('customer.import.show', $batch) }}" class="btn btn-light">Torna alla verifica</a>
    </div>

    <x-xx.scheda-cliente
        mode="edit"
        formModeOverride="import"
        :customer="$customerDraft"
        :formActionOverride="route('customer.import.row.update', [$batch, $row])"
        formMethodOverride="PUT"
        cardTitleOverride="Import clienti - correzione riga"
        draftKeyOverride="clienti.import.{{ $batch->id }}.row.{{ $row->id }}.draft.v1"
        submitLayout="import"
        primarySubmitLabel="Aggiorna riga importazione"
        :tipiClienti="$tipiClienti"
        :gruppiLivello1="$gruppiLivello1"
        :gruppiLivello2="$gruppiLivello2"
        :gruppiLivello3="$gruppiLivello3"
        :titoli="$titoli"
        :tipiVia="$tipiVia"
        :tipiDocumento="$tipiDocumento"
        :nations="$nations"
        :regions="$regions"
        :provinces="$provinces"
        :ciudades="$ciudades"
        :rilasciatoDa="$rilasciatoDa"
        :cittadinanze="$cittadinanze"
        :geoNazioni="$geoNazioni"
    />
@endsection
