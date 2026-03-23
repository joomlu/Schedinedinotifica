@extends('layouts.master')
@section('title') Pagamenti @endsection

@php
    $oldArticoloId = old('articolo_id');
    $canManageArticoli = $canManageArticoli ?? true;
    $showArticoliCatalogo = $showArticoliCatalogo ?? $canManageArticoli;
    $activeTab = request('tab', $oldArticoloId ? 'licenze' : ($showArticoliCatalogo ? 'articoli' : 'licenze'));
    $activeLicenzeTab = request('licenze_tab', $oldArticoloId ? 'nuova' : 'elenco');
    $activeArticoliTab = request('articoli_tab', old('nome') || old('codice') || old('accesso_key') ? 'nuovo' : 'elenco');
    $statiLicenza = ['da_pagare', 'pagato', 'parziale', 'sospeso'];
    $oldPrezzo = old('prezzo', '0');
    $selectedArticolo = $oldArticoloId ? $articoli->firstWhere('id', (int) $oldArticoloId) : null;
    $pagamentiBaseRoute = $pagamentiBaseRoute ?? 'superadmin.pagamenti';
    $servizioRoutePrefix = $servizioRoutePrefix ?? 'superadmin.strutture';
    $strutturaEditRoute = $strutturaEditRoute ?? 'superadmin.strutture.edit';
    $areaLabel = str_starts_with($pagamentiBaseRoute, 'admin.') ? 'Admin' : 'SuperAdmin';
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') {{ $areaLabel }} @endslot
        @slot('title') Pagamenti e Licenze @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Controlla i dati di pagamenti e licenze.</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h4 class="card-title mb-1">Pagamenti e licenze</h4>
            <p class="text-muted mb-0">Gestisci licenze assegnate, scadenze, stato pagamento e operativita delle strutture usando la stessa UI del resto del sistema.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Strutture filtrate</div>
                    <div class="fw-bold fs-4">{{ $summary['totale'] }}</div>
                    <div class="small text-muted">Totale righe visibili nel pannello servizi struttura.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Licenze attive</div>
                    <div class="fw-bold fs-4 text-success">{{ $summary['licenze_attive'] }}</div>
                    <div class="small text-muted">Assegnazioni oggi operative.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Articoli attivi</div>
                    <div class="fw-bold fs-4 text-primary">{{ $summary['articoli_attivi'] }}</div>
                    <div class="small text-muted">Catalogo licenze disponibili.</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-1">Scadute</div>
                    <div class="fw-bold fs-4 text-danger">{{ $summary['scadute'] }}</div>
                    <div class="small text-muted">Servizi struttura scaduti da rinnovare.</div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
        @if($showArticoliCatalogo)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'articoli' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-articoli" type="button" role="tab">Catalogo articoli</button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'licenze' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-licenze" type="button" role="tab">Licenze assegnate</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'strutture' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-strutture" type="button" role="tab">Servizi struttura</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'conto' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-conto" type="button" role="tab">Stato conto</button>
        </li>
    </ul>

    <div class="tab-content">
        @if($showArticoliCatalogo)
        <div class="tab-pane fade {{ $activeTab === 'articoli' ? 'show active' : '' }}" id="tab-articoli" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeArticoliTab === 'elenco' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#articoli-pane-elenco" type="button" role="tab">Elenco articoli</button>
                        </li>
                        @if($canManageArticoli)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeArticoliTab === 'nuovo' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#articoli-pane-nuovo" type="button" role="tab">Nuovo articolo</button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeArticoliTab === 'elenco' ? 'show active' : '' }}" id="articoli-pane-elenco" role="tabpanel">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1">Elenco articoli</h5>
                                    <p class="text-muted mb-0">Qui leggi il catalogo licenze, i prezzi base, i codici di accesso e l'organizzazione in articoli principali o secondari.</p>
                                </div>
                                @if($canManageArticoli)
                                <button type="button" class="btn btn-primary" id="goNuovoArticolo">Nuovo articolo</button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Articolo</th>
                                            <th>Accesso</th>
                                            <th>Prezzo base</th>
                                            <th>Stato</th>
                                            <th style="min-width: 340px;">Gestione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($articoli as $articolo)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $articolo->nome }}</div>
                                                    <div class="small text-muted">{{ $articolo->parent ? 'Sotto '.$articolo->parent->nome : 'Articolo principale' }}</div>
                                                    @if($articolo->descrizione)
                                                        <div class="small text-muted mt-1">{{ $articolo->descrizione }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>{{ $articolo->accesso_key ?: '—' }}</div>
                                                    <div class="small text-muted">{{ $articolo->codice ?: 'Nessun codice' }}</div>
                                                </td>
                                                <td class="fw-semibold">{{ number_format((float) $articolo->prezzo_base, 2, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge {{ $articolo->attivo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                        {{ $articolo->attivo ? 'Attivo' : 'Disattivo' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($canManageArticoli)
                                                    <form method="POST" action="{{ route($pagamentiBaseRoute.'.articoli.update', $articolo->id) }}" class="row g-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="col-md-4">
                                                            <input type="text" name="nome" class="form-control form-control-sm" value="{{ $articolo->nome }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" name="accesso_key" class="form-control form-control-sm" value="{{ $articolo->accesso_key }}" placeholder="Accesso">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="number" name="prezzo_base" class="form-control form-control-sm" step="0.01" min="0" value="{{ $articolo->prezzo_base }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select name="attivo" class="form-select form-select-sm">
                                                                <option value="1" @selected($articolo->attivo)>Attivo</option>
                                                                <option value="0" @selected(!$articolo->attivo)>Disattivo</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="codice" class="form-control form-control-sm" value="{{ $articolo->codice }}" placeholder="Codice">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select name="parent_id" class="form-select form-select-sm">
                                                                <option value="">Articolo principale</option>
                                                                @foreach($articoli as $articoloPadre)
                                                                    @continue($articoloPadre->id === $articolo->id)
                                                                    <option value="{{ $articoloPadre->id }}" @selected($articolo->parent_id === $articoloPadre->id)>{{ $articoloPadre->nome }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="number" name="ordine" class="form-control form-control-sm" min="0" value="{{ $articolo->ordine }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <textarea name="descrizione" class="form-control form-control-sm" rows="2" placeholder="Descrizione articolo">{{ $articolo->descrizione }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <input type="text" name="note" class="form-control form-control-sm" value="{{ $articolo->note }}" placeholder="Note">
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary">Salva articolo</button>
                                                        </div>
                                                    </form>
                                                    @else
                                                        <div class="small text-muted">Catalogo centralizzato. L'amministratore può leggerlo ma non modificarlo.</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="fw-semibold mb-1">Catalogo ancora vuoto</div>
                                                    <div class="text-muted">Crea qui il primo articolo licenza, ad esempio il modulo Pro o i suoi sottoarticoli.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($canManageArticoli)
                        <div class="tab-pane fade {{ $activeArticoliTab === 'nuovo' ? 'show active' : '' }}" id="articoli-pane-nuovo" role="tabpanel">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-9">
                                    <div class="card border shadow-sm mb-0">
                                        <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">Nuovo articolo / licenza</div>
                                                <div class="small text-muted">Crea un articolo principale o un sottoarticolo del catalogo licenze.</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-light" id="goElencoArticoli">Torna all'elenco</button>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route($pagamentiBaseRoute.'.articoli.store') }}" class="row g-3">
                                                @csrf
                                                <div class="col-12">
                                                    <label class="form-label">Articolo padre</label>
                                                    <select name="parent_id" class="form-select">
                                                        <option value="">Articolo principale</option>
                                                        @foreach($articoli as $articoloPadre)
                                                            <option value="{{ $articoloPadre->id }}" @selected(old('parent_id') == $articoloPadre->id)>{{ $articoloPadre->nome }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Nome</label>
                                                    <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Codice</label>
                                                    <input type="text" name="codice" class="form-control" value="{{ old('codice') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Accesso chiave</label>
                                                    <input type="text" name="accesso_key" class="form-control" value="{{ old('accesso_key') }}" placeholder="es. schedine-pro">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Prezzo base</label>
                                                    <input type="number" name="prezzo_base" class="form-control" step="0.01" min="0" value="{{ old('prezzo_base', '0') }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Ordine</label>
                                                    <input type="number" name="ordine" class="form-control" min="0" value="{{ old('ordine', '0') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Attivo</label>
                                                    <select name="attivo" class="form-select">
                                                        <option value="1" @selected(old('attivo', '1') == '1')>Sì</option>
                                                        <option value="0" @selected(old('attivo') === '0')>No</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Descrizione</label>
                                                    <textarea name="descrizione" class="form-control" rows="3">{{ old('descrizione') }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Note</label>
                                                    <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                                                </div>
                                                <div class="col-12 d-flex flex-wrap gap-2">
                                                    <button type="submit" class="btn btn-primary">Salva articolo</button>
                                                    <button type="button" class="btn btn-light" id="cancelNuovoArticolo">Annulla</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="tab-pane fade {{ $activeTab === 'licenze' ? 'show active' : '' }}" id="tab-licenze" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeLicenzeTab === 'elenco' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#licenze-pane-elenco" type="button" role="tab">Elenco licenze assegnate</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeLicenzeTab === 'nuova' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#licenze-pane-nuova" type="button" role="tab">Nuova licenza</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeLicenzeTab === 'elenco' ? 'show active' : '' }}" id="licenze-pane-elenco" role="tabpanel">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1">Elenco licenze assegnate</h5>
                                    <p class="text-muted mb-0">Qui controlli tracking, stato pagamento, scadenza e stampa della singola licenza.</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="goNuovaLicenza">Nuova licenza</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Licenza</th>
                                            <th>Destinazione</th>
                                            <th>Admin</th>
                                            <th>Prezzo</th>
                                            <th>Stato</th>
                                            <th>Scadenza</th>
                                            <th class="text-end">Gestione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assegnazioni as $assegnazione)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $assegnazione->numero_licenza ?: '—' }}</div>
                                                    <div>{{ $assegnazione->articolo?->nome }}</div>
                                                    <div class="small text-muted">{{ $assegnazione->codice_tracking }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $assegnazione->struttura?->nome_struttura ?: ($assegnazione->proprietario?->nome ?: 'Destinazione non definita') }}</div>
                                                    <div class="small text-muted">{{ $assegnazione->struttura ? 'Licenza struttura' : 'Licenza da riallineare' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $assegnazione->admin?->name ?: '—' }}</div>
                                                    <div class="small text-muted">{{ $assegnazione->admin?->email ?: 'Nessuna email' }}</div>
                                                </td>
                                                <td class="fw-semibold">{{ number_format((float) $assegnazione->prezzo, 2, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge {{ $assegnazione->stato_pagamento === 'pagato' ? 'bg-success-subtle text-success' : ($assegnazione->stato_pagamento === 'parziale' ? 'bg-warning-subtle text-warning' : ($assegnazione->stato_pagamento === 'sospeso' ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $assegnazione->stato_pagamento)) }}
                                                    </span>
                                                    <div class="small text-muted mt-1">{{ $assegnazione->attiva ? 'Attiva' : 'Non attiva' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ optional($assegnazione->data_scadenza)->format('d/m/Y') ?: '—' }}</div>
                                                    <div class="small text-muted">Inizio {{ optional($assegnazione->data_inizio)->format('d/m/Y') ?: '—' }}</div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalLicenzaEdit{{ $assegnazione->id }}" title="Modifica licenza">
                                                            <i class="ri-edit-line fs-16 align-middle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-soft-secondary btn-sm" onclick="window.open('{{ route($pagamentiBaseRoute.'.licenze.print', $assegnazione->id) }}', '_blank')" title="Stampa licenza">
                                                            <i class="ri-printer-line fs-16 align-middle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalLicenzaDelete{{ $assegnazione->id }}" title="Elimina licenza">
                                                            <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="modalLicenzaEdit{{ $assegnazione->id }}" tabindex="-1" aria-labelledby="modalLicenzaEditLabel{{ $assegnazione->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <div>
                                                                <h5 class="modal-title mb-1" id="modalLicenzaEditLabel{{ $assegnazione->id }}">Modifica licenza</h5>
                                                                <div class="small text-muted">{{ $assegnazione->numero_licenza ?: 'Licenza senza numero' }} · {{ $assegnazione->codice_tracking }}</div>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                        </div>
                                                        <form method="POST" action="{{ route($pagamentiBaseRoute.'.licenze.update', $assegnazione->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="row g-3">
                                                                    <div class="col-md-8">
                                                                        <label class="form-label">Articolo / licenza</label>
                                                                        <select name="articolo_id" class="form-select" required>
                                                                            @foreach($articoli as $articolo)
                                                                                <option value="{{ $articolo->id }}" @selected($assegnazione->articolo_id === $articolo->id)>
                                                                                    {{ $articolo->parent ? $articolo->parent->nome.' / ' : '' }}{{ $articolo->nome }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Quantita</label>
                                                                        <input type="number" name="quantita" class="form-control" min="1" max="99999" value="{{ $assegnazione->quantita }}">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Proprietario collegato</label>
                                                                        <input type="hidden" id="licenza-owner-hidden-{{ $assegnazione->id }}" name="proprietario_id" value="{{ $assegnazione->proprietario_id }}">
                                                                        <input type="text" id="licenza-owner-preview-{{ $assegnazione->id }}" class="form-control" value="{{ $assegnazione->proprietario?->nome ?: 'Si aggiorna dalla struttura selezionata' }}" readonly>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Struttura</label>
                                                                        <select name="struttura_id" class="form-select licenza-edit-struttura" data-preview-target="licenza-owner-preview-{{ $assegnazione->id }}" data-hidden-target="licenza-owner-hidden-{{ $assegnazione->id }}" required>
                                                                            <option value="">Seleziona struttura</option>
                                                                            @foreach($struttureDisponibili as $struttura)
                                                                                <option value="{{ $struttura->id }}" data-proprietario="{{ $struttura->proprietario_id }}" data-proprietario-nome="{{ $struttura->proprietario?->nome }}" @selected($assegnazione->struttura_id === $struttura->id)}>
                                                                                    {{ $struttura->nome_struttura }}@if($struttura->proprietario) - {{ $struttura->proprietario->nome }}@endif
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <div class="form-text">La licenza principale resta legata alla struttura. Il proprietario si aggiorna di conseguenza.</div>
                                                                    </div>
                                                                    @if($pagamentiBaseRoute === 'superadmin.pagamenti')
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">Admin</label>
                                                                            <select name="admin_id" class="form-select">
                                                                                <option value="">Nessun admin</option>
                                                                                @foreach($admins as $admin)
                                                                                    <option value="{{ $admin->id }}" @selected($assegnazione->admin_id === $admin->id)>{{ $admin->name }}@if($admin->email) - {{ $admin->email }}@endif</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    @endif
                                                                    <div class="col-md-{{ $pagamentiBaseRoute === 'superadmin.pagamenti' ? '3' : '6' }}">
                                                                        <label class="form-label">Prezzo</label>
                                                                        <input type="number" name="prezzo" class="form-control" step="0.01" min="0" value="{{ $assegnazione->prezzo }}">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Stato pagamento</label>
                                                                        <select name="stato_pagamento" class="form-select">
                                                                            @foreach($statiLicenza as $statoLicenza)
                                                                                <option value="{{ $statoLicenza }}" @selected($assegnazione->stato_pagamento === $statoLicenza)>{{ ucfirst(str_replace('_', ' ', $statoLicenza)) }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Attiva</label>
                                                                        <select name="attiva" class="form-select">
                                                                            <option value="1" @selected($assegnazione->attiva)>Sì</option>
                                                                            <option value="0" @selected(!$assegnazione->attiva)>No</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Data inizio</label>
                                                                        <x-calendario name="data_inizio" variant="single" :value="optional($assegnazione->data_inizio)->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Data scadenza</label>
                                                                        <x-calendario name="data_scadenza" variant="single" :value="optional($assegnazione->data_scadenza)->format('Y-m-d')" placeholder="gg/mm/aaaa" />
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="form-label">Note</label>
                                                                        <textarea name="note" class="form-control" rows="3">{{ $assegnazione->note }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                                                <button type="submit" class="btn btn-primary btn-sm">Salva licenza</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="modalLicenzaDelete{{ $assegnazione->id }}" tabindex="-1" aria-labelledby="modalLicenzaDeleteLabel{{ $assegnazione->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalLicenzaDeleteLabel{{ $assegnazione->id }}">Elimina licenza</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="fw-semibold mb-1">{{ $assegnazione->articolo?->nome }}</div>
                                                            <div class="small text-muted mb-2">{{ $assegnazione->numero_licenza ?: 'Licenza senza numero' }} · {{ $assegnazione->codice_tracking }}</div>
                                                            <p class="mb-0">Vuoi eliminare questa licenza assegnata? L'operazione rimuove il collegamento commerciale dalla struttura o dal proprietario.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Annulla</button>
                                                            <form method="POST" action="{{ route($pagamentiBaseRoute.'.licenze.destroy', $assegnazione->id) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="fw-semibold mb-1">Nessuna licenza assegnata</div>
                                                    <div class="text-muted">Puoi iniziare dal pulsante nuova licenza e creare la prima assegnazione.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade {{ $activeLicenzeTab === 'nuova' ? 'show active' : '' }}" id="licenze-pane-nuova" role="tabpanel">
                            <div class="row justify-content-center">
                                <div class="col-xxl-8 col-xl-9">
                                    <div class="card border shadow-sm mb-0">
                                        <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">Nuova licenza</div>
                                                <div class="small text-muted">Assegni la licenza software alla struttura. Il proprietario viene derivato automaticamente dalla struttura selezionata.</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-light" id="goElencoLicenze">Torna all'elenco</button>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route($pagamentiBaseRoute.'.licenze.store') }}" class="row g-3">
                                                @csrf
                                                <div class="col-12">
                                                    <div class="border rounded-3 p-3 bg-light-subtle">
                                                        <div class="text-muted small text-uppercase mb-1">Codice licenza</div>
                                                        <div class="fw-semibold">Numerazione automatica al salvataggio</div>
                                                        <div class="small text-muted">Il sistema genera un numero univoco da usare per tracking, stampa e ricerca.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Articolo / licenza</label>
                                                    <select name="articolo_id" class="form-select" id="licenza_articolo_id" required>
                                                        <option value="">Seleziona articolo</option>
                                                        @foreach($articoli as $articolo)
                                                            <option
                                                                value="{{ $articolo->id }}"
                                                                data-prezzo="{{ $articolo->prezzo_base }}"
                                                                data-accesso="{{ $articolo->accesso_key ?: $articolo->codice ?: 'Accesso non definito' }}"
                                                                @selected((string) $oldArticoloId === (string) $articolo->id)
                                                            >
                                                                {{ $articolo->parent ? $articolo->parent->nome.' / ' : '' }}{{ $articolo->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Accesso licenza</label>
                                                    <input type="text" id="licenza_accesso_preview" class="form-control" value="{{ $selectedArticolo?->accesso_key ?: ($selectedArticolo?->codice ?: 'Si compila dal catalogo articolo') }}" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Proprietario collegato</label>
                                                    <input type="hidden" name="proprietario_id" id="licenza_proprietario_id" value="{{ old('proprietario_id') }}">
                                                    <input type="text" id="licenza_proprietario_preview" class="form-control" value="{{ old('proprietario_id') ? optional($proprietari->firstWhere('id', (int) old('proprietario_id')))->nome : '' }}" placeholder="Seleziona prima la struttura" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Struttura</label>
                                                    <select name="struttura_id" class="form-select" id="licenza_struttura_id" required>
                                                        <option value="">Seleziona struttura</option>
                                                        @foreach($struttureDisponibili as $struttura)
                                                            <option value="{{ $struttura->id }}" data-proprietario="{{ $struttura->proprietario_id }}" @selected(old('struttura_id') == $struttura->id)>
                                                                {{ $struttura->nome_struttura }}@if($struttura->proprietario) - {{ $struttura->proprietario->nome }}@endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">La licenza principale viene sempre collegata a una struttura specifica.</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Quantità</label>
                                                    <input type="number" name="quantita" class="form-control" min="1" max="99999" value="{{ old('quantita', 1) }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Prezzo</label>
                                                    <input type="number" name="prezzo" id="licenza_prezzo" class="form-control" step="0.01" min="0" value="{{ $oldPrezzo }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Stato pagamento</label>
                                                    <select name="stato_pagamento" class="form-select">
                                                        @foreach($statiLicenza as $statoLicenza)
                                                            <option value="{{ $statoLicenza }}" @selected(old('stato_pagamento', 'da_pagare') === $statoLicenza)>{{ ucfirst(str_replace('_', ' ', $statoLicenza)) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Attiva</label>
                                                    <select name="attiva" class="form-select">
                                                        <option value="1" @selected(old('attiva', '1') == '1')>Sì</option>
                                                        <option value="0" @selected(old('attiva') === '0')>No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Data inizio</label>
                                                    <x-calendario name="data_inizio" variant="single" :value="old('data_inizio')" placeholder="gg/mm/aaaa" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Data scadenza</label>
                                                    <x-calendario name="data_scadenza" variant="single" :value="old('data_scadenza')" placeholder="gg/mm/aaaa" />
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Note</label>
                                                    <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                                                </div>
                                                <div class="col-12 d-flex flex-wrap gap-2">
                                                    <button type="submit" class="btn btn-primary">Assegna licenza</button>
                                                    <button type="button" class="btn btn-light" id="cancelNuovaLicenza">Annulla</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ $activeTab === 'strutture' ? 'show active' : '' }}" id="tab-strutture" role="tabpanel">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route($pagamentiBaseRoute.'.index') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="tab" value="strutture">
                        <div class="col-lg-4">
                            <label class="form-label">Cerca</label>
                            <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Struttura, proprietario, admin, città, piano...">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Attiva</label>
                            <select name="attiva" class="form-select">
                                <option value="">Tutte</option>
                                <option value="1" {{ ($filters['attiva'] ?? '') === '1' ? 'selected' : '' }}>Attive</option>
                                <option value="0" {{ ($filters['attiva'] ?? '') === '0' ? 'selected' : '' }}>Disattive</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Stato pagamento</label>
                            <select name="stato_pagamento" class="form-select">
                                <option value="">Tutti</option>
                                @foreach($statiPagamento as $statoPagamento)
                                    <option value="{{ $statoPagamento }}" {{ ($filters['stato_pagamento'] ?? '') === $statoPagamento ? 'selected' : '' }}>{{ $statoPagamento }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Scadenza</label>
                            <select name="scadenza" class="form-select">
                                <option value="">Tutte</option>
                                <option value="scadute" {{ ($filters['scadenza'] ?? '') === 'scadute' ? 'selected' : '' }}>Scadute</option>
                                <option value="entro_30" {{ ($filters['scadenza'] ?? '') === 'entro_30' ? 'selected' : '' }}>Entro 30 giorni</option>
                                <option value="senza_data" {{ ($filters['scadenza'] ?? '') === 'senza_data' ? 'selected' : '' }}>Senza data</option>
                            </select>
                        </div>
                        <div class="col-lg-1 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">Filtra</button>
                        </div>
                        <div class="col-12">
                            <a href="{{ route($pagamentiBaseRoute.'.index', ['tab' => 'strutture']) }}" class="btn btn-light btn-sm">Azzera filtri</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <h4 class="card-title mb-1">Situazione licenze struttura</h4>
                            <p class="text-muted mb-0">Qui leggi lo stato operativo delle strutture e poi entri nelle azioni con i comandi dedicati della UI.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Struttura</th>
                                    <th>Proprietario</th>
                                    <th>Admin</th>
                                    <th>Territorio</th>
                                    <th>Servizio</th>
                                    <th class="text-end">Gestione</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($strutture as $struttura)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $struttura->nome_struttura }}</div>
                                            <div class="small text-muted">ID {{ $struttura->id }}</div>
                                        </td>
                                        <td>
                                            <div>{{ optional($struttura->proprietario)->nome ?? '—' }}</div>
                                            <div class="small text-muted">{{ optional($struttura->proprietario)->email ?? 'Nessuna email' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ optional(optional($struttura->proprietario)->admin)->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ optional(optional($struttura->proprietario)->admin)->email ?? 'Nessuna email' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $struttura->citta ?: 'Città non impostata' }}</div>
                                            <div class="small text-muted">{{ $struttura->provincia ?: 'Provincia non impostata' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge {{ $struttura->attiva ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $struttura->attiva ? 'Servizio attivo' : 'Servizio disattivo' }}
                                                </span>
                                                @if($struttura->scadenza_servizio)
                                                    @php
                                                        $isExpired = $struttura->scadenza_servizio->isPast();
                                                        $isSoon = !$isExpired && $struttura->scadenza_servizio->diffInDays(now()) <= 30;
                                                    @endphp
                                                    <span class="badge {{ $isExpired ? 'bg-danger-subtle text-danger' : ($isSoon ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info') }}">
                                                        Scadenza {{ $struttura->scadenza_servizio->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">Nessuna scadenza</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">Piano</div>
                                            <div class="fw-semibold">{{ $struttura->piano ?: 'Non impostato' }}</div>
                                            <div class="small text-muted mt-2">Stato pagamento</div>
                                            <div class="fw-semibold">{{ $struttura->stato_pagamento ?: 'Non impostato' }}</div>
                                        </td>
                                        <td class="text-end" style="min-width: 190px;">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalServizioStruttura{{ $struttura->id }}" title="Modifica servizio">
                                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                                </button>
                                                <form method="POST" action="{{ route('strutture.seleziona', $struttura->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-soft-success btn-sm" title="Seleziona struttura">
                                                        <i class="ri-focus-3-line fs-16 align-middle"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route($strutturaEditRoute, $struttura->id) }}" class="btn btn-soft-secondary btn-sm" title="Apri struttura">
                                                    <i class="ri-external-link-line fs-16 align-middle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalServizioStruttura{{ $struttura->id }}" tabindex="-1" aria-labelledby="modalServizioStrutturaLabel{{ $struttura->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title mb-1" id="modalServizioStrutturaLabel{{ $struttura->id }}">Modifica servizio struttura</h5>
                                                        <div class="small text-muted">{{ $struttura->nome_struttura }} · {{ optional($struttura->proprietario)->nome ?: 'Senza proprietario' }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>
                                                <form action="{{ route($servizioRoutePrefix.'.servizio', $struttura->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Attiva</label>
                                                                <select name="attiva" class="form-select">
                                                                    <option value="1" {{ $struttura->attiva ? 'selected' : '' }}>Attiva</option>
                                                                    <option value="0" {{ !$struttura->attiva ? 'selected' : '' }}>Disattiva</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Scadenza servizio</label>
                                                                <x-calendario
                                                                    name="scadenza_servizio"
                                                                    variant="single"
                                                                    :value="optional($struttura->scadenza_servizio)->format('Y-m-d')"
                                                                    placeholder="gg/mm/aaaa"
                                                                />
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Piano</label>
                                                                <input type="text" name="piano" class="form-control" value="{{ $struttura->piano }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Stato pagamento</label>
                                                                <input type="text" name="stato_pagamento" class="form-control" value="{{ $struttura->stato_pagamento }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Salva servizio</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="fw-semibold mb-1">Nessuna struttura trovata</div>
                                            <div class="text-muted">Con i filtri attuali non ci sono righe da mostrare in pagamenti e licenze.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ $activeTab === 'conto' ? 'show active' : '' }}" id="tab-conto" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Stato conto generale</h5>
                            <p class="text-muted mb-0">Qui vedi in un solo posto licenze, proforme proprietario e, per il superadmin, anche le proforme emesse verso gli amministratori.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route($pagamentiBaseRoute.'.index') }}" class="row g-3 mb-3">
                        <input type="hidden" name="tab" value="conto">
                        <div class="col-md-4">
                            <label class="form-label">Amministratore</label>
                            <select name="conto_admin_id" class="form-select" @disabled($pagamentiBaseRoute === 'admin.pagamenti')>
                                <option value="">Tutti</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" @selected(($contoFilters['admin_id'] ?? null) == $admin->id)>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Proprietario</label>
                            <select name="conto_proprietario_id" class="form-select">
                                <option value="">Tutti</option>
                                @foreach($proprietari as $proprietario)
                                    <option value="{{ $proprietario->id }}" @selected(($contoFilters['proprietario_id'] ?? null) == $proprietario->id)>{{ $proprietario->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Struttura</label>
                            <select name="conto_struttura_id" class="form-select">
                                <option value="">Tutte</option>
                                @foreach($struttureDisponibili as $struttura)
                                    <option value="{{ $struttura->id }}" @selected(($contoFilters['struttura_id'] ?? null) == $struttura->id)>{{ $struttura->nome_struttura }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Applica filtri</button>
                            <a href="{{ route($pagamentiBaseRoute.'.index', ['tab' => 'conto']) }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small text-uppercase mb-1">Totale licenze</div>
                                <div class="fw-bold fs-4">{{ number_format((float) ($statoConto['licenze'] ?? 0), 2, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small text-uppercase mb-1">Totale proforme proprietario</div>
                                <div class="fw-bold fs-4">{{ number_format((float) ($statoConto['proforme'] ?? 0), 2, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="text-muted small text-uppercase mb-1">Totale selezione</div>
                                <div class="fw-bold fs-4">{{ number_format((float) ($statoConto['totale'] ?? 0), 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Admin</th>
                                    <th>Proprietario</th>
                                    <th>Struttura</th>
                                    <th>Descrizione</th>
                                    <th>Documento</th>
                                    <th>Stato</th>
                                    <th>Scadenza</th>
                                    <th class="text-end">Totale</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($statoConto['righe'] ?? collect()) as $riga)
                                    <tr>
                                        <td>{{ $riga['tipo'] }}</td>
                                        <td>{{ optional($riga['data'])->format('d/m/Y') ?: '—' }}</td>
                                        <td>{{ $riga['admin'] ?: '—' }}</td>
                                        <td>{{ $riga['proprietario'] ?: '—' }}</td>
                                        <td>{{ $riga['struttura'] ?: '—' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $riga['descrizione'] }}</div>
                                            @if(!empty($riga['tracking']))
                                                <div class="small text-muted">{{ $riga['tracking'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $riga['documento'] ?: '—' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $riga['stato'] ?: '—')) }}</td>
                                        <td>{{ optional($riga['scadenza'])->format('d/m/Y') ?: '—' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float) $riga['totale'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Nessun movimento trovato con i filtri correnti.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($pagamentiBaseRoute === 'superadmin.pagamenti')
                        <div class="card border shadow-sm mt-3 mb-0">
                            <div class="card-header bg-light-subtle">
                                <div class="fw-semibold">Proforme amministratori</div>
                                <div class="small text-muted">Questa sezione mostra i documenti che emetti verso gli amministratori, separati dalle strutture e dai proprietari.</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr class="table-light">
                                                <th>Amministratore</th>
                                                <th>Numero</th>
                                                <th>Data</th>
                                                <th>Stato</th>
                                                <th class="text-end">Totale</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($adminProformeConto as $proformaAdmin)
                                                <tr>
                                                    <td>{{ $proformaAdmin->amministratore?->name ?: '—' }}</td>
                                                    <td>{{ $proformaAdmin->numero }}</td>
                                                    <td>{{ optional($proformaAdmin->data_documento)->format('d/m/Y') ?: '—' }}</td>
                                                    <td>{{ ucfirst($proformaAdmin->stato) }}</td>
                                                    <td class="text-end fw-semibold">{{ number_format((float) $proformaAdmin->totale, 2, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Nessuna proforma amministratore trovata.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var articoloSelect = document.getElementById('licenza_articolo_id');
    var prezzoInput = document.getElementById('licenza_prezzo');
    var accessoPreview = document.getElementById('licenza_accesso_preview');
    var strutturaSelect = document.getElementById('licenza_struttura_id');
    var proprietarioInput = document.getElementById('licenza_proprietario_id');
    var proprietarioPreview = document.getElementById('licenza_proprietario_preview');
    var nuovoArticoloTrigger = document.querySelector('[data-bs-target="#articoli-pane-nuovo"]');
    var elencoArticoliTrigger = document.querySelector('[data-bs-target="#articoli-pane-elenco"]');
    var nuovaLicenzaTrigger = document.querySelector('[data-bs-target="#licenze-pane-nuova"]');
    var elencoLicenzeTrigger = document.querySelector('[data-bs-target="#licenze-pane-elenco"]');

    function updateArticoloDefaults() {
        if (!articoloSelect || !prezzoInput || !accessoPreview) return;
        var option = articoloSelect.options[articoloSelect.selectedIndex];
        if (!option || !articoloSelect.value) {
            prezzoInput.value = '0';
            accessoPreview.value = 'Si compila dal catalogo articolo';
            return;
        }

        prezzoInput.value = option.dataset.prezzo || '0';
        accessoPreview.value = option.dataset.accesso || 'Accesso non definito in catalogo';
    }

    articoloSelect?.addEventListener('change', updateArticoloDefaults);
    updateArticoloDefaults();

    function syncLicenzaOwnerFromStructure() {
        if (!strutturaSelect || !proprietarioInput || !proprietarioPreview) return;
        var option = strutturaSelect.options[strutturaSelect.selectedIndex];
        if (!option || !strutturaSelect.value) {
            proprietarioInput.value = '';
            proprietarioPreview.value = '';
            return;
        }

        proprietarioInput.value = option.dataset.proprietario || '';
        proprietarioPreview.value = option.dataset.proprietarioNome || '';
    }

    strutturaSelect?.addEventListener('change', syncLicenzaOwnerFromStructure);
    syncLicenzaOwnerFromStructure();

    document.querySelectorAll('.licenza-edit-struttura').forEach(function (select) {
        function syncEditOwner() {
            var option = select.options[select.selectedIndex];
            var preview = document.getElementById(select.dataset.previewTarget);
            var hidden = document.getElementById(select.dataset.hiddenTarget);
            if (!preview || !hidden) return;

            if (!option || !select.value) {
                preview.value = '';
                hidden.value = '';
                return;
            }

            preview.value = option.dataset.proprietarioNome || '';
            hidden.value = option.dataset.proprietario || '';
        }

        select.addEventListener('change', syncEditOwner);
        syncEditOwner();
    });

    document.getElementById('goNuovaLicenza')?.addEventListener('click', function () {
        if (nuovaLicenzaTrigger) {
            bootstrap.Tab.getOrCreateInstance(nuovaLicenzaTrigger).show();
        }
    });

    document.getElementById('goElencoLicenze')?.addEventListener('click', function () {
        if (elencoLicenzeTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoLicenzeTrigger).show();
        }
    });

    document.getElementById('cancelNuovaLicenza')?.addEventListener('click', function () {
        if (elencoLicenzeTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoLicenzeTrigger).show();
        }
    });

    document.getElementById('goNuovoArticolo')?.addEventListener('click', function () {
        if (nuovoArticoloTrigger) {
            bootstrap.Tab.getOrCreateInstance(nuovoArticoloTrigger).show();
        }
    });

    document.getElementById('goElencoArticoli')?.addEventListener('click', function () {
        if (elencoArticoliTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoArticoliTrigger).show();
        }
    });

    document.getElementById('cancelNuovoArticolo')?.addEventListener('click', function () {
        if (elencoArticoliTrigger) {
            bootstrap.Tab.getOrCreateInstance(elencoArticoliTrigger).show();
        }
    });
});
</script>
@endpush
