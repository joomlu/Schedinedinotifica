@extends('layouts.master')

@section('title', 'Aiuto')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Aiuto @endslot
    @slot('title') Scegli il centro di aiuto @endslot
@endcomponent

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100 mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-book-open-line text-primary fs-20"></i>
                    <h4 class="card-title mb-0">Centro assistenza generale</h4>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-3">Questa e la guida operativa normale del sistema. Qui trovi esattamente quello che vede il proprietario e, in generale, l aiuto quotidiano della struttura: guida rapida, impostazioni e gestione, moduli, FAQ e problemi comuni.</p>
                <ul class="mb-4 ps-3">
                    <li class="mb-2">Guida rapida del lavoro quotidiano</li>
                    <li class="mb-2">Centro assistenza operativo per proprietario e struttura</li>
                    <li class="mb-2">Moduli, domande frequenti e problemi comuni</li>
                </ul>
                <div class="mt-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('help.general') }}" class="btn btn-primary">Apri centro assistenza generale</a>
                    <a href="{{ route('help.print', ['section' => 'general']) }}" target="_blank" class="btn btn-light">Stampa guida generale</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100 mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-shield-user-line text-primary fs-20"></i>
                    <h4 class="card-title mb-0">Guida admin e superadmin</h4>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-3">Questa e la guida amministrativa separata. Serve a chi gestisce il software e deve capire ruoli, perimetri, responsabilita e correzione degli errori nella catena tra admin, proprietari e strutture.</p>
                <ul class="mb-4 ps-3">
                    <li class="mb-2">Cosa governa solo il superadmin</li>
                    <li class="mb-2">Cosa puo fare l admin nel proprio perimetro</li>
                    <li class="mb-2">Come leggere e correggere la catena amministrativa</li>
                </ul>
                <div class="mt-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('help.admin') }}" class="btn btn-primary">Apri guida admin e superadmin</a>
                    <a href="{{ route('help.print', ['section' => 'admin-index']) }}" target="_blank" class="btn btn-light">Stampa guida admin</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
