@extends('layouts.master')
@section('title') Schedina @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Schedine
        @endslot
        @slot('title')
            Nuova schedina
        @endslot
    @endcomponent

    @if(!empty($prefilledCustomer))
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="ri-user-3-line me-2"></i>
            <div>
                Schedina precompilata per {{ $prefilledCustomer->surname }} {{ $prefilledCustomer->name }} ({{ $prefilledCustomer->numero_cliente }}). Controlla i dati e completa le date di soggiorno.
            </div>
        </div>
    @endif

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
