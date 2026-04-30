@php
    $statoPagamento = old('stato_pagamento', $struttura->stato_pagamento ?? 'pagato');
    $avviso = old('avviso', $struttura->avviso ?? 'attivo');
    $statoOnline = (string) old('attiva', $struttura->attiva ?? true);
    $accessoNome = old('accesso_nome', $accessoPrincipale->name ?? '');
    $accessoUsername = old('accesso_username', $accessoPrincipale->username ?? '');
    $accessoEmail = old('accesso_email', $accessoPrincipale->email ?? '');
    $zoneOptions = collect($zoneOptions ?? []);
    $localitaOptions = collect($localitaOptions ?? []);
    $statoPagamentoLabel = in_array($statoPagamento, ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $statoPagamento));
    $activeTab = request('tab', old('messaggio_offline') || old('messaggio_avviso') || old('scadenza_servizio') ? 'servizio' : (old('accesso_username') || old('accesso_password') ? 'accesso' : 'dati'));
    $serviziAttivi = $licenzeStorico->pluck('articolo.nome')->filter()->unique()->values();
    $isCreateMode = ($mode ?? 'edit') === 'create';
    $zoneOptions = $zoneOptions->filter()->values();
    $localitaOptions = $localitaOptions->filter()->values();
    $zonaCorrente = old('zona', $struttura->zona);
    $localitaCorrente = old('localita', $struttura->localita);
    $articoliCatalogo = collect($articoliCatalogo ?? []);
    $servizioCorrenteId = (int) old('articolo_id', optional($licenzeStorico->firstWhere('articolo.parent_id', null))->articolo_id ?? 0);
    $proformeByStruttura = collect($proformeStorico ?? [])->reduce(function ($carry, $proforma) use ($struttura) {
        if (!$carry->has($struttura->id)) {
            $carry->put($struttura->id, $proforma);
        }

        return $carry;
    }, collect());
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Quadro struttura</h5>
        <p class="text-muted mb-0">Sintesi amministrativa immediata della struttura.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Struttura</div>
                    <div class="fw-semibold mt-1">{{ $struttura->nome_struttura ?: 'Nuova struttura' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Proprietario</div>
                    <div class="fw-semibold mt-1">{{ optional($struttura->proprietario)->nome ?: 'Non assegnato' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Servizio</div>
                    <div class="fw-semibold mt-1">{{ $serviziAttivi->join(', ') ?: 'Nessun servizio assegnato' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Piano</div>
                    <div class="fw-semibold mt-1">{{ $struttura->piano ?: 'Non definito' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Pagamento</div>
                    <div class="fw-semibold mt-1">{{ $statoPagamentoLabel }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Prossima scadenza</div>
                    <div class="fw-semibold mt-1">{{ optional($struttura->scadenza_servizio)->format('d/m/Y') ?: 'Non impostata' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Stato</div>
                    <div class="mt-1">
                        <span class="badge {{ ($struttura->attiva ?? true) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            {{ ($struttura->attiva ?? true) ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Avviso</div>
                    <div class="fw-semibold mt-1">{{ ucfirst($struttura->avviso ?? 'attivo') }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Licenze</div>
                    <div class="fw-semibold mt-1">{{ $licenzeStorico->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'dati' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#struttura-admin-pane-dati" type="button" role="tab">Dati struttura</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'servizio' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#struttura-admin-pane-servizio" type="button" role="tab">Servizio</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'accesso' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#struttura-admin-pane-accesso" type="button" role="tab">Accesso</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'storico' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#struttura-admin-pane-storico" type="button" role="tab">Storico</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'dati' ? 'show active' : '' }}" id="struttura-admin-pane-dati" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Anagrafica struttura</h5>
                                <p class="text-muted mb-0">
                                    {{ $isCreateMode ? 'Dati iniziali per creare e assegnare la struttura al proprietario. Il resto può essere completato poi dalla struttura dentro il programma.' : 'Dati letti dalla scheda struttura principale del programma, qui mostrati in sola consultazione.' }}
                                </p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Nome struttura</label>
                                        <input type="text" name="nome_struttura" class="form-control" value="{{ old('nome_struttura', $struttura->nome_struttura) }}" required {{ $isCreateMode ? '' : 'readonly' }}>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Logo struttura</label>
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            @if($struttura->logo)
                                                <img src="{{ asset($struttura->logo) }}" alt="Logo struttura" class="img-thumbnail" style="max-height: 72px;">
                                            @else
                                                <div class="border rounded-3 px-3 py-2 text-muted bg-light-subtle">Nessun logo struttura</div>
                                            @endif
                                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        @if($isCreateMode)
                                            <x-geo.italia
                                                prefix="struttura_geo_admin"
                                                title="GEO struttura"
                                                :required="false"
                                                :value="[
                                                    'nazione_text' => old('nazione', $struttura->nazione ?? 'Italia'),
                                                    'regione_text' => old('regione', $struttura->regione),
                                                    'provincia_text' => old('provincia', $struttura->provincia),
                                                    'comune_text' => old('citta', $struttura->citta),
                                                    'cap' => old('cap', $struttura->cap),
                                                    'cap_text' => old('cap', $struttura->cap),
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
                                        @else
                                            <input type="hidden" name="nazione" value="{{ old('nazione', $struttura->nazione) }}">
                                            <input type="hidden" name="regione" value="{{ old('regione', $struttura->regione) }}">
                                            <input type="hidden" name="provincia" value="{{ old('provincia', $struttura->provincia) }}">
                                            <input type="hidden" name="citta" value="{{ old('citta', $struttura->citta) }}">
                                            <input type="hidden" name="cap" value="{{ old('cap', $struttura->cap) }}">
                                            <div class="border rounded-3 p-3 bg-light-subtle">
                                                <div class="row g-3">
                                                    <div class="col-md-2"><div class="text-muted small">Nazione</div><div class="fw-medium">{{ old('nazione', $struttura->nazione ?: '—') }}</div></div>
                                                    <div class="col-md-3"><div class="text-muted small">Regione</div><div class="fw-medium">{{ old('regione', $struttura->regione ?: '—') }}</div></div>
                                                    <div class="col-md-2"><div class="text-muted small">Provincia</div><div class="fw-medium">{{ old('provincia', $struttura->provincia ?: '—') }}</div></div>
                                                    <div class="col-md-3"><div class="text-muted small">Città</div><div class="fw-medium">{{ old('citta', $struttura->citta ?: '—') }}</div></div>
                                                    <div class="col-md-2"><div class="text-muted small">CAP</div><div class="fw-medium">{{ old('cap', $struttura->cap ?: '—') }}</div></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Dettaglio indirizzo</h5>
                                <p class="text-muted mb-0">Logo città, zona, località e dati di posizione della struttura.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 align-items-start">
                                    <div class="col-lg-3">
                                        @unless($isCreateMode)
                                            <input type="hidden" name="numero_civico" value="{{ old('numero_civico', $struttura->numero_civico) }}">
                                            <input type="hidden" name="indirizzo" value="{{ old('indirizzo', $struttura->indirizzo) }}">
                                            <input type="hidden" name="latitudine" value="{{ old('latitudine', $struttura->latitudine) }}">
                                            <input type="hidden" name="longitudine" value="{{ old('longitudine', $struttura->longitudine) }}">
                                        @endunless
                                        <input type="hidden" id="struttura_admin_logo_citta" name="logo_citta" value="{{ old('logo_citta', $struttura->logo_citta) }}">
                                        <div id="strutturaAdminLogoBox" class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-center bg-light-subtle">
                                            @if($struttura->logo_citta)
                                                <img
                                                    id="strutturaAdminLogoImage"
                                                    src="{{ asset($struttura->logo_citta) }}"
                                                    alt="Logo città"
                                                    class="img-fluid"
                                                    style="max-height: 110px;"
                                                >
                                                <div id="strutturaAdminLogoEmpty" class="text-center text-muted d-none">
                                                    <i class="ri-image-line fs-1 d-block mb-2"></i>
                                                    Nessun logo città
                                                </div>
                                            @else
                                                <img
                                                    id="strutturaAdminLogoImage"
                                                    src=""
                                                    alt="Logo città"
                                                    class="img-fluid d-none"
                                                    style="max-height: 110px;"
                                                >
                                                <div id="strutturaAdminLogoEmpty" class="text-center text-muted">
                                                    <i class="ri-image-line fs-1 d-block mb-2"></i>
                                                    Nessun logo città
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Zona</label>
                                                <x-ui.select id="strutturaZonaSelect" name="zona" data-allow-manual="1">
                                                    <option value=""></option>
                                                    @foreach($zoneOptions as $zonaOption)
                                                        <option value="{{ $zonaOption }}" @selected($zonaCorrente === $zonaOption)>{{ $zonaOption }}</option>
                                                    @endforeach
                                                    @if($zonaCorrente && !$zoneOptions->contains($zonaCorrente))
                                                        <option value="{{ $zonaCorrente }}" selected>{{ $zonaCorrente }}</option>
                                                    @endif
                                                </x-ui.select>
                                                @if($zoneOptions->isNotEmpty())
                                                    <div class="small text-muted mt-2">
                                                        Catalogo zona:
                                                        {{ $zoneOptions->take(8)->join(', ') }}
                                                        @if($zoneOptions->count() > 8)
                                                            …
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Quartiere / Località</label>
                                                <x-ui.select id="strutturaLocalitaSelect" name="localita" data-allow-manual="1">
                                                    <option value=""></option>
                                                    @foreach($localitaOptions as $localitaOption)
                                                        <option value="{{ $localitaOption }}" @selected($localitaCorrente === $localitaOption)>{{ $localitaOption }}</option>
                                                    @endforeach
                                                    @if($localitaCorrente && !$localitaOptions->contains($localitaCorrente))
                                                        <option value="{{ $localitaCorrente }}" selected>{{ $localitaCorrente }}</option>
                                                    @endif
                                                </x-ui.select>
                                                @if($localitaOptions->isNotEmpty())
                                                    <div class="small text-muted mt-2">
                                                        Catalogo località:
                                                        {{ $localitaOptions->take(8)->join(', ') }}
                                                        @if($localitaOptions->count() > 8)
                                                            …
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Numero civico</label>
                                                <input
                                                    type="text"
                                                    name="{{ $isCreateMode ? 'numero_civico' : '' }}"
                                                    class="form-control"
                                                    value="{{ old('numero_civico', $struttura->numero_civico) }}"
                                                    {{ $isCreateMode ? '' : 'readonly' }}
                                                >
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Indirizzo</label>
                                                <input
                                                    type="text"
                                                    name="{{ $isCreateMode ? 'indirizzo' : '' }}"
                                                    class="form-control"
                                                    value="{{ old('indirizzo', $struttura->indirizzo) }}"
                                                    {{ $isCreateMode ? '' : 'readonly' }}
                                                >
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Latitudine</label>
                                                <input
                                                    type="text"
                                                    id="struttura_admin_latitudine"
                                                    name="{{ $isCreateMode ? 'latitudine' : '' }}"
                                                    class="form-control"
                                                    value="{{ old('latitudine', $struttura->latitudine) }}"
                                                    {{ $isCreateMode ? '' : 'readonly' }}
                                                >
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Longitudine</label>
                                                <input
                                                    type="text"
                                                    id="struttura_admin_longitudine"
                                                    name="{{ $isCreateMode ? 'longitudine' : '' }}"
                                                    class="form-control"
                                                    value="{{ old('longitudine', $struttura->longitudine) }}"
                                                    {{ $isCreateMode ? '' : 'readonly' }}
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'servizio' ? 'show active' : '' }}" id="struttura-admin-pane-servizio" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Proprietà e servizio</h5>
                        <p class="text-muted mb-0">Gestione commerciale della struttura, con licenze, stato del servizio, pagamento e comunicazioni.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Proprietario</label>
                                <x-ui.select name="proprietario_id">
                                    <option value="">-- Seleziona --</option>
                                    @foreach($proprietari as $proprietario)
                                        <option value="{{ $proprietario->id }}" {{ (string) old('proprietario_id', $struttura->proprietario_id) === (string) $proprietario->id ? 'selected' : '' }}>
                                            {{ $proprietario->nome }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Servizio</label>
                                <x-ui.select name="articolo_id">
                                    <option value="">-- Seleziona servizio --</option>
                                    @foreach($articoliCatalogo as $articoloCatalogo)
                                        <option value="{{ $articoloCatalogo->id }}" @selected($servizioCorrenteId === (int) $articoloCatalogo->id)>
                                            {{ $articoloCatalogo->nome }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Piano</label>
                                <input type="text" name="piano" class="form-control" value="{{ old('piano', $struttura->piano) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Scadenza servizio</label>
                                <x-calendario name="scadenza_servizio" variant="single" :value="old('scadenza_servizio', $struttura->scadenza_servizio)" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stato pagamento</label>
                                <x-ui.select name="stato_pagamento">
                                    <option value="pagato" {{ in_array($statoPagamento, ['pagato', 'ok'], true) ? 'selected' : '' }}>Pagato</option>
                                    <option value="da_pagare" {{ $statoPagamento === 'da_pagare' ? 'selected' : '' }}>Da pagare</option>
                                    <option value="sospeso" {{ $statoPagamento === 'sospeso' ? 'selected' : '' }}>Sospeso</option>
                                </x-ui.select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stato</label>
                                <x-ui.select name="attiva">
                                    <option value="1" {{ $statoOnline === '1' ? 'selected' : '' }}>Online</option>
                                    <option value="0" {{ $statoOnline === '0' ? 'selected' : '' }}>Offline</option>
                                </x-ui.select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Avviso</label>
                                <x-ui.select name="avviso">
                                    <option value="attivo" {{ $avviso === 'attivo' ? 'selected' : '' }}>Attivo</option>
                                    <option value="sospeso" {{ $avviso === 'sospeso' ? 'selected' : '' }}>Sospeso</option>
                                    <option value="inattivo" {{ $avviso === 'inattivo' ? 'selected' : '' }}>Inattivo</option>
                                </x-ui.select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Messaggio stato Offline</label>
                                <textarea name="messaggio_offline" class="form-control" rows="4" placeholder="Il servizio non è disponibile. Contatta l'amministratore.">{{ old('messaggio_offline', $struttura->messaggio_offline) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Messaggio Avviso</label>
                                <textarea name="messaggio_avviso" class="form-control" rows="4" placeholder="Messaggio da mostrare se lo stato è sospeso o inattivo.">{{ old('messaggio_avviso', $struttura->messaggio_avviso) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'accesso' ? 'show active' : '' }}" id="struttura-admin-pane-accesso" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Accesso struttura</h5>
                        <p class="text-muted mb-0">Credenziali principali di accesso al software della struttura.</p>
                    </div>
                    <div class="card-body">
                        @if($accessoPrincipale)
                            <div class="alert alert-success">
                                Accesso principale collegato:
                                <strong>{{ $accessoPrincipale->username }}</strong>
                                @if($accessoPrincipale->email)
                                    · {{ $accessoPrincipale->email }}
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Nessun accesso principale configurato. Inserisci almeno username e password per crearne uno.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nome accesso</label>
                                <input type="text" name="accesso_nome" class="form-control" value="{{ $accessoNome }}" placeholder="Nome accesso struttura">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="accesso_username" class="form-control" value="{{ $accessoUsername }}" placeholder="username-struttura">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email accesso</label>
                                <input type="email" name="accesso_email" class="form-control" value="{{ $accessoEmail }}" placeholder="struttura@example.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password</label>
                                <div class="position-relative">
                                    <input type="password" name="accesso_password" class="form-control pe-5" id="accesso_password_admin" placeholder="{{ $mode === 'edit' ? 'Lascia vuoto per mantenerla' : 'Minimo 8 caratteri' }}">
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" data-password-toggle="accesso_password_admin" style="height: 100%;">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'storico' ? 'show active' : '' }}" id="struttura-admin-pane-storico" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Storico licenze e pagamenti</h5>
                        <p class="text-muted mb-0">Storico amministrativo completo della struttura, con servizi, licenze, scadenze e stato pagamento.</p>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('admin.pagamenti.index', ['tab' => 'licenze', 'conto_struttura_id' => $struttura->id]) }}" class="btn btn-outline-secondary btn-sm">
                                Apri gestione licenze
                            </a>
                            @if($struttura->proprietario_id)
                                <a href="{{ route('admin.proprietari.proforme.create', ['id' => $struttura->proprietario_id, 'struttura_id' => $struttura->id]) }}" class="btn btn-primary btn-sm">
                                    Nuova proforma
                                </a>
                            @endif
                        </div>
                        @if($licenzeStorico->isEmpty())
                            <div class="text-muted">Nessuna licenza storica disponibile per questa struttura.</div>
                        @else
                            <div class="row g-3">
                                @foreach($licenzeStorico as $licenza)
                                    @php
                                        $proformaLicenza = $licenza->struttura_id ? $proformeByStruttura->get($licenza->struttura_id) : null;
                                        $proformaLicenzaLabel = in_array(($proformaLicenza->stato ?? ''), ['pagata', 'fatturata', 'ok'], true) ? 'Pagata' : 'Proforma';
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $licenza->numero_licenza ?: '—' }}</div>
                                                    <div class="text-muted small">{{ $licenza->articolo->nome ?? $licenza->articolo->codice ?? 'Piano non definito' }}</div>
                                                </div>
                                                <span class="badge {{ $licenza->attiva ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                    {{ $licenza->attiva ? 'Attiva' : 'Non attiva' }}
                                                </span>
                                            </div>
                                            <div class="row g-2 mt-2">
                                                <div class="col-6">
                                                    <div class="text-muted small">Dal</div>
                                                    <div class="fw-medium">{{ optional($licenza->data_inizio)->format('d/m/Y') ?: '—' }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Al</div>
                                                    <div class="fw-medium">{{ optional($licenza->data_scadenza)->format('d/m/Y') ?: '—' }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Pagamento</div>
                                                    <div class="fw-medium">{{ in_array(($licenza->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $licenza->stato_pagamento ?? 'pagato')) }}</div>
                                                    @if(($proformaLicenza ?? null) && in_array(($proformaLicenza->stato ?? ''), ['pagata', 'fatturata', 'ok'], true))
                                                        <div class="small text-muted">
                                                            {{ optional($proformaLicenza->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                                                            @if($proformaLicenza->numero_fattura)
                                                                · Fatt. {{ $proformaLicenza->numero_fattura }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Prezzo</div>
                                                    <div class="fw-medium">{{ number_format((float) $licenza->prezzo, 2, ',', '.') }} €</div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-3">
                                                    <a href="{{ route('admin.pagamenti.licenze.print', $licenza->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                        Apri licenza
                                                    </a>
                                                    @if($proformaLicenza)
                                                        <a href="{{ route('admin.proprietari.proforme.show', ['id' => $struttura->proprietario_id, 'fatturazione' => $proformaLicenza->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                            {{ in_array(($proformaLicenza->stato ?? ''), ['pagata', 'fatturata', 'ok'], true) ? 'Pagata' : 'Proforma' }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Storico proforme e pagamenti</h6>
                                <p class="text-muted mb-0">Documenti emessi per questa struttura, con riferimento al pagamento e alla proforma.</p>
                            </div>
                        </div>

                        @if(($proformeStorico ?? collect())->isEmpty())
                            <div class="text-muted">Nessuna proforma o storico pagamento collegato a questa struttura.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>N. documento</th>
                                            <th>Data</th>
                                            <th>Stato</th>
                                            <th>Intestazione</th>
                                            <th>Totale</th>
                                            <th class="text-end">Proforma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($proformeStorico as $proforma)
                                            @php
                                                $proformaLabel = in_array(($proforma->stato ?? ''), ['pagata', 'fatturata', 'ok'], true) ? 'Pagata' : 'Proforma';
                                            @endphp
                                            <tr>
                                                <td>{{ $proforma->numero ?: '—' }}</td>
                                                <td>{{ optional($proforma->data_documento)->format('d/m/Y') ?: '—' }}</td>
                                                <td>{{ $proformaLabel }}</td>
                                                <td>{{ $proforma->intestazione ?: ($proforma->proprietario->ragione_sociale ?? $proforma->proprietario->nome ?? '—') }}</td>
                                                <td>{{ number_format((float) $proforma->totale, 2, ',', '.') }} €</td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.proprietari.proforme.show', ['id' => $struttura->proprietario_id, 'fatturazione' => $proforma->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                        {{ $proformaLabel }}
                                                    </a>
                                                    @if($proformaLabel === 'Pagata' && ($proforma->data_pagamento || $proforma->numero_fattura))
                                                        <div class="small text-muted mt-1">
                                                            {{ optional($proforma->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                                                            @if($proforma->numero_fattura)
                                                                · Fatt. {{ $proforma->numero_fattura }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($isCreateMode)
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var geoRoot = document.querySelector('[data-ui="geo-italia"][data-prefix="struttura_geo_admin"]');
            var comuneSelect = document.getElementById('struttura_geo_admin_comune_id');
            var latInput = document.getElementById('struttura_admin_latitudine');
            var lngInput = document.getElementById('struttura_admin_longitudine');
            var logoInput = document.getElementById('struttura_admin_logo_citta');
            var logoImage = document.getElementById('strutturaAdminLogoImage');
            var logoEmpty = document.getElementById('strutturaAdminLogoEmpty');
            var zonaSelect = document.getElementById('strutturaZonaSelect');
            var localitaSelect = document.getElementById('strutturaLocalitaSelect');
            var http = window.http || window.axios || null;
            var appBaseUrl = @json(url('/'));

            if (!geoRoot || !comuneSelect || !http) {
                return;
            }

            function normalizeAssetPath(path) {
                if (!path) {
                    return '';
                }

                if (/^https?:\/\//i.test(path) || path.startsWith('/')) {
                    return path;
                }

                return appBaseUrl.replace(/\/$/, '') + '/' + String(path).replace(/^\//, '');
            }

            function updateLogoPreview(path) {
                if (!logoImage || !logoEmpty) {
                    return;
                }

                var resolvedPath = normalizeAssetPath(path);

                if (!resolvedPath) {
                    if (logoInput) {
                        logoInput.value = '';
                    }
                    logoImage.src = '';
                    logoImage.classList.add('d-none');
                    logoEmpty.classList.remove('d-none');
                    return;
                }

                if (logoInput) {
                    logoInput.value = path;
                }
                logoImage.src = resolvedPath;
                logoImage.classList.remove('d-none');
                logoEmpty.classList.add('d-none');
            }

            function refreshSelectOptions(select, items) {
                if (!select) {
                    return;
                }

                var currentValue = select.value || '';
                select.innerHTML = '';
                select.appendChild(new Option('', '', false, false));

                (items || []).forEach(function (item) {
                    var exists = Array.from(select.options).some(function (option) {
                        return option.value === item;
                    });

                    if (!exists) {
                        select.appendChild(new Option(item, item, false, false));
                    }
                });

                if (currentValue) {
                    var found = Array.from(select.options).some(function (option) {
                        return option.value === currentValue;
                    });

                    if (!found) {
                        select.appendChild(new Option(currentValue, currentValue, true, true));
                    }

                    select.value = currentValue;
                }

                if (window.jQuery) {
                    window.jQuery(select).trigger('change.select2');
                }
            }

            function refreshZoneSuggestions(comuneId) {
                return http.get(@json(route('struttura.zone_suggestions')), {
                    params: { geo_comune_id: comuneId || '' }
                }).then(function (response) {
                    var payload = response && response.data !== undefined ? response.data : response;
                    refreshSelectOptions(zonaSelect, payload && payload.zona ? payload.zona : []);
                    refreshSelectOptions(localitaSelect, payload && payload.localita ? payload.localita : []);
                }).catch(function () {});
            }

            function hydrateComuneDetails(comuneId, force) {
                if (!comuneId) {
                    if (latInput) latInput.value = '';
                    if (lngInput) lngInput.value = '';
                    updateLogoPreview('');
                    return Promise.resolve();
                }

                var hasCoordinates = latInput && lngInput
                    ? !!String(latInput.value || '').trim() && !!String(lngInput.value || '').trim()
                    : false;

                return http.get('/geo/resolve', {
                    params: { geo_comune_id: comuneId }
                }).then(function (response) {
                    var payload = response && response.data !== undefined ? response.data : response;
                    var comune = payload && payload.comune ? payload.comune : null;

                    if (!comune) {
                        return;
                    }

                    if ((force || !hasCoordinates) && latInput) {
                        latInput.value = comune.lat !== undefined && comune.lat !== null ? comune.lat : '';
                    }

                    if ((force || !hasCoordinates) && lngInput) {
                        lngInput.value = comune.lng !== undefined && comune.lng !== null ? comune.lng : '';
                    }

                    updateLogoPreview(comune.logo_citta || comune.logo || '');
                }).catch(function () {});
            }

            geoRoot.addEventListener('geoselect:change', function (event) {
                var comuneId = event && event.detail && event.detail.comune ? event.detail.comune.value : null;
                refreshZoneSuggestions(comuneId);
                hydrateComuneDetails(comuneId, true);
            });

            if (comuneSelect.value) {
                refreshZoneSuggestions(comuneSelect.value);
                hydrateComuneDetails(comuneSelect.value, false);
            }
        });
        </script>
    @endpush
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        var targetId = button.getAttribute('data-password-toggle');
                        var input = document.getElementById(targetId);
                        if (!input) return;
                        input.type = input.type === 'password' ? 'text' : 'password';
                    });
                });
            });
        </script>
    @endpush
@endonce
