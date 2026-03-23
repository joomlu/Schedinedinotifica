@extends('layouts.master-without-nav')
@section('title', 'Invito Web Check-in')

@section('body')
<body>
@endsection

@section('content')
<div class="auth-page-wrapper pt-5 pb-5">
    <div class="auth-page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-lg-10">
                    <div class="card border-0 shadow-lg overflow-hidden webcheckin-invite-card">
                        <div class="webcheckin-invite-hero p-4 p-lg-5 text-center">
                            @if(!empty($strutturaInfo?->logo))
                                <div class="mb-4">
                                    <img src="{{ asset($strutturaInfo->logo) }}" alt="Logo struttura" class="webcheckin-invite-logo">
                                </div>
                            @endif
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white bg-opacity-75 small text-uppercase fw-semibold text-primary mb-3">
                                <i class="ri-hotel-line"></i>
                                <span>Web Check-in</span>
                            </div>
                            <h1 class="display-6 fw-semibold mb-3">Benvenuto in {{ $strutturaInfo->nome_struttura ?? 'Struttura' }}</h1>
                            <p class="lead text-muted mb-3">
                                Abbiamo preparato per te un accesso riservato al Web Check-in. In pochi minuti potrai lasciare pronta la registrazione online e rendere l'arrivo in struttura molto piu rapido, semplice e piacevole.
                            </p>
                            <div class="small text-muted webcheckin-invite-kicker">
                                Un passaggio guidato, ordinato e confortevole per iniziare bene il soggiorno fin dal primo momento.
                            </p>
                        </div>

                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="rounded-3 border bg-light-subtle p-3 h-100 text-center">
                                        <div class="small text-muted text-uppercase mb-1">Prenotazione</div>
                                        <div class="fw-semibold">{{ $richiesta->numero_prenotazione }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded-3 border bg-light-subtle p-3 h-100 text-center">
                                        <div class="small text-muted text-uppercase mb-1">Arrivo</div>
                                        <div class="fw-semibold">{{ optional($richiesta->arrivo)->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded-3 border bg-light-subtle p-3 h-100 text-center">
                                        <div class="small text-muted text-uppercase mb-1">Ospiti</div>
                                        <div class="fw-semibold">{{ $richiesta->quantita_persone }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="webcheckin-invite-panel rounded-4 p-4 mb-4">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-7 text-lg-start text-center">
                                        <div class="small text-uppercase text-muted mb-2">Prima di iniziare</div>
                                        <div class="fw-semibold fs-5 mb-2">Tieni a portata di mano i documenti di identita</div>
                                        <div class="text-muted">
                                            Ti consigliamo di avere con te i dati anagrafici e i documenti delle persone incluse nel soggiorno. Potrai salvare tutto online e lasciare la registrazione gia pronta per la reception.
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="webcheckin-invite-steps rounded-4 bg-white border p-3">
                                            <div class="webcheckin-invite-step"><span>1</span> Apri la tua pagina riservata</div>
                                            <div class="webcheckin-invite-step"><span>2</span> Compila la schedina online</div>
                                            <div class="webcheckin-invite-step"><span>3</span> Salva il Web Check-in</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column align-items-center gap-3">
                                <a href="{{ $checkinUrl }}" class="btn btn-lg btn-success px-5 webcheckin-invite-cta">
                                    <i class="ri-arrow-right-circle-line align-middle me-2"></i> Apri il tuo Web Check-in
                                </a>
                                <div class="small text-muted">Se hai ricevuto questo invito, significa che la tua struttura ti sta aspettando.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<style>
    .webcheckin-invite-card {
        border-radius: 1.5rem;
    }

    .webcheckin-invite-hero {
        background: linear-gradient(135deg, rgba(31,111,120,.12), rgba(46,139,87,.10));
    }

    .webcheckin-invite-kicker {
        max-width: 640px;
        margin: 0 auto;
    }

    .webcheckin-invite-logo {
        max-height: 78px;
        max-width: 240px;
        object-fit: contain;
    }

    .webcheckin-invite-panel {
        border: 1px solid rgba(31,111,120,.12);
        background: linear-gradient(180deg, rgba(255,255,255,.85), rgba(246,247,245,.95));
    }

    .webcheckin-invite-steps {
        display: grid;
        gap: .75rem;
    }

    .webcheckin-invite-step {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-weight: 500;
        color: #35505b;
    }

    .webcheckin-invite-step span {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(31,111,120,.12);
        color: #1f6f78;
        font-weight: 700;
        flex-shrink: 0;
    }

    .webcheckin-invite-cta {
        min-width: 320px;
        border-radius: 999px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(46,139,87,.18);
    }

    @media (max-width: 576px) {
        .webcheckin-invite-cta {
            min-width: 100%;
        }
    }
</style>
@endsection
