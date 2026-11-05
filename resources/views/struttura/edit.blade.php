@extends('layouts.master')
@section('title')
    Modifica Struttura
@endsection
@section('content')
    @include('struttura.form', [
        'struttura' => $struttura,
        'tipologieGenerali' => $tipologieGenerali,
        'tipologieStruttura' => $tipologieStruttura,
        'classificazioni' => $classificazioni,
        'zoneOptions' => $zoneOptions ?? [],
        'localitaOptions' => $localitaOptions ?? [],
    ])
@endsection
