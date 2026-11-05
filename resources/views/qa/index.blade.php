@extends('layouts.master')
@section('title') QA Dashboard @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') QA @endslot
        @slot('title') QA Dashboard @endslot
    @endcomponent

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sessione</h5>
                    <p class="text-muted mb-3">Stato user, struttura corrente, impersonazione.</p>
                    <a href="{{ route('qa.session') }}" class="btn btn-primary">Apri</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Accesso</h5>
                    <p class="text-muted mb-3">Matrice attesa OK/403 per ruolo.</p>
                    <a href="{{ route('qa.accesso') }}" class="btn btn-primary">Apri</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tenancy</h5>
                    <p class="text-muted mb-3">Conteggi per struttura e legacy NULL.</p>
                    <a href="{{ route('qa.tenancy') }}" class="btn btn-primary">Apri</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Demo Map</h5>
                    <p class="text-muted mb-3">Admin → proprietari → strutture (solo lettura).</p>
                    <a href="{{ route('qa.demo-map') }}" class="btn btn-primary">Apri</a>
                </div>
            </div>
        </div>
    </div>
@endsection
