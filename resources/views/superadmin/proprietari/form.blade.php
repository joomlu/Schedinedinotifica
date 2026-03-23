@extends('layouts.master')
@section('title') Proprietari @endsection

@php
    $activeTab = old('active_tab', request('tab', 'personale'));
    $areaLabel = $areaLabel ?? 'SuperAdmin';
    $ownerRoutePrefix = $ownerRoutePrefix ?? 'superadmin.proprietari';
    $strutturaRoutePrefix = $strutturaRoutePrefix ?? 'superadmin.strutture';
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel }} @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo proprietario' : 'Modifica proprietario' }} @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Controlla i dati del proprietario.</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form id="owner-main-form" method="POST" action="{{ $mode === 'create' ? route($ownerRoutePrefix . '.store') : route($ownerRoutePrefix . '.update', $proprietario->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif
                <input type="hidden" name="active_tab" id="owner_active_tab" value="{{ $activeTab }}">

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Strutture totali</div>
                            <div class="display-6 mb-0">{{ $summary['strutture'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Strutture attive</div>
                            <div class="display-6 mb-0">{{ $summary['strutture_attive'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Utenti collegati</div>
                            <div class="display-6 mb-0">{{ $summary['utenti'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Servizi attivi</div>
                            <div class="display-6 mb-0">{{ $summary['servizi_attivi'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs nav-tabs-custom flex-nowrap overflow-auto mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'personale' ? 'active' : '' }}" id="owner-tab-personale" data-bs-toggle="tab" data-bs-target="#owner-pane-personale" type="button" role="tab">Proprietario</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'ubicazione' ? 'active' : '' }}" id="owner-tab-ubicazione" data-bs-toggle="tab" data-bs-target="#owner-pane-ubicazione" type="button" role="tab">Ubicazione</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'fiscale' ? 'active' : '' }}" id="owner-tab-fiscale" data-bs-toggle="tab" data-bs-target="#owner-pane-fiscale" type="button" role="tab">Fiscale</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'admin' ? 'active' : '' }}" id="owner-tab-admin" data-bs-toggle="tab" data-bs-target="#owner-pane-admin" type="button" role="tab">Amministratore</button>
                    </li>
                    @if($mode === 'edit')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'strutture' ? 'active' : '' }}" id="owner-tab-strutture" data-bs-toggle="tab" data-bs-target="#owner-pane-strutture" type="button" role="tab">Strutture</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'servizi' ? 'active' : '' }}" id="owner-tab-servizi" data-bs-toggle="tab" data-bs-target="#owner-pane-servizi" type="button" role="tab">Servizio</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'fatturazione' ? 'active' : '' }}" id="owner-tab-fatturazione" data-bs-toggle="tab" data-bs-target="#owner-pane-fatturazione" type="button" role="tab">Proforme</button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade {{ $activeTab === 'personale' ? 'show active' : '' }}" id="owner-pane-personale" role="tabpanel">
                        <div class="border rounded-3 p-3">
                            <h5 class="mb-1">Proprietario</h5>
                            <div class="text-muted mb-3">Qui salvi i dati anagrafici e i riferimenti principali del proprietario.</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome</label>
                                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $proprietario->nome) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $proprietario->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $proprietario->telefono) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PEC</label>
                                    <input type="email" name="pec" class="form-control" value="{{ old('pec', $proprietario->pec) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'ubicazione' ? 'show active' : '' }}" id="owner-pane-ubicazione" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12">
                                <x-geo.italia
                                    title="Ubicazione proprietario"
                                    prefix="owner_geo"
                                    :required="false"
                                    :value="[
                                        'nazione_text' => old('nazione', $proprietario->nazione ?? 'Italia'),
                                        'regione_text' => old('regione', $proprietario->regione),
                                        'provincia_text' => old('provincia', $proprietario->provincia),
                                        'comune_text' => old('citta', $proprietario->citta),
                                        'cap' => old('cap', $proprietario->cap),
                                        'cap_text' => old('cap', $proprietario->cap),
                                        'manual' => (bool) old('geo_manual', $proprietario->geo_manual ?? false),
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
                                <div class="card mb-0 border-0 shadow-sm">
                                    <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                        <i class="ri-map-pin-user-line me-2 text-primary"></i>
                                        <h5 class="card-title mb-0">Dettagli indirizzo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Indirizzo</label>
                                                <input type="text" name="indirizzo" class="form-control" value="{{ old('indirizzo', $proprietario->indirizzo) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Numero civico</label>
                                                <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $proprietario->numero_civico) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Latitudine</label>
                                                <input type="text" id="owner_latitudine" name="latitudine" class="form-control" value="{{ old('latitudine', $proprietario->latitudine) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Longitudine</label>
                                                <input type="text" id="owner_longitudine" name="longitudine" class="form-control" value="{{ old('longitudine', $proprietario->longitudine) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'fiscale' ? 'show active' : '' }}" id="owner-pane-fiscale" role="tabpanel">
                        <div class="card mb-0 border-0 shadow-sm">
                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                <i class="ri-file-list-2-line me-2 text-primary"></i>
                                <h5 class="card-title mb-0">Dati fiscali / amministrativi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Ragione sociale</label>
                                        <input type="text" name="ragione_sociale" class="form-control" value="{{ old('ragione_sociale', $proprietario->ragione_sociale) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Partita IVA</label>
                                        <input type="text" name="partita_iva" class="form-control" value="{{ old('partita_iva', $proprietario->partita_iva) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Codice fiscale</label>
                                        <input type="text" name="codice_fiscale" class="form-control" value="{{ old('codice_fiscale', $proprietario->codice_fiscale) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Codice destinatario</label>
                                        <input type="text" name="codice_destinatario" class="form-control" value="{{ old('codice_destinatario', $proprietario->codice_destinatario) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Codice unico</label>
                                        <input type="text" name="codice_unico" class="form-control" value="{{ old('codice_unico', $proprietario->codice_unico) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Note amministrative</label>
                                        <textarea name="note_amministrative" class="form-control" rows="2">{{ old('note_amministrative', $proprietario->note_amministrative) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'admin' ? 'show active' : '' }}" id="owner-pane-admin" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-header border-0 bg-light-subtle">
                                <div class="fw-semibold">Amministratore assegnato</div>
                                <div class="small text-muted">Qui consulti il referente che segue questo proprietario.</div>
                            </div>
                            <div class="card-body">
                                @if($proprietario->admin)
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                <div class="text-muted small text-uppercase mb-1">Referente</div>
                                                <div class="fw-semibold">{{ $proprietario->admin->name }}</div>
                                                <div class="small text-muted mt-2">{{ $proprietario->admin->qualifica ?: 'Qualifica non impostata' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                <div class="text-muted small text-uppercase mb-1">Contatti</div>
                                                <div class="small">{{ $proprietario->admin->email ?: 'Nessuna email' }}</div>
                                                <div class="small text-muted">{{ $proprietario->admin->telefono ?: 'Telefono non impostato' }}</div>
                                                <div class="small text-muted">{{ collect([$proprietario->admin->citta, $proprietario->admin->provincia])->filter()->join(' - ') ?: 'Località non impostata' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-light border mb-0">Nessun amministratore assegnato.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($mode === 'edit')
                        <div class="tab-pane fade {{ $activeTab === 'strutture' ? 'show active' : '' }}" id="owner-pane-strutture" role="tabpanel">
                            <div class="border rounded-3 p-3">
                                <h5 class="mb-1">Strutture del proprietario</h5>
                                <div class="text-muted mb-3">Qui leggi tutte le strutture collegate a questo proprietario. Sono la base operativa e il perimetro su cui si appoggiano anche i servizi per struttura.</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                            <div class="text-muted small text-uppercase mb-1">Strutture collegate</div>
                                            <div class="fs-3 mb-1">{{ $proprietario->strutture->count() }}</div>
                                            <div class="small text-muted">Elenco attuale del proprietario.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                            <div class="text-muted small text-uppercase mb-1">Strutture attive</div>
                                            <div class="fs-3 mb-1">{{ $proprietario->strutture->where('attiva', true)->count() }}</div>
                                            <div class="small text-muted">Strutture oggi operative.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                            <div class="text-muted small text-uppercase mb-1">Disponibili da collegare</div>
                                            <div class="fs-3 mb-1">{{ ($struttureDisponibili ?? collect())->count() }}</div>
                                            <div class="small text-muted">Strutture libere o da riassegnare.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-6">
                                        <div class="card border shadow-sm h-100 mb-0">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Assegna struttura esistente</div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3 align-items-end">
                                                    <div class="col-12">
                                                        <label class="form-label">Struttura da collegare</label>
                                                        <select name="struttura_id" class="form-select">
                                                            <option value="">Seleziona struttura</option>
                                                            @foreach(($struttureDisponibili ?? collect()) as $strutturaDisponibile)
                                                                <option value="{{ $strutturaDisponibile->id }}">
                                                                    {{ $strutturaDisponibile->nome_struttura }}
                                                                    @if($strutturaDisponibile->proprietario)
                                                                        - ora di {{ $strutturaDisponibile->proprietario->nome }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <button
                                                            type="submit"
                                                            class="btn btn-primary"
                                                            formaction="{{ route($ownerRoutePrefix . '.assegna_struttura', $proprietario->id) }}"
                                                            formmethod="POST"
                                                            name="active_tab"
                                                            value="strutture"
                                                        >
                                                            Assegna struttura
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card border shadow-sm h-100 mb-0">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Crea nuova struttura</div>
                                            </div>
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div class="text-muted mb-3">Se devi creare una struttura nuova per questo proprietario, la apri già preassegnata e poi torni qui automaticamente.</div>
                                                <div>
                                                    <button type="button" class="btn btn-outline-primary" onclick="window.location.href='{{ route($strutturaRoutePrefix . '.create', ['proprietario_id' => $proprietario->id, 'return_to_owner_id' => $proprietario->id]) }}'">Crea e assegna struttura</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Struttura</th>
                                                <th>Città</th>
                                                <th>Provincia</th>
                                                <th>Piano</th>
                                                <th>Stato pagamento</th>
                                                <th>Attiva</th>
                                                <th class="text-end">Gestione</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($proprietario->strutture as $struttura)
                                                <tr class="js-owner-struttura-row" data-href="{{ route($strutturaRoutePrefix . '.edit', ['id' => $struttura->id, 'return_to_owner_id' => $proprietario->id]) }}" style="cursor: pointer;">
                                                    <td>
                                                        <div class="fw-semibold">{{ $struttura->nome_struttura }}</div>
                                                        <div class="small text-muted">ID {{ $struttura->id }}</div>
                                                    </td>
                                                    <td>{{ $struttura->citta ?: '—' }}</td>
                                                    <td>{{ $struttura->provincia ?: '—' }}</td>
                                                    <td>{{ $struttura->piano ?: '—' }}</td>
                                                    <td>{{ $struttura->stato_pagamento ?: '—' }}</td>
                                                    <td>
                                                        <span class="badge {{ $struttura->attiva ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                            {{ $struttura->attiva ? 'Attiva' : 'Non attiva' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-soft-info btn-sm" onclick="window.location.href='{{ route($strutturaRoutePrefix . '.edit', ['id' => $struttura->id, 'return_to_owner_id' => $proprietario->id]) }}'" title="Accedi struttura">
                                                            <i class="ri-login-box-line fs-16 align-middle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-muted">Nessuna struttura collegata a questo proprietario. Puoi assegnarne una esistente o crearne una nuova direttamente da qui.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'servizi' ? 'show active' : '' }}" id="owner-pane-servizi" role="tabpanel">
                            <div class="border rounded-3 p-3">
                                <h5 class="mb-1">Servizi assegnati</h5>
                                <div class="text-muted mb-3">Qui scegli i servizi del catalogo del suo amministratore. Questa assegnazione è la base economica del proprietario e delle proforme che emetti verso di lui.</div>

                                @if($proprietario->admin && $serviziDisponibili->isNotEmpty())
                                    @php
                                        $serviziPerStruttura = $serviziDisponibili->where('tipo_costo', 'per_struttura')->values();
                                        $serviziGenerali = $serviziDisponibili->where('tipo_costo', '!=', 'per_struttura')->values();
                                        $totaleServiziAssegnati = collect($serviziAssegnati ?? [])->sum(function ($assegnazione) {
                                            return ((int) ($assegnazione['quantita'] ?? 1)) * ((float) ($assegnazione['importo_effettivo'] ?? 0));
                                        });
                                    @endphp
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4"><div class="border rounded-3 p-3 bg-light-subtle h-100"><div class="text-muted small text-uppercase mb-1">Servizi disponibili</div><div class="fs-3 mb-1">{{ $serviziDisponibili->count() }}</div><div class="small text-muted">Catalogo dell amministratore.</div></div></div>
                                        <div class="col-md-4"><div class="border rounded-3 p-3 bg-light-subtle h-100"><div class="text-muted small text-uppercase mb-1">Servizi assegnati</div><div class="fs-3 mb-1">{{ collect($serviziAssegnati ?? [])->count() }}</div><div class="small text-muted">Voci attive per questo proprietario.</div></div></div>
                                        <div class="col-md-4"><div class="border rounded-3 p-3 bg-light-subtle h-100"><div class="text-muted small text-uppercase mb-1">Totale base da pagare</div><div class="fs-3 mb-1" id="owner-servizi-totale">{{ number_format((float) $totaleServiziAssegnati, 2, ',', '.') }}</div><div class="small text-muted">Somma base dei servizi assegnati.</div></div></div>
                                    </div>
                                    @if($serviziPerStruttura->isNotEmpty())
                                        <div class="card border shadow-sm mb-3">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Servizi per struttura</div>
                                                <div class="small text-muted">Qui assegni i servizi che il proprietario paga per singola struttura.</div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Struttura</th>
                                                                <th>Servizio</th>
                                                                <th style="width: 100px;">Attivo</th>
                                                                <th>Importo base</th>
                                                                <th style="width: 120px;">Quantità</th>
                                                                <th style="width: 160px;">Importo personalizzato</th>
                                                                <th style="width: 150px;">Totale da pagare</th>
                                                                <th>Note</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($proprietario->strutture as $struttura)
                                                                @foreach($serviziPerStruttura as $servizio)
                                                                    @php
                                                                        $assegnazione = collect($serviziAssegnati ?? [])->first(fn ($row) => (int) $row['admin_servizio_id'] === (int) $servizio->id && (int) ($row['struttura_id'] ?? 0) === (int) $struttura->id);
                                                                        $quantita = max(1, (int) old("servizi_struttura.{$servizio->id}.{$struttura->id}.quantita", $assegnazione['quantita'] ?? 1));
                                                                        $overrideValue = old("servizi_struttura.{$servizio->id}.{$struttura->id}.importo_override", $assegnazione['importo_override'] ?? '');
                                                                        $importoEffettivo = filled($overrideValue) ? (float) str_replace(',', '.', (string) $overrideValue) : (float) ($servizio->importo ?? 0);
                                                                        $totaleRiga = $quantita * $importoEffettivo;
                                                                    @endphp
                                                                    <tr data-owner-servizio-row>
                                                                        <td>
                                                                            <div class="fw-semibold">{{ $struttura->nome_struttura }}</div>
                                                                            <div class="small text-muted">{{ $struttura->citta ?: 'Città non impostata' }}</div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="fw-semibold">{{ $servizio->nome }}</div>
                                                                            @if($servizio->note)
                                                                                <div class="small text-muted">{{ $servizio->note }}</div>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <input type="hidden" name="servizi_struttura[{{ $servizio->id }}][{{ $struttura->id }}][selected]" value="0">
                                                                            <input class="form-check-input js-owner-servizio-selected" type="checkbox" name="servizi_struttura[{{ $servizio->id }}][{{ $struttura->id }}][selected]" value="1" {{ old("servizi_struttura.{$servizio->id}.{$struttura->id}.selected", $assegnazione !== null) ? 'checked' : '' }}>
                                                                        </td>
                                                                        <td>{{ number_format((float) $servizio->importo, 2, ',', '.') }}</td>
                                                                        <td><input type="number" min="1" max="99999" name="servizi_struttura[{{ $servizio->id }}][{{ $struttura->id }}][quantita]" class="form-control form-control-sm js-owner-servizio-quantita" value="{{ $quantita }}"></td>
                                                                        <td><input type="number" step="0.01" min="0" name="servizi_struttura[{{ $servizio->id }}][{{ $struttura->id }}][importo_override]" class="form-control form-control-sm js-owner-servizio-override" value="{{ $overrideValue }}"></td>
                                                                        <td class="fw-semibold js-owner-servizio-totale" data-base-importo="{{ (float) ($servizio->importo ?? 0) }}">{{ number_format((float) $totaleRiga, 2, ',', '.') }}</td>
                                                                        <td><input type="text" name="servizi_struttura[{{ $servizio->id }}][{{ $struttura->id }}][note]" class="form-control form-control-sm" value="{{ old("servizi_struttura.{$servizio->id}.{$struttura->id}.note", $assegnazione['note'] ?? '') }}"></td>
                                                                    </tr>
                                                                @endforeach
                                                            @empty
                                                                <tr>
                                                                    <td colspan="8" class="text-center text-muted py-4">Per assegnare servizi per struttura devi prima collegare almeno una struttura al proprietario.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($serviziGenerali->isNotEmpty())
                                        <div class="card border shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Servizi generali del proprietario</div>
                                                <div class="small text-muted">Qui assegni i servizi che non dipendono da una struttura specifica e vanno fatturati al proprietario come voce generale.</div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 100px;">Attivo</th>
                                                                <th>Servizio</th>
                                                                <th>Modalità</th>
                                                                <th>Importo base</th>
                                                                <th style="width: 120px;">Quantità</th>
                                                                <th style="width: 160px;">Importo personalizzato</th>
                                                                <th style="width: 150px;">Totale da pagare</th>
                                                                <th>Note</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($serviziGenerali as $servizio)
                                                                @php
                                                                    $assegnazione = collect($serviziAssegnati ?? [])->first(fn ($row) => (int) $row['admin_servizio_id'] === (int) $servizio->id && empty($row['struttura_id']));
                                                                    $quantita = max(1, (int) old("servizi_generali.{$servizio->id}.quantita", $assegnazione['quantita'] ?? 1));
                                                                    $overrideValue = old("servizi_generali.{$servizio->id}.importo_override", $assegnazione['importo_override'] ?? '');
                                                                    $importoEffettivo = filled($overrideValue) ? (float) str_replace(',', '.', (string) $overrideValue) : (float) ($servizio->importo ?? 0);
                                                                    $totaleRiga = $quantita * $importoEffettivo;
                                                                @endphp
                                                                <tr data-owner-servizio-row>
                                                                    <td>
                                                                        <input type="hidden" name="servizi_generali[{{ $servizio->id }}][selected]" value="0">
                                                                        <input class="form-check-input js-owner-servizio-selected" type="checkbox" name="servizi_generali[{{ $servizio->id }}][selected]" value="1" {{ old("servizi_generali.{$servizio->id}.selected", $assegnazione !== null) ? 'checked' : '' }}>
                                                                    </td>
                                                                    <td>
                                                                        <div class="fw-semibold">{{ $servizio->nome }}</div>
                                                                        @if($servizio->note)
                                                                            <div class="small text-muted">{{ $servizio->note }}</div>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ match($servizio->tipo_costo) { 'flat' => 'Forfait', 'percentuale' => 'Percentuale', default => $servizio->tipo_costo } }}</td>
                                                                    <td>{{ number_format((float) $servizio->importo, 2, ',', '.') }}</td>
                                                                    <td><input type="number" min="1" max="99999" name="servizi_generali[{{ $servizio->id }}][quantita]" class="form-control form-control-sm js-owner-servizio-quantita" value="{{ $quantita }}"></td>
                                                                    <td><input type="number" step="0.01" min="0" name="servizi_generali[{{ $servizio->id }}][importo_override]" class="form-control form-control-sm js-owner-servizio-override" value="{{ $overrideValue }}"></td>
                                                                    <td class="fw-semibold js-owner-servizio-totale" data-base-importo="{{ (float) ($servizio->importo ?? 0) }}">{{ number_format((float) $totaleRiga, 2, ',', '.') }}</td>
                                                                    <td><input type="text" name="servizi_generali[{{ $servizio->id }}][note]" class="form-control form-control-sm" value="{{ old("servizi_generali.{$servizio->id}.note", $assegnazione['note'] ?? '') }}"></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @elseif($proprietario->admin)
                                    <div class="alert alert-light border mb-0">L amministratore assegnato non ha ancora un catalogo servizi configurato.</div>
                                @else
                                    <div class="alert alert-light border mb-0">Per assegnare servizi al proprietario devi prima collegarlo a un amministratore con catalogo servizi attivo.</div>
                                @endif
                            </div>
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'fatturazione' ? 'show active' : '' }}" id="owner-pane-fatturazione" role="tabpanel">
                            <div class="border rounded-3 p-3">
                                <h5 class="mb-1">Proforme servizi</h5>
                                <div class="text-muted mb-3">Qui leggi il quadro economico del proprietario e prepari le proforme da emettere verso di lui, partendo dai servizi assegnati.</div>
                                @php
                                    $licenzeOwner = collect($licenzeProprietario ?? []);
                                    $licenzeOwnerAttive = $licenzeOwner->where('attiva', true);
                                    $licenzeOwnerDaPagare = $licenzeOwner->where('stato_pagamento', 'da_pagare');
                                    $licenzeOwnerScadenza = $licenzeOwner->filter(fn ($licenza) => filled($licenza->data_scadenza))->sortBy('data_scadenza')->first();
                                @endphp
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 mb-0">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-4">
                                                            <i class="ri-file-list-3-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-muted small text-uppercase mb-1">Servizi in calcolo</div>
                                                        <div class="fs-3 mb-1">{{ $fatturazione['righe']->count() }}</div>
                                                        <div class="small text-muted">Righe base costruite su servizi e strutture del proprietario.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 mb-0">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title rounded-circle bg-success-subtle text-success fs-4">
                                                            <i class="ri-money-euro-circle-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-muted small text-uppercase mb-1">Proforma totale</div>
                                                        <div class="fs-3 mb-1">{{ number_format((float) ($fatturazione['totale'] ?? 0), 2, ',', '.') }}</div>
                                                        <div class="small text-muted">Somma base dei servizi fatturabili verso il proprietario.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 mb-0">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title rounded-circle bg-info-subtle text-info fs-4">
                                                            <i class="ri-building-4-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-muted small text-uppercase mb-1">Strutture in calcolo</div>
                                                        <div class="fs-3 mb-1">{{ $proprietario->strutture->count() }}</div>
                                                        <div class="small text-muted">Le strutture entrano nel calcolo dei servizi per struttura.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Licenze e pagamenti delle strutture del proprietario</div>
                                                <div class="small text-muted">Qui il proprietario vede cosa è attivo sulle sue strutture, cosa sta pagando la singola struttura e cosa può essere centralizzato sul proprietario.</div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-3">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="text-muted small text-uppercase mb-1">Licenze attive</div>
                                                            <div class="fw-bold fs-4">{{ $licenzeOwnerAttive->count() }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="text-muted small text-uppercase mb-1">Da pagare</div>
                                                            <div class="fw-bold fs-4 text-danger">{{ $licenzeOwnerDaPagare->count() }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="text-muted small text-uppercase mb-1">Totale licenze</div>
                                                            <div class="fw-bold fs-4">{{ number_format((float) $licenzeOwner->sum('prezzo'), 2, ',', '.') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="text-muted small text-uppercase mb-1">Prossima scadenza</div>
                                                            <div class="fw-semibold">{{ optional($licenzeOwnerScadenza?->data_scadenza)->format('d/m/Y') ?: 'Nessuna data' }}</div>
                                                            <div class="small text-muted">{{ $licenzeOwnerScadenza?->articolo?->nome ?: 'Nessuna licenza in scadenza' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="table-responsive border rounded-3">
                                                    <table class="table align-middle mb-0">
                                                        <thead>
                                                            <tr class="table-light">
                                                                <th>Struttura</th>
                                                                <th>Prodotto / licenza</th>
                                                                <th>Tracking</th>
                                                                <th>Stato</th>
                                                                <th>Scadenza</th>
                                                                <th class="text-end">Importo</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($licenzeOwner as $licenza)
                                                                <tr>
                                                                    <td>{{ $licenza->struttura?->nome_struttura ?: 'Licenza proprietario' }}</td>
                                                                    <td>
                                                                        <div class="fw-semibold">{{ $licenza->articolo?->nome ?: 'Licenza' }}</div>
                                                                        <div class="small text-muted">{{ $licenza->numero_licenza ?: '—' }}</div>
                                                                    </td>
                                                                    <td class="small">{{ $licenza->codice_tracking }}</td>
                                                                    <td>
                                                                        <span class="badge {{ $licenza->stato_pagamento === 'pagato' ? 'bg-success-subtle text-success' : ($licenza->stato_pagamento === 'parziale' ? 'bg-warning-subtle text-warning' : ($licenza->stato_pagamento === 'sospeso' ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                                                            {{ ucfirst(str_replace('_', ' ', $licenza->stato_pagamento)) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ optional($licenza->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
                                                                    <td class="text-end fw-semibold">{{ number_format((float) $licenza->prezzo, 2, ',', '.') }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted py-4">Nessuna licenza collegata alle strutture di questo proprietario.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                            <div class="text-muted small">Il dettaglio qui sotto è la base di partenza della fatturazione. Le personalizzazioni complete le fai nella schermata della proforma.</div>
                                            <button type="button" class="btn btn-success" onclick="window.location.href='{{ route($ownerRoutePrefix . '.proforme.create', $proprietario->id) }}'">
                                                <i class="ri-add-line align-bottom me-1"></i>
                                                Nuova proforma
                                            </button>
                                        </div>
                                        <div class="table-responsive border rounded-3">
                                            <table class="table align-middle mb-0">
                                                <thead>
                                                    <tr class="table-light">
                                                        <th>Struttura</th>
                                                        <th>Servizio</th>
                                                        <th>Quantità</th>
                                                        <th>Prezzo unitario</th>
                                                        <th class="text-end">Totale</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($fatturazione['righe'] as $riga)
                                                        <tr>
                                                            <td>{{ $riga['struttura_nome'] }}</td>
                                                            <td>
                                                                <div class="fw-semibold">{{ $riga['descrizione'] }}</div>
                                                                @if(!empty($riga['note']))
                                                                    <div class="small text-muted">{{ $riga['note'] }}</div>
                                                                @endif
                                                            </td>
                                                            <td>{{ $riga['quantita'] }}</td>
                                                            <td>{{ number_format((float) $riga['prezzo_unitario'], 2, ',', '.') }}</td>
                                                            <td class="text-end fw-semibold">{{ number_format((float) $riga['totale'], 2, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center text-muted py-4">Nessun servizio pronto per la fatturazione.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle"><div class="fw-semibold">Storico proforme</div></div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table align-middle mb-0">
                                                        <thead>
                                                            <tr class="table-light">
                                                                <th>Numero</th>
                                                                <th>Data</th>
                                                                <th>Stato</th>
                                                                <th>Righe</th>
                                                                <th class="text-end">Totale</th>
                                                                <th class="text-end"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($proforme as $proforma)
                                                                <tr>
                                                                    <td class="fw-semibold">{{ $proforma->numero }}</td>
                                                                    <td>{{ optional($proforma->data_documento)->format('d/m/Y') }}</td>
                                                                    <td>
                                                                        <span class="badge {{
                                                                            $proforma->stato === 'fatturata' ? 'bg-success-subtle text-success' :
                                                                            ($proforma->stato === 'chiusa' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info')
                                                                        }}">
                                                                            {{ ucfirst($proforma->stato) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $proforma->righe->count() }}</td>
                                                                    <td class="text-end fw-semibold">{{ number_format((float) $proforma->totale, 2, ',', '.') }}</td>
                                                                    <td class="text-end text-nowrap">
                                                                        <div class="d-inline-flex gap-1">
                                                                            <button type="button" class="btn btn-soft-info btn-sm" onclick="window.location.href='{{ route($ownerRoutePrefix . '.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}'" title="Apri proforma">
                                                                                <i class="ri-eye-line fs-16 align-middle"></i>
                                                                            </button>
                                                                            <button type="button" class="btn btn-soft-secondary btn-sm" onclick="window.open('{{ route($ownerRoutePrefix . '.proforme.print', ['id' => $proprietario->id, 'fatturazione' => $proforma->id]) }}', '_blank')" title="Stampa proforma">
                                                                                <i class="ri-printer-line fs-16 align-middle"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="6" class="text-center text-muted py-4">Nessuna proforma creata per questo proprietario.</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route($ownerRoutePrefix . '.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" form="owner-main-form" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var activeTabInput = document.getElementById('owner_active_tab');
    var geoRoot = document.querySelector('[data-ui="geo-italia"][data-prefix="owner_geo"]');
    var latInput = document.getElementById('owner_latitudine');
    var lngInput = document.getElementById('owner_longitudine');
    var comuneSelect = document.getElementById('owner_geo_comune_id');
    var http = window.http || window.axios || null;

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabButton) {
        tabButton.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target') || '';
            var tabName = target.replace('#owner-pane-', '');
            if (activeTabInput && tabName) activeTabInput.value = tabName;
        });
    });

    function hydrateCoordinates(comuneId) {
        if (!http || !comuneId || !latInput || !lngInput) return;
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
                return;
            }
            hydrateCoordinates(comuneId);
        });

        if (comuneSelect && comuneSelect.value && (!latInput.value || !lngInput.value)) {
            hydrateCoordinates(comuneSelect.value);
        }
    }

    function parseDecimal(value) {
        if (value === null || value === undefined) return 0;
        var normalized = String(value).trim().replace(/\s/g, '');
        if (!normalized) return 0;
        if (normalized.includes(',') && normalized.includes('.')) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else if (normalized.includes(',')) {
            normalized = normalized.replace(',', '.');
        }
        var parsed = parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatMoney(value) {
        return new Intl.NumberFormat('it-IT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value || 0);
    }

    function recalcOwnerServizi() {
        var grandTotal = 0;

        document.querySelectorAll('[data-owner-servizio-row]').forEach(function (row) {
            var selected = row.querySelector('.js-owner-servizio-selected');
            var qtyInput = row.querySelector('.js-owner-servizio-quantita');
            var overrideInput = row.querySelector('.js-owner-servizio-override');
            var totalCell = row.querySelector('.js-owner-servizio-totale');
            if (!qtyInput || !overrideInput || !totalCell) return;

            var qty = Math.max(1, parseInt(qtyInput.value || '1', 10) || 1);
            var override = parseDecimal(overrideInput.value);
            var base = parseDecimal(totalCell.dataset.baseImporto);
            var unit = overrideInput.value !== '' ? override : base;
            var rowTotal = qty * unit;

            totalCell.textContent = formatMoney(rowTotal);

            if (!selected || selected.checked) {
                grandTotal += rowTotal;
            }
        });

        var totalNode = document.getElementById('owner-servizi-totale');
        if (totalNode) {
            totalNode.textContent = formatMoney(grandTotal);
        }
    }

    document.querySelectorAll('.js-owner-servizio-selected, .js-owner-servizio-quantita, .js-owner-servizio-override').forEach(function (input) {
        input.addEventListener('input', recalcOwnerServizi);
        input.addEventListener('change', recalcOwnerServizi);
    });

    document.querySelectorAll('.js-owner-struttura-row').forEach(function (row) {
        row.addEventListener('dblclick', function () {
            var href = row.dataset.href;
            if (href) {
                window.location.href = href;
            }
        });
    });

    recalcOwnerServizi();
});
</script>
@endpush
