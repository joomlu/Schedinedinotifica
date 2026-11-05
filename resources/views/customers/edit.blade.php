@extends('layouts.master')
@section('title')
    Modifica cliente
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Moduli
        @endslot
        @slot('title')
            Modifica cliente
        @endslot
    @endcomponent

    <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
        <a href="{{ \App\Support\CustomerListReturn::url() }}" class="btn btn-light">Torna all’elenco clienti</a>
        <a href="{{ route('customer.show', $customer->id) }}" class="btn btn-outline-secondary">Visualizza cliente</a>
        <a href="{{ route('customer.print', $customer->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary">Stampa scheda completa</a>
    </div>
    <p class="text-muted">La stampa contiene i dati salvati. Salva le modifiche prima di stampare.</p>
    <x-xx.scheda-cliente
        mode="edit"
        :customer="$customer"
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
