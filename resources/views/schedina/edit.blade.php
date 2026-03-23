@extends('layouts.master')
@section('title') Schedina @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Schedine
        @endslot
        @slot('title')
            Modifica schedina
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-12 col-lg-11 mx-auto">
            @include('schedina.partials.form', [
                'schedina' => $schedina,
                'titoli' => $titoli,
                'tipiVia' => $tipiVia,
                'tipiDocumento' => $tipiDocumento,
                'nations' => $nations,
                'regions' => $regions,
                'provinces' => $provinces,
                'ciudades' => $ciudades,
                'tassaConfig' => $tassaConfig,
                'esenzioni' => $esenzioni,
                'strutturaInfo' => $strutturaInfo,
                'geoEndpoints' => $geoEndpoints,
            ])
        </div>
    </div>
@endsection

@section('script')
    @include('schedina.partials.scripts')
@endsection
