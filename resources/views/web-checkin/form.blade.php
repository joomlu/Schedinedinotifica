@extends('layouts.master')
@section('title', 'Web Check-in')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Schedine
    @endslot
    @slot('title')
        {{ $richiesta->exists ? 'Gestione Web Check-in' : 'Nuovo Web Check-in' }}
    @endslot
@endcomponent

@php
    $statoLabel = $richiesta->exists ? str_replace('_', ' ', $richiesta->stato) : 'da creare';
    $statoClass = match($richiesta->stato ?? null) {
        'compilato' => 'success',
        'convertito' => 'primary',
        'in_compilazione' => 'info',
        default => 'warning',
    };
@endphp

@if($richiesta->exists)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-xl-2 col-md-4">
                            <div class="text-muted small">Richiesta</div>
                            <div class="fs-6">{{ $richiesta->codice }}</div>
                        </div>
                        <div class="col-xl-3 col-md-4">
                            <div class="text-muted small">Prenotazione</div>
                            <div class="fs-6">{{ $richiesta->numero_prenotazione }}</div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="text-muted small">Persone</div>
                            <div class="fs-6">{{ $richiesta->quantita_persone }}</div>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <div class="text-muted small">Schedina web</div>
                            <div class="fs-6">{{ $richiesta->schedina->scheda ?? '-' }}</div>
                        </div>
                        <div class="col-xl-3 col-md-8">
                            <div class="text-muted small">Stato</div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-{{ $statoClass }}-subtle text-{{ $statoClass }} text-uppercase">{{ $statoLabel }}</span>
                                @if($richiesta->schedina && ($richiesta->stato ?? null) !== 'convertito')
                                    <span class="badge bg-success-subtle text-success">Compilata nel web</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
        <i class="ri-route-line me-2 text-primary"></i>
        <h4 class="card-title mb-0">{{ $richiesta->exists ? 'Gestione Web Check-in' : 'Nuovo Web Check-in' }}</h4>
    </div>
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="webcheckin-tab-richiesta" data-bs-toggle="pill" data-bs-target="#webcheckin-pane-richiesta" type="button" role="tab" aria-controls="webcheckin-pane-richiesta" aria-selected="true">Richiesta</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="webcheckin-tab-accesso" data-bs-toggle="pill" data-bs-target="#webcheckin-pane-accesso" type="button" role="tab" aria-controls="webcheckin-pane-accesso" aria-selected="false">Accesso e stato</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="webcheckin-tab-comunicazioni" data-bs-toggle="pill" data-bs-target="#webcheckin-pane-comunicazioni" type="button" role="tab" aria-controls="webcheckin-pane-comunicazioni" aria-selected="false">Comunicazioni</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="webcheckin-pane-richiesta" role="tabpanel" aria-labelledby="webcheckin-tab-richiesta">
                <form method="POST" action="{{ $richiesta->exists ? route('web_checkin.update', ['id' => $richiesta->id]) : route('web_checkin.store') }}">
                    @csrf
                    @if($richiesta->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-bookmark-3-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Identificazione richiesta</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Numero prenotazione *</label>
                                            <input type="text" name="numero_prenotazione" class="form-control" value="{{ old('numero_prenotazione', $richiesta->numero_prenotazione) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Referente *</label>
                                            <input type="text" name="nome_referente" class="form-control" value="{{ old('nome_referente', $richiesta->nome_referente) }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-mail-open-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Canali di contatto</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email *</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $richiesta->email) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">WhatsApp</label>
                                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $richiesta->whatsapp) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 bg-light-subtle mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-calendar-check-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Periodo soggiorno e note</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-xl-3 col-md-4">
                                            <label class="form-label">Arrivo *</label>
                                            <x-calendario name="arrivo" variant="period-start" group="web-checkin" :value="old('arrivo', optional($richiesta->arrivo)->format('Y-m-d'))" required />
                                        </div>
                                        <div class="col-xl-3 col-md-4">
                                            <label class="form-label">Partenza *</label>
                                            <x-calendario name="partenza" variant="period-end" group="web-checkin" :value="old('partenza', optional($richiesta->partenza)->format('Y-m-d'))" required />
                                        </div>
                                        <div class="col-xl-2 col-md-4">
                                            <label class="form-label">Quantità persone *</label>
                                            <input type="number" name="quantita_persone" class="form-control" min="1" max="50" value="{{ old('quantita_persone', $richiesta->quantita_persone ?: 1) }}" required>
                                        </div>
                                        <div class="col-xl-4 col-md-12">
                                            <label class="form-label">Note interne</label>
                                            <textarea name="note" class="form-control" rows="2">{{ old('note', $richiesta->note) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('schedina.web') }}" class="btn btn-light">Chiudi</a>
                        <button type="submit" class="btn btn-primary">{{ $richiesta->exists ? 'Aggiorna richiesta' : 'Crea richiesta' }}</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="webcheckin-pane-accesso" role="tabpanel" aria-labelledby="webcheckin-tab-accesso">
                @if($richiesta->exists)
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light-subtle mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-links-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Pagina cliente Web Check-in</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="rounded-3 border bg-white p-3 mb-3">
                                        <div class="fw-semibold mb-1">Invita il cliente a completare il Web Check-in online</div>
                                        <div class="text-muted small">Usa il link breve qui sotto oppure apri direttamente il programma di mail del computer con il messaggio gia pronto.</div>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Link breve</span>
                                        <input type="text" class="form-control" id="webcheckin-public-link" value="{{ $publicUrl }}" readonly>
                                        <button type="button" class="btn btn-soft-dark" id="copy-webcheckin-public-link">Copia link</button>
                                        <a href="{{ $publicUrl }}" target="_blank" class="btn btn-soft-primary">Apri pagina cliente</a>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ $mailToUrl ?: '#' }}" class="btn btn-soft-success js-webcheckin-email" data-has-email="{{ $mailToUrl ? '1' : '0' }}">
                                            <i class="ri-mail-send-line align-middle me-1"></i> Apri email nel programma di posta
                                        </a>
                                        <a href="{{ $whatsAppUrl ?: '#' }}" target="_blank" rel="noopener" class="btn btn-soft-success js-webcheckin-whatsapp" data-has-whatsapp="{{ $whatsAppUrl ? '1' : '0' }}">
                                            <i class="ri-whatsapp-line align-middle me-1"></i> Apri WhatsApp esterno
                                        </a>
                                        <button type="button" class="btn btn-soft-secondary" id="copy-webcheckin-email-text">Copia testo email</button>
                                        <button type="button" class="btn btn-soft-secondary" id="copy-webcheckin-whatsapp-text">Copia testo WhatsApp</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-pulse-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Stato operativo</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="mb-3"><span class="badge bg-{{ $statoClass }}-subtle text-{{ $statoClass }} text-uppercase">{{ $statoLabel }}</span></div>
                                    <div class="small text-muted">
                                        @if($richiesta->ultimo_accesso_at)
                                            Ultimo accesso: {{ $richiesta->ultimo_accesso_at->format('d/m/Y H:i') }}<br>
                                        @endif
                                        @if($richiesta->compilato_at)
                                            Compilato il: {{ $richiesta->compilato_at->format('d/m/Y H:i') }}<br>
                                        @endif
                                        @if($richiesta->convertito_at)
                                            Convertito il: {{ $richiesta->convertito_at->format('d/m/Y H:i') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card border-0 bg-light-subtle h-100 mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-file-list-3-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Schedina web collegata</h5>
                                </div>
                                <div class="card-body pt-2">
                                    @if($richiesta->schedina)
                                        <div class="row g-3 mb-3">
                                            <div class="col-4">
                                                <div class="text-muted small">Numero</div>
                                                <div>{{ $richiesta->schedina->scheda ?: '-' }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted small">Arrivo</div>
                                                <div>{{ $richiesta->schedina->arrive ?: '-' }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted small">Partenza</div>
                                                <div>{{ $richiesta->schedina->departure ?: '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('schedina.edit', ['id' => $richiesta->schedina->id]) }}" class="btn btn-soft-info btn-sm">Apri schedina web</a>
                                        </div>
                                    @else
                                        <div class="text-muted">Non ancora generata.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-0">Salva prima la richiesta per generare il link pubblico e la schedina web collegata.</div>
                @endif
            </div>

            <div class="tab-pane fade" id="webcheckin-pane-comunicazioni" role="tabpanel" aria-labelledby="webcheckin-tab-comunicazioni">
                @if($richiesta->exists)
                    @php
                        $previewStruttura = $richiesta->struttura?->nome_struttura ?: 'Struttura';
                    @endphp
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm overflow-hidden mb-0">
                                <div class="card-header bg-light-subtle border-0 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if(!empty($richiesta->struttura?->logo))
                                            <img src="{{ asset($richiesta->struttura->logo) }}" alt="Logo struttura" style="max-height: 52px; max-width: 180px; object-fit: contain;">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">Comunicazione Web Check-in</div>
                                            <div class="small text-muted">Anteprima del tono e del contenuto che il cliente ricevera tramite mail o WhatsApp esterni.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-xl-6">
                                            <div class="rounded-3 border bg-body-secondary p-3 h-100">
                                                <div class="small text-uppercase text-muted mb-2">Preview email</div>
                                                <div class="fw-semibold mb-2">{{ $mailSubject }}</div>
                                                <div class="small text-muted mb-2">Da: Reception {{ $previewStruttura }}</div>
                                                <div class="border rounded bg-white p-3 small" style="white-space: pre-line;">{{ $mailBody }}</div>
                                                <div class="small text-muted mt-2">Questa e la versione testuale reale che verra aperta nel programma di posta del computer.</div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="rounded-3 border bg-body-secondary p-3 h-100">
                                                <div class="small text-uppercase text-muted mb-2">Preview WhatsApp</div>
                                                <div class="border rounded bg-white p-3 small" style="white-space: pre-line;">{{ $whatsappBody }}</div>
                                                <div class="small text-muted mt-2">Questa e la versione testuale reale che verra aperta in WhatsApp esterno.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 bg-light-subtle mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-mail-send-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Email pronta per l'invio</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="mb-3">
                                        <label class="form-label">Oggetto</label>
                                        <input type="text" class="form-control" id="webcheckin-mail-subject" value="{{ $mailSubject }}" readonly>
                                    </div>
                                    <div>
                                        <label class="form-label">Testo email</label>
                                        <textarea class="form-control" id="webcheckin-mail-body" rows="10" readonly>{{ $mailBody }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 bg-light-subtle mb-0">
                                <div class="card-header border-0 py-2 d-flex align-items-center">
                                    <i class="ri-whatsapp-line me-2 text-primary"></i>
                                    <h5 class="card-title mb-0 fs-6">Messaggio rapido WhatsApp</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <textarea class="form-control" id="webcheckin-whatsapp-body" rows="4" readonly>{{ $whatsappBody }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-0">Le comunicazioni verranno generate dopo il salvataggio della richiesta.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showMissingDataAlert = function (text) {
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Dato mancante',
                    text: text,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            window.alert(text);
        };

        const copyButton = document.getElementById('copy-webcheckin-public-link');
        const copyInput = document.getElementById('webcheckin-public-link');
        const copyEmailButton = document.getElementById('copy-webcheckin-email-text');
        const copyWhatsappButton = document.getElementById('copy-webcheckin-whatsapp-text');
        const mailSubject = document.getElementById('webcheckin-mail-subject');
        const mailBody = document.getElementById('webcheckin-mail-body');
        const whatsappBody = document.getElementById('webcheckin-whatsapp-body');

        document.querySelectorAll('.js-webcheckin-email').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (button.dataset.hasEmail === '1') return;
                event.preventDefault();
                showMissingDataAlert('Questo indirizzo email non risulta caricato nel sistema.');
            });
        });

        document.querySelectorAll('.js-webcheckin-whatsapp').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (button.dataset.hasWhatsapp === '1') return;
                event.preventDefault();
                showMissingDataAlert('Questo numero WhatsApp non risulta caricato nel sistema.');
            });
        });

        if (copyButton && copyInput) {
            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(copyInput.value);
                    copyButton.classList.remove('btn-soft-dark');
                    copyButton.classList.add('btn-soft-success');
                    copyButton.textContent = 'Link copiato';
                    setTimeout(function () {
                        copyButton.classList.remove('btn-soft-success');
                        copyButton.classList.add('btn-soft-dark');
                        copyButton.textContent = 'Copia link';
                    }, 1600);
                } catch (error) {
                    window.prompt('Copia il link:', copyInput.value);
                }
            });
        }

        if (copyEmailButton && mailSubject && mailBody) {
            copyEmailButton.addEventListener('click', async function () {
                const emailText = 'Oggetto: ' + mailSubject.value + '\n\n' + mailBody.value;
                try {
                    await navigator.clipboard.writeText(emailText);
                    copyEmailButton.classList.remove('btn-soft-secondary');
                    copyEmailButton.classList.add('btn-soft-success');
                    copyEmailButton.textContent = 'Testo copiato';
                    setTimeout(function () {
                        copyEmailButton.classList.remove('btn-soft-success');
                        copyEmailButton.classList.add('btn-soft-secondary');
                        copyEmailButton.textContent = 'Copia testo email';
                    }, 1600);
                } catch (error) {
                    window.prompt('Copia il testo email:', emailText);
                }
            });
        }

        if (copyWhatsappButton && whatsappBody) {
            copyWhatsappButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(whatsappBody.value);
                    copyWhatsappButton.classList.remove('btn-soft-secondary');
                    copyWhatsappButton.classList.add('btn-soft-success');
                    copyWhatsappButton.textContent = 'Testo copiato';
                    setTimeout(function () {
                        copyWhatsappButton.classList.remove('btn-soft-success');
                        copyWhatsappButton.classList.add('btn-soft-secondary');
                        copyWhatsappButton.textContent = 'Copia testo WhatsApp';
                    }, 1600);
                } catch (error) {
                    window.prompt('Copia il testo WhatsApp:', whatsappBody.value);
                }
            });
        }
    });
</script>
@endpush
