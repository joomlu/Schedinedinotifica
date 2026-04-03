@php
    $zoneOptions = collect($zoneOptions ?? []);
    $localitaOptions = collect($localitaOptions ?? []);
@endphp

@push('scripts')
<script>
// Switch tipo_apertura Velzon Minimal + logica calendario
// Gestisce toggle annuale/stagionale e blocca i datepicker quando non servono.
document.addEventListener('DOMContentLoaded', function() {
    var switchInput = document.getElementById('tipoAperturaSwitch');
    var hiddenInput = document.getElementById('tipo_apertura_value');
    var apertura = document.querySelector('input[name="data_apertura"][data-provider="flatpickr"]');
    var chiusura = document.querySelector('input[name="data_chiusura"][data-provider="flatpickr"]');
    var geoRoot = document.querySelector('[data-ui="geo-italia"][data-prefix="geo"]');
    var latInput = document.getElementById('struttura_latitudine');
    var lngInput = document.getElementById('struttura_longitudine');
    var http = window.http || window.axios || null;
    var zonaSelect = document.getElementById('strutturaZonaSelect');
    var localitaSelect = document.getElementById('strutturaLocalitaSelect');
    var passwordToggles = document.querySelectorAll('[data-password-toggle]');
    var comuneSelect = document.getElementById('geo_comune_id');

    function setCalendarState(input, disabled, clearValue) {
        if (!input) return;
        var fp = input._flatpickr || null;

        if (fp) {
            if (clearValue) {
                fp.clear();
            }
            fp.input.disabled = !!disabled;
            fp.set('clickOpens', !disabled);
            fp.input.classList.toggle('calendar-disabled', !!disabled);

            if (fp.altInput) {
                fp.altInput.disabled = !!disabled;
                fp.altInput.classList.toggle('calendar-disabled', !!disabled);
            }
            return;
        }

        if (clearValue) {
            input.value = '';
        }
        input.disabled = !!disabled;
        input.classList.toggle('calendar-disabled', !!disabled);
    }

    function updateTipoApertura() {
        var isAnnuale = switchInput && switchInput.checked;
        hiddenInput.value = isAnnuale ? 'Annuale' : 'Stagionale';
        var msg = document.getElementById('msg-apertura-annuale');
        setCalendarState(apertura, isAnnuale, isAnnuale);
        setCalendarState(chiusura, isAnnuale, isAnnuale);
        if (msg) msg.style.display = isAnnuale ? '' : 'none';
    }

    [apertura, chiusura].forEach(function(input) {
        if (input) {
            input.addEventListener('focus', function(e) {
                if (input.classList.contains('calendar-disabled')) {
                    e.preventDefault();
                    input.blur();
                }
            });
            input.addEventListener('click', function(e) {
                if (input.classList.contains('calendar-disabled')) {
                    e.preventDefault();
                    input.blur();
                }
            });
        }
    });

    if (switchInput && hiddenInput) {
        switchInput.addEventListener('change', updateTipoApertura);
        updateTipoApertura();
    }

    function refreshZoneSuggestions(comuneId) {
        if ((!zonaSelect && !localitaSelect) || !http) {
            return;
        }

        http.get('{{ route('struttura.zone_suggestions') }}', { params: { geo_comune_id: comuneId || '' } })
            .then(function (response) {
                var payload = response && response.data !== undefined ? response.data : response;

                var refreshSelect = function (select, items) {
                    if (!select) return;
                    var currentValue = select.value || '';

                    select.innerHTML = '';
                    select.appendChild(new Option('', '', false, false));

                    (items || []).forEach(function (item) {
                        var exists = Array.from(select.options).some(function (opt) { return opt.value === item; });
                        if (!exists) {
                            select.appendChild(new Option(item, item, false, false));
                        }
                    });

                    if (currentValue) {
                        var found = Array.from(select.options).some(function (opt) { return opt.value === currentValue; });
                        if (!found) {
                            select.appendChild(new Option(currentValue, currentValue, true, true));
                        }
                        select.value = currentValue;
                    }

                    if (window.jQuery) {
                        window.jQuery(select).trigger('change.select2');
                    }
                };

                refreshSelect(zonaSelect, payload && payload.zona ? payload.zona : []);
                refreshSelect(localitaSelect, payload && payload.localita ? payload.localita : []);
            })
            .catch(function () {});
    }

    function hydrateCoordinates(comuneId, force) {
        if (!http || !latInput || !lngInput || !comuneId) {
            return;
        }

        var hasCoordinates = !!String(latInput.value || '').trim() && !!String(lngInput.value || '').trim();
        if (hasCoordinates && !force) {
            return;
        }

        http.get('/geo/resolve', { params: { geo_comune_id: comuneId } })
            .then(function (response) {
                var payload = response && response.data !== undefined ? response.data : response;
                var comune = payload && payload.comune ? payload.comune : null;
                latInput.value = comune && comune.lat !== undefined && comune.lat !== null ? comune.lat : '';
                lngInput.value = comune && comune.lng !== undefined && comune.lng !== null ? comune.lng : '';
            })
            .catch(function () {});
    }

    if (geoRoot && latInput && lngInput && http) {
        geoRoot.addEventListener('geoselect:change', function (event) {
            var comuneId = event && event.detail && event.detail.comune ? event.detail.comune.value : null;

            if (!comuneId) {
                latInput.value = '';
                lngInput.value = '';
            }

            refreshZoneSuggestions(comuneId);

            if (!comuneId) {
                return;
            }

            hydrateCoordinates(comuneId, true);
        });

        if (comuneSelect && comuneSelect.value) {
            refreshZoneSuggestions(comuneSelect.value);
            hydrateCoordinates(comuneSelect.value, false);
        }
    }

    passwordToggles.forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-password-toggle');
            var input = targetId ? document.getElementById(targetId) : null;
            var icon = button.querySelector('i');
            if (!input) return;

            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';

            if (icon) {
                icon.classList.toggle('ri-eye-line', !reveal);
                icon.classList.toggle('ri-eye-off-line', reveal);
            }

            button.setAttribute('aria-label', reveal ? 'Nascondi password' : 'Mostra password');
            button.setAttribute('title', reveal ? 'Nascondi password' : 'Mostra password');
        });
    });
});

