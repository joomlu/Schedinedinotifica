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
