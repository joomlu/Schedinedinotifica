@extends('layouts.master-without-nav')
@section('title', 'Web Check-in')

@section('body')
<body>
@endsection

@section('content')
<div class="auth-page-wrapper pt-5 pb-5">
    <div class="auth-page-content">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-xxl-11 col-lg-12">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ collect($errors->all())->take(3)->implode(' ') }}</div>
                    @endif

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-light-subtle border-0 py-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-6">
                                    @if(!empty($strutturaInfo?->logo))
                                        <div class="mb-3">
                                            <img src="{{ asset($strutturaInfo->logo) }}" alt="Logo struttura" style="max-height: 64px; max-width: 220px; object-fit: contain;">
                                        </div>
                                    @endif
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-hotel-line me-2 text-primary fs-4"></i>
                                        <h2 class="h3 mb-0">Web Check-in</h2>
                                    </div>
                                    <div class="text-muted">
                                        {{ $strutturaInfo->nome_struttura ?? 'Struttura' }} · Prenotazione {{ $webCheckinRichiesta->numero_prenotazione }}
                                    </div>
                                    <div class="small text-muted mt-2">
                                        Benvenuto nel servizio di Web Check-in. Compilando questa schedina online prima dell'arrivo permetti alla reception di preparare in anticipo la tua registrazione e di rendere l'accoglienza piu rapida, semplice e ordinata.
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="row g-3">
                                        <div class="col-md-4 col-6">
                                            <div class="border rounded p-3 bg-white h-100">
                                                <div class="text-muted small">Arrivo</div>
                                                <div>{{ optional($webCheckinRichiesta->arrivo)->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <div class="border rounded p-3 bg-white h-100">
                                                <div class="text-muted small">Partenza</div>
                                                <div>{{ optional($webCheckinRichiesta->partenza)->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="border rounded p-3 bg-white h-100">
                                                <div class="text-muted small">Persone previste</div>
                                                <div>{{ $webCheckinRichiesta->quantita_persone }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-body">
                            <div class="row g-4 mb-4">
                                        <div class="col-xl-4">
                                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                                    <i class="ri-information-line me-2 text-primary"></i>
                                                    <h5 class="card-title mb-0 fs-6">Come compilare</h5>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <div class="small text-muted">
                                                Questa e la stessa schedina utilizzata dalla struttura alla reception. Puoi compilarla con calma online, salvare i dati e lasciare pronta la registrazione prima dell'arrivo.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                <div class="col-xl-4">
                                    <div class="card border-0 bg-light-subtle h-100 mb-0">
                                        <div class="card-header border-0 py-2 d-flex align-items-center">
                                            <i class="ri-passport-line me-2 text-primary"></i>
                                            <h5 class="card-title mb-0 fs-6">Documenti pronti</h5>
                                        </div>
                                        <div class="card-body pt-2">
                                            <div class="small text-muted">
                                                Tieni a portata di mano documento di identita, dati anagrafici, residenza e le informazioni delle persone comprese nel soggiorno, cosi la compilazione sara piu veloce.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        <div class="col-xl-4">
                                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                                    <i class="ri-checkbox-circle-line me-2 text-primary"></i>
                                                    <h5 class="card-title mb-0 fs-6">Invio finale</h5>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <div class="small text-muted">
                                                Quando hai terminato, usa il pulsante finale per salvare il Web Check-in. La reception trovera la tua schedina online gia pronta e potra completare l'arrivo in modo piu rapido.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
                                'showCircuitSaveButtons' => false,
                                'showIstatFields' => true,
                                'primarySaveLabel' => 'Salva Web Check-in',
                                'showPrintTassa' => false,
                                'geoEndpointBase' => $geoEndpointBase,
                                'disableCrudConfirm' => true,
                                'disableClientValidation' => true,
                                'usePutMethod' => false,
                                'formAction' => $formAction,
                                'formTitle' => 'Compila il Web Check-in online',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('schedina.partials.scripts')
@endsection