</script>
@endpush

    @component('components.breadcrumb')
        @slot('li_1')
            Anagrafica
        @endslot
        @slot('title')
            Dati struttura
        @endslot
    @endcomponent

    <div class="row config-page">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('struttura.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <!-- Identità Struttura -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-building-4-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Identità Struttura</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            @if($struttura->logo)
                                                <img src="{{ asset($struttura->logo) }}" alt="Logo struttura" class="img-fluid mb-3" style="max-height: 90px; display: block;">
                                            @endif
                                        </div>
                                        <x-xx.struttura-tipologia :tipologie-generali="$tipologieGenerali" :tipologie-struttura="$tipologieStruttura" :classificazioni="$classificazioni" :entity="$struttura" />
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label class="form-label">Nome struttura *</label>
                                                <input type="text" name="nome_struttura" class="form-control" required value="{{ old('nome_struttura', $struttura->nome_struttura) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Logo struttura</label>
                                                <input type="file" name="logo" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Apertura / Stagionalità -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-shield-check-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Stato servizio</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="rounded-3 border px-3 py-3 h-100">
                                                    <div class="text-muted small">Servizio</div>
                                                    <div class="fw-semibold mt-1">
                                                        @if($struttura->attiva)
                                                            <span class="badge bg-success-subtle text-success">Attivo</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger">Non attivo</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="rounded-3 border px-3 py-3 h-100">
                                                    <div class="text-muted small">Scadenza servizio</div>
                                                    <div class="fw-semibold mt-1">{{ $struttura->scadenza_servizio?->format('d/m/Y') ?: 'Non impostata' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="rounded-3 border px-3 py-3 h-100">
                                                    <div class="text-muted small">Piano</div>
                                                    <div class="fw-semibold mt-1">{{ $struttura->piano ?: 'Non definito' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="rounded-3 border px-3 py-3 h-100">
                                                    <div class="text-muted small">Pagamento</div>
                                                    <div class="fw-semibold mt-1">{{ $struttura->stato_pagamento ?: 'Non definito' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-3">
                                            Questi dati arrivano dalla gestione del servizio e restano consultabili qui per avere il quadro completo della struttura.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Apertura / Stagionalità -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-calendar-event-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Apertura / Stagionalità</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo apertura *</label><br>
                                                <div class="form-check form-switch form-switch-md d-inline-flex align-items-center">
                                                    <input class="form-check-input" type="checkbox" id="tipoAperturaSwitch" {{ old('tipo_apertura', $struttura->tipo_apertura)==='Annuale' ? 'checked' : '' }}>
                                                    <label class="form-check-label ms-2" for="tipoAperturaSwitch">Annuale</label>
                                                </div>
                                                <input type="hidden" name="tipo_apertura" id="tipo_apertura_value" value="{{ old('tipo_apertura', $struttura->tipo_apertura) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Data apertura</label>
                                                <x-calendario
                                                    name="data_apertura"
                                                    id="data_apertura"
                                                    variant="period-start"
                                                    group="apertura-stagionale"
                                                    class="form-control"
                                                    :value="old('data_apertura', $struttura->data_apertura)"
                                                    placeholder="gg/mm/aaaa"
                                                    title="Data apertura stagionale"
                                                />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Data chiusura</label>
                                                <x-calendario
                                                    name="data_chiusura"
                                                    id="data_chiusura"
                                                    variant="period-end"
                                                    group="apertura-stagionale"
                                                    class="form-control"
                                                    :value="old('data_chiusura', $struttura->data_chiusura)"
                                                    placeholder="gg/mm/aaaa"
                                                    title="Data chiusura stagionale (successiva alla data apertura)"
                                                />
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div id="msg-apertura-annuale" class="alert alert-info py-2 px-3 mb-0" style="display:none; font-size:0.95em;">
                                                    L'apertura annuale non richiede la selezione di un periodo: la struttura è sempre aperta tutto l'anno.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ubicazione -->
                            <div class="col-12">
                                <x-geo.italia
                                    title="Ubicazione"
                                    :required="true"
                                    :value="[
                                        'nazione_text' => old('nazione', $struttura->nazione ?? 'Italia'),
                                        'regione_text' => old('regione', $struttura->regione),
                                        'provincia_text' => old('provincia', $struttura->provincia),
                                        'comune_text' => old('citta', $struttura->citta),
                                        'cap' => old('cap', $struttura->cap),
                                        'cap_text' => old('cap', $struttura->cap),
                                        'manual' => (bool) old('geo_manual', $struttura->geo_manual ?? false),
                                    ]"
                                    :names="[
                                        'nazione_id' => 'nazione',
                                        'regione_id' => 'regione',
                                        'provincia_id' => 'provincia',
                                        'comune_id' => 'citta',
                                        'cap' => 'cap',
                                        'regione_text' => 'regione',
                                        'provincia_text' => 'provincia',
                                        'citta_text' => 'citta',
                                        'cap_text' => 'cap',
                                        'manual_flag' => 'geo_manual',
                                    ]"
                                />
                            </div>

                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-map-pin-user-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Dettagli indirizzo</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($struttura->logo_citta)
                                            <div class="mb-2">
                                                <img src="{{ asset($struttura->logo_citta) }}" alt="Logo città" class="img-fluid mb-3" style="max-height: 75px; display: block;">
                                            </div>
                                        @endif
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label">Logo città</label>
                                                <input type="file" name="logo_citta" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Zona</label>
                                                <x-ui.select id="strutturaZonaSelect" name="zona" data-allow-manual="1">
                                                    <option value=""></option>
                                                    @foreach($zoneOptions as $zonaOption)
                                                        <option value="{{ $zonaOption }}" @selected(old('zona', $struttura->zona) === $zonaOption)>{{ $zonaOption }}</option>
                                                    @endforeach
                                                    @php $zonaCorrente = old('zona', $struttura->zona); @endphp
                                                    @if($zonaCorrente && !$zoneOptions->contains($zonaCorrente))
                                                        <option value="{{ $zonaCorrente }}" selected>{{ $zonaCorrente }}</option>
                                                    @endif
                                                </x-ui.select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Quartiere / Località</label>
                                                <x-ui.select id="strutturaLocalitaSelect" name="localita" data-allow-manual="1">
                                                    <option value=""></option>
                                                    @foreach($localitaOptions as $localitaOption)
                                                        <option value="{{ $localitaOption }}" @selected(old('localita', $struttura->localita) === $localitaOption)>{{ $localitaOption }}</option>
                                                    @endforeach
                                                    @php $localitaCorrente = old('localita', $struttura->localita); @endphp
                                                    @if($localitaCorrente && !$localitaOptions->contains($localitaCorrente))
                                                        <option value="{{ $localitaCorrente }}" selected>{{ $localitaCorrente }}</option>
                                                    @endif
                                                </x-ui.select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Indirizzo *</label>
                                                <input type="text" name="indirizzo" class="form-control" required value="{{ old('indirizzo', $struttura->indirizzo) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Numero civico</label>
                                                <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $struttura->numero_civico) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Latitudine</label>
                                                <input type="text" id="struttura_latitudine" name="latitudine" class="form-control" inputmode="decimal" required value="{{ old('latitudine', $struttura->latitudine) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Longitudine</label>
                                                <input type="text" id="struttura_longitudine" name="longitudine" class="form-control" inputmode="decimal" required value="{{ old('longitudine', $struttura->longitudine) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dati fiscali / amministrativi -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-file-list-2-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Dati fiscali / amministrativi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label form-label-help">CIR
                                                    <x-ui.help
                                                        title="CIR"
                                                        text="Codice identificativo regionale della struttura. Inserisci il codice ufficiale comunicato dall ente competente."
                                                    />
                                                </label>
                                                <input type="text" name="cir" class="form-control text-uppercase" placeholder="Es. CIR-RN-000001" value="{{ old('cir', $struttura->cir) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ragione sociale</label>
                                                <input type="text" name="ragione_sociale" class="form-control" value="{{ old('ragione_sociale', $struttura->ragione_sociale) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Partita IVA</label>
                                                <input type="text" name="partita_iva" class="form-control" inputmode="numeric" maxlength="11" placeholder="11 cifre" value="{{ old('partita_iva', $struttura->partita_iva) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Codice fiscale</label>
                                                <input type="text" name="codice_fiscale" class="form-control text-uppercase" maxlength="16" placeholder="16 caratteri" value="{{ old('codice_fiscale', $struttura->codice_fiscale) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label-help">CIN
                                                    <x-ui.help
                                                        title="CIN"
                                                        text="Codice identificativo nazionale. Copialo come rilasciato ufficialmente, senza spazi inutili."
                                                    />
                                                </label>
                                                <input type="text" name="cin" class="form-control text-uppercase" placeholder="Es. CIN-IT-RN-K2-0001" value="{{ old('cin', $struttura->cin) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label-help">Codice unico
                                                    <x-ui.help
                                                        title="Codice unico"
                                                        text="In Italia il Codice Univoco, o Codice Destinatario, indica dove recapitare la fattura elettronica nel Sistema di Interscambio. Deve avere 7 caratteri alfanumerici: solo lettere maiuscole e numeri."
                                                    />
                                                </label>
                                                <input type="text" name="codice_unico" class="form-control text-uppercase" maxlength="7" placeholder="7 caratteri, es. ABC1234" value="{{ old('codice_unico', $struttura->codice_unico) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Capacità ricettiva -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-hotel-bed-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">
                                            <span class="section-title-help">Capacità ricettiva
                                                <x-ui.help
                                                    title="Camere reali"
                                                    text="Quando attivo, la sezione Camere reali comparira nelle schedine per questa struttura. Funziona solo se anche il flag centrale del gestionale e attivo."
                                                />
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Camere disponibili</label>
                                                <input type="number" name="camere_disponibili" class="form-control" min="0" value="{{ old('camere_disponibili', $struttura->camere_disponibili) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Letti disponibili</label>
                                                <input type="number" name="letti_disponibili" class="form-control" min="0" value="{{ old('letti_disponibili', $struttura->letti_disponibili) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Letti aggiuntivi</label>
                                                <input type="number" name="letti_agg" class="form-control" min="0" value="{{ old('letti_agg', $struttura->letti_agg) }}">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6 d-flex align-items-center">
                                                <input type="hidden" name="camere_reali_enabled" value="0">
                                                <div class="form-check form-switch form-switch-md">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="camere_reali_enabled" id="camereRealiSwitch" value="1" {{ old('camere_reali_enabled', $struttura->camere_reali_enabled) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="camereRealiSwitch">Abilita collegamento camere reali (gestionale)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Riferimenti istituzionali -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-shield-keyhole-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">
                                            <span class="section-title-help">Riferimenti istituzionali
                                                <x-ui.help
                                                    title="Riferimenti istituzionali"
                                                    text="Qui vanno credenziali e codici usati dai canali telematici. Puoi iniziare in simulazione e completare l invio reale piu avanti, senza perdere la configurazione gia preparata."
                                                />
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">ISTAT username</label>
                                                <input type="text" name="istat_username" class="form-control" value="{{ old('istat_username', $struttura->istat_username) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">ISTAT password</label>
                                                <div class="input-group">
                                                    <input type="password" name="istat_password" id="istat_password" class="form-control" value="{{ old('istat_password', $struttura->istat_password) }}" autocomplete="new-password">
                                                    <button type="button" class="btn btn-outline-secondary" data-password-toggle="istat_password" aria-label="Mostra password" title="Mostra password">
                                                        <i class="ri-eye-line align-middle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Codice struttura Ross1000</label>
                                                <input type="text" name="istat_codice_struttura" class="form-control" value="{{ old('istat_codice_struttura', $struttura->istat_codice_struttura) }}">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">URL web service ISTAT</label>
                                                <input type="url" name="istat_ws_url" class="form-control" value="{{ old('istat_ws_url', $struttura->istat_ws_url) }}" placeholder="https://...">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="hidden" name="istat_ws_simulazione" value="0">
                                                <div class="form-check form-switch form-switch-md mt-4">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="istat_ws_simulazione" id="istatWsSimulazione" value="1" {{ old('istat_ws_simulazione', $struttura->istat_ws_simulazione) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="istatWsSimulazione">Modalità prova invio ISTAT</label>
                                                </div>
                                            </div>
                                            <div class="col-12"><hr class="my-2"></div>
                                            <div class="col-md-4">
                                                <label class="form-label">Questura username</label>
                                                <input type="text" name="questura_username" class="form-control" value="{{ old('questura_username', $struttura->questura_username) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Questura password</label>
                                                <div class="input-group">
                                                    <input type="password" name="questura_password" id="questura_password" class="form-control" value="{{ old('questura_password', $struttura->questura_password) }}" autocomplete="new-password">
                                                    <button type="button" class="btn btn-outline-secondary" data-password-toggle="questura_password" aria-label="Mostra password" title="Mostra password">
                                                        <i class="ri-eye-line align-middle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">WSKEY Questura</label>
                                                <input type="text" name="questura_wskey" class="form-control" value="{{ old('questura_wskey', $struttura->questura_wskey) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Codici Questura</label>
                                                <input type="text" name="questura_codici" class="form-control" value="{{ old('questura_codici', $struttura->questura_codici) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">PUK / codice di supporto</label>
                                                <input type="text" name="questura_puk" class="form-control" value="{{ old('questura_puk', $struttura->questura_puk) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="hidden" name="questura_ws_simulazione" value="0">
                                                <div class="form-check form-switch form-switch-md mt-4">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="questura_ws_simulazione" id="questuraWsSimulazione" value="1" {{ old('questura_ws_simulazione', $struttura->questura_ws_simulazione) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="questuraWsSimulazione">Modalità prova invio Questura</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contatti -->
                            <div class="col-12">
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-contacts-book-2-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Contatti</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Telefono *</label>
                                                <input type="tel" name="telefono" class="form-control" inputmode="tel" required value="{{ old('telefono', $struttura->telefono) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cellulare</label>
                                                <input type="tel" name="telefono_secondario" class="form-control" inputmode="tel" value="{{ old('telefono_secondario', $struttura->telefono_secondario) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Email *</label>
                                                <input type="email" name="email" class="form-control" required value="{{ old('email', $struttura->email) }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Fax</label>
                                                <input type="tel" name="fax" class="form-control" inputmode="tel" value="{{ old('fax', $struttura->fax) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Sito web</label>
                                                <input type="url" name="sito_web" class="form-control" value="{{ old('sito_web', $struttura->sito_web) }}" placeholder="https://www.esempio.it">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
