@extends('layouts.master-without-nav')
@section('title', $richiesta->compilato_at ? 'Compilazione conclusa' : 'Dati parziali salvati')

@section('body')
<body>
@endsection

@section('content')
<style>body{background:#f3f5f4}.btn-primary{background:{{ $identitaHotel['colore'] }};border-color:{{ $identitaHotel['colore'] }};color:{{ $identitaHotel['testo_colore'] }}}</style>
<div class="auth-page-wrapper pt-5 pb-5">
    <div class="auth-page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-light-subtle border-0 py-4 text-center">
                            <div class="mb-3">
                                @if($identitaHotel['logo_url'])<img src="{{ $identitaHotel['logo_url'] }}" width="{{ $identitaHotel['logo_width'] }}" height="{{ $identitaHotel['logo_height'] }}" alt="{{ $identitaHotel['nome'] }}" style="max-width:200px;max-height:90px;object-fit:contain">@endif
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-2 mx-auto" style="width: 72px; height: 72px;">
                                    <i class="ri-check-line"></i>
                                </span>
                            </div>
                            <h2 class="h3 mb-2">{{ $richiesta->compilato_at ? 'Compilazione conclusa' : 'Dati parziali salvati' }}</h2>
                            <div class="text-muted">
                                {{ $richiesta->compilato_at ? 'Grazie! Abbiamo ricevuto i tuoi dati. La reception li verificherà prima del tuo arrivo.' : 'Le informazioni inserite sono state conservate. Riprendi dallo stesso link per aggiungere i dati mancanti oppure completali con la reception.' }}
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small">Struttura</div>
                                        <div>{{ $strutturaInfo->nome_struttura ?? 'Struttura' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small">Prenotazione</div>
                                        <div>{{ $richiesta->numero_prenotazione }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small">Ospiti</div>
                                        <div>{{ $richiesta->quantita_persone }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm mb-4" id="webcheckin-ricevuta">
                                <div class="card-header bg-light-subtle border-0 d-flex align-items-center">
                                    <i class="ri-file-text-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0">Riepilogo Web Check-in</h5>
                                </div>
                                <div class="card-body">
                                    <div class="ricevuta-print">
                                        <div class="ricevuta-print__header">
                                            @if($identitaHotel['logo_url'])
                                                <div class="ricevuta-print__logo-wrap">
                                                    <img src="{{ $identitaHotel['logo_url'] }}" width="{{ $identitaHotel['logo_width'] }}" height="{{ $identitaHotel['logo_height'] }}" alt="Logo struttura" class="ricevuta-print__logo">
                                                </div>
                                            @endif
                                            <div class="ricevuta-print__title">{{ $richiesta->compilato_at ? 'Compilazione conclusa' : 'Dati parziali salvati' }}</div>
                                            <div class="ricevuta-print__subtitle">{{ $strutturaInfo->nome_struttura ?? 'Struttura' }}</div>
                                        </div>

                                        <div class="ricevuta-print__line">
                                            <div><span class="text-muted">Prenotazione:</span> {{ $richiesta->numero_prenotazione }}</div>
                                            <div><span class="text-muted">Ospiti:</span> {{ $richiesta->quantita_persone }}</div>
                                        </div>

                                        <div class="ricevuta-print__line ricevuta-print__line--full">
                                            <div><span class="text-muted">Ospite principale:</span> {{ trim(($schedina->name ?? '') . ' ' . ($schedina->surname ?? '')) ?: ($richiesta->nome_referente ?: '-') }}</div>
                                        </div>

                                        <div class="ricevuta-print__line">
                                            <div><span class="text-muted">Arrivo:</span> {{ $schedina->arrive ? \Carbon\Carbon::parse($schedina->arrive)->format('d/m/Y') : '-' }}</div>
                                            <div><span class="text-muted">Partenza:</span> {{ $schedina->departure ? \Carbon\Carbon::parse($schedina->departure)->format('d/m/Y') : '-' }}</div>
                                        </div>

                                        <div class="ricevuta-print__line">
                                            <div><span class="text-muted">Persone totali:</span> {{ $schedina->cant_people ?: '-' }}</div>
                                            <div><span class="text-muted">Accompagnatori:</span> {{ $componentiCount }}</div>
                                        </div>

                                        <div class="ricevuta-print__note">
                                            {{ $richiesta->compilato_at ? 'Compilazione conclusa: la reception verificherà i dati prima della registrazione definitiva.' : 'Dati salvati anche se incompleti. Puoi riprendere dallo stesso link finché è valido; la reception potrà aiutarti a completarli.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($campiPendentiOspite))<details class="mb-3"><summary>Campi ancora da completare</summary><ul>@foreach($campiPendentiOspite as $pending)<li>{{ $pending }}</li>@endforeach</ul></details>@endif
                            <div class="alert alert-info mb-4">
                                @if(!empty($isLockedAfterConversion))
                                    La reception ha ricevuto e verificato il tuo check-in. Per eventuali correzioni, contatta la struttura.
                                @else
                                    Se devi correggere o aggiornare qualche dato, puoi ancora riaprire il Web Check-in e modificare i tuoi dati prima dell'arrivo.
                                @endif
                            </div>

                            <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                                @if(!empty($editUrl))
                                    <a href="{{ $editUrl }}" class="btn btn-light">Modifica dati</a>
                                @endif
                                <button type="button" class="btn btn-outline-secondary" id="webcheckin-print">
                                    Stampa ricevuta
                                </button>
                                <button type="button" class="btn btn-primary" id="webcheckin-finish-ok">OK</button>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const finishButton = document.getElementById('webcheckin-finish-ok');
        const printButton = document.getElementById('webcheckin-print');

        if (printButton) {
            printButton.addEventListener('click', function () {
                window.print();
            });
        }

        if (!finishButton) return;

        finishButton.addEventListener('click', function () {
            window.close();
            setTimeout(function () {
                window.location.replace('about:blank');
            }, 150);
        });
    });
</script>
<style>
    .ricevuta-print__title {
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
    }

    .ricevuta-print__subtitle {
        color: #6c757d;
        margin-top: 0.15rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .ricevuta-print__logo-wrap {
        text-align: center;
        margin-bottom: 0.75rem;
    }

    .ricevuta-print__logo {
        max-height: 54px;
        max-width: 180px;
        object-fit: contain;
    }

    .ricevuta-print__line {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.4rem 1.25rem;
        font-size: 0.95rem;
        margin-bottom: 0.45rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px solid #eef1f4;
    }

    .ricevuta-print__line--full {
        grid-template-columns: 1fr;
    }

    .ricevuta-print__note {
        padding-top: 0.85rem;
        font-size: 0.95rem;
        color: #495057;
        text-align: center;
    }

    @media print {
        .auth-page-wrapper,
        .auth-page-content,
        .container,
        .row,
        .col-xl-8,
        .col-lg-10 {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .btn,
        .alert,
        .card-header.bg-light-subtle.border-0.py-4.text-center,
        .row.g-3.mb-4 {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 0 !important;
        }

        #webcheckin-ricevuta {
            margin-bottom: 0 !important;
        }

        #webcheckin-ricevuta .card-header {
            padding: 0 0 0.75rem 0 !important;
            background: transparent !important;
        }

        #webcheckin-ricevuta .card-body {
            padding: 0 !important;
        }

        .ricevuta-print__logo {
            max-height: 46px;
            max-width: 160px;
        }

        .ricevuta-print__line {
            grid-template-columns: 1fr 1fr;
            gap: 0.25rem 0.9rem;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            padding-bottom: 0.3rem;
        }

        .ricevuta-print__note {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        body {
            background: #fff !important;
        }

        @page {
            size: A4;
            margin: 12mm;
        }
    }
</style>
@endsection
