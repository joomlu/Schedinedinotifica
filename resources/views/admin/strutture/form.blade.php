@extends('layouts.master')
@section('title') Strutture @endsection

@php
    $owner = $struttura->proprietario;
    $ownerAdmin = $owner?->admin;
    $ownerMailTo = $owner?->email ? 'mailto:' . rawurlencode((string) $owner->email) . '?subject=' . rawurlencode('Scadenza e servizi - ' . ($struttura->nome_struttura ?: 'Struttura')) : null;
    $adminMailTo = $ownerAdmin?->email ? 'mailto:' . rawurlencode((string) $ownerAdmin->email) . '?subject=' . rawurlencode('Coordinamento struttura - ' . ($struttura->nome_struttura ?: 'Struttura')) : null;
    $activeTab = old('active_tab', $activeTab ?? request('tab', 'dati'));
    $licenzeCollection = collect($licenzeAssegnate ?? []);
    $licenzeAttive = $licenzeCollection->where('attiva', true);
    $licenzeDaPagare = $licenzeCollection->where('stato_pagamento', 'da_pagare');
    $prodottiInUso = $licenzeAttive->map(fn ($licenza) => $licenza->articolo?->nome)->filter()->unique()->values();
    $prossimaScadenzaLicenza = $licenzeCollection
        ->filter(fn ($licenza) => filled($licenza->data_scadenza))
        ->sortBy('data_scadenza')
        ->first();
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Admin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuova struttura' : 'Modifica struttura' }} @endslot
    @endcomponent

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($mode === 'edit')
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Proprietario</div>
                            <div class="fw-semibold">{{ $owner?->nome ?: 'Non assegnato' }}</div>
                            <div class="small text-muted">{{ $ownerAdmin?->name ?: 'Nessun admin' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Prodotto in uso</div>
                            <div class="fw-semibold">{{ $prodottiInUso->isNotEmpty() ? $prodottiInUso->join(', ') : 'Nessuna licenza' }}</div>
                            <div class="small text-muted">{{ $licenzeAttive->count() }} licenze attive collegate</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Pagamento / servizio</div>
                            <div class="fw-semibold">{{ $struttura->stato_pagamento ?: 'Non impostato' }}</div>
                            <div class="small text-muted">Piano {{ $struttura->piano ?: 'non definito' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Prossima scadenza</div>
                            <div class="fw-semibold">{{ optional($prossimaScadenzaLicenza?->data_scadenza ?: $struttura->scadenza_servizio)->format('d/m/Y') ?: 'Nessuna data' }}</div>
                            <div class="small text-muted">{{ $prossimaScadenzaLicenza ? 'Scadenza licenza' : 'Scadenza servizio struttura' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <ul class="nav nav-tabs nav-tabs-custom flex-nowrap overflow-auto mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'dati' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#admin-struttura-pane-dati" type="button" role="tab">Dati struttura</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'relazione' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#admin-struttura-pane-relazione" type="button" role="tab">Relazione e pagamenti</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'licenze' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#admin-struttura-pane-licenze" type="button" role="tab">Licenze</button>
                </li>
            </ul>

            <form id="admin-struttura-main-form" method="POST" action="{{ $mode === 'create' ? route('admin.strutture.store') : route('admin.strutture.update', $struttura->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif
                <input type="hidden" name="active_tab" id="admin_struttura_active_tab" value="{{ $activeTab }}">
            </form>

            <div class="tab-content">
                    <div class="tab-pane fade {{ $activeTab === 'dati' ? 'show active' : '' }}" id="admin-struttura-pane-dati" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="border rounded-3 p-3 bg-light-subtle">
                                    <div class="fw-semibold mb-1">Scheda struttura</div>
                                    <div class="text-muted small">Qui salvi i dati principali della struttura. Licenze e pagamenti restano separati nei tab dedicati.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome struttura</label>
                                <input type="text" name="nome_struttura" class="form-control" form="admin-struttura-main-form" value="{{ old('nome_struttura', $struttura->nome_struttura) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Città</label>
                                <input type="text" name="citta" class="form-control" form="admin-struttura-main-form" value="{{ old('citta', $struttura->citta) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Provincia</label>
                                <input type="text" name="provincia" class="form-control" form="admin-struttura-main-form" value="{{ old('provincia', $struttura->provincia) }}">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Proprietario</label>
                                <x-ui.select name="proprietario_id" form="admin-struttura-main-form">
                                    <option value="">-- Seleziona --</option>
                                    @foreach($proprietari as $proprietario)
                                        <option value="{{ $proprietario->id }}" {{ old('proprietario_id', $struttura->proprietario_id) == $proprietario->id ? 'selected' : '' }}>{{ $proprietario->nome }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Attiva</label>
                                <x-ui.select name="attiva" form="admin-struttura-main-form">
                                    <option value="1" {{ old('attiva', $struttura->attiva ?? true) ? 'selected' : '' }}>Sì</option>
                                    <option value="0" {{ old('attiva', $struttura->attiva ?? true) ? '' : 'selected' }}>No</option>
                                </x-ui.select>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'relazione' ? 'show active' : '' }}" id="admin-struttura-pane-relazione" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="card border shadow-sm mb-0 h-100">
                                    <div class="card-header bg-light-subtle">
                                        <div class="fw-semibold">Soggetti collegati</div>
                                    </div>
                                    <div class="card-body">
                                        @if($owner)
                                            <div class="mb-3">
                                                <div class="text-muted small text-uppercase mb-1">Proprietario</div>
                                                <div class="fw-semibold">{{ $owner->nome }}</div>
                                                <div class="small text-muted">{{ $owner->email ?: 'Nessuna email' }}</div>
                                                <div class="small text-muted">{{ $owner->telefono ?: 'Nessun telefono' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-muted small text-uppercase mb-1">Amministratore di riferimento</div>
                                                <div class="fw-semibold">{{ $ownerAdmin?->name ?: 'Non assegnato' }}</div>
                                                <div class="small text-muted">{{ $ownerAdmin?->email ?: 'Nessuna email' }}</div>
                                                <div class="small text-muted">{{ $ownerAdmin?->telefono ?: 'Nessun telefono' }}</div>
                                            </div>
                                        @else
                                            <div class="alert alert-info mb-0">Questa struttura non ha ancora un proprietario assegnato. Puoi salvarla e collegarla più tardi.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border shadow-sm mb-0 h-100">
                                    <div class="card-header bg-light-subtle">
                                        <div class="fw-semibold">Situazione commerciale</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                    <div class="text-muted small text-uppercase mb-1">Stato pagamento</div>
                                                    <div class="fw-semibold">{{ $struttura->stato_pagamento ?: 'Non impostato' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                    <div class="text-muted small text-uppercase mb-1">Piano</div>
                                                    <div class="fw-semibold">{{ $struttura->piano ?: 'Non definito' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                    <div class="text-muted small text-uppercase mb-1">Scadenza servizio</div>
                                                    <div class="fw-semibold">{{ $struttura->scadenza_servizio?->format('d/m/Y') ?: 'Non impostata' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border shadow-sm mb-0 h-100">
                                    <div class="card-header bg-light-subtle">
                                        <div class="fw-semibold">Aggiorna servizio e controllo</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Scadenza servizio</label>
                                                <x-calendario name="scadenza_servizio" variant="single" form="admin-struttura-main-form" :value="old('scadenza_servizio', optional($struttura->scadenza_servizio)->format('Y-m-d'))" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Piano</label>
                                                <input type="text" name="piano" class="form-control" form="admin-struttura-main-form" value="{{ old('piano', $struttura->piano) }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Stato pagamento</label>
                                                <input type="text" name="stato_pagamento" class="form-control" form="admin-struttura-main-form" value="{{ old('stato_pagamento', $struttura->stato_pagamento) }}">
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-info mb-0">
                                                    Questa sezione governa il controllo commerciale della struttura. Le singole licenze con prezzo, tracking e scadenza restano nel tab <strong>Licenze</strong>.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border shadow-sm mb-0">
                                    <div class="card-header bg-light-subtle">
                                        <div class="fw-semibold">Comunicazioni rapide</div>
                                    </div>
                                    <div class="card-body d-flex flex-wrap gap-2">
                                        @if($ownerMailTo)
                                            <a href="{{ $ownerMailTo }}" class="btn btn-soft-primary">Scrivi al proprietario</a>
                                        @endif
                                        @if($adminMailTo)
                                            <a href="{{ $adminMailTo }}" class="btn btn-soft-info">Scrivi all amministratore</a>
                                        @endif
                                        <a href="{{ route('admin.pagamenti.index', ['tab' => 'conto', 'conto_struttura_id' => $struttura->id]) }}" class="btn btn-soft-secondary">Apri stato conto struttura</a>
                                        <a href="{{ route('calendario.index', ['contesto' => 'struttura', 'sid' => $struttura->id]) }}" class="btn btn-soft-warning">Apri calendario struttura</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="tab-pane fade {{ $activeTab === 'licenze' ? 'show active' : '' }}" id="admin-struttura-pane-licenze" role="tabpanel">
                        @if($mode === 'create')
                            <div class="alert alert-info mb-0">Salva prima la struttura. Dopo il primo salvataggio potrai assegnare qui le licenze e vedere i pagamenti collegati.</div>
                        @else
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small text-uppercase mb-1">Licenze attive</div>
                                        <div class="fw-bold fs-4">{{ $licenzeAttive->count() }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small text-uppercase mb-1">Da pagare</div>
                                        <div class="fw-bold fs-4 text-danger">{{ $licenzeDaPagare->count() }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small text-uppercase mb-1">Totale licenze</div>
                                        <div class="fw-bold fs-4">{{ number_format((float) $licenzeCollection->sum('prezzo'), 2, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-5">
                                            <div class="text-muted small text-uppercase mb-1">Prodotti / licenze in uso</div>
                                            <div class="fw-semibold">{{ $prodottiInUso->isNotEmpty() ? $prodottiInUso->join(', ') : 'Nessun prodotto attivo' }}</div>
                                            <div class="small text-muted mt-1">Ogni licenza assegnata a questa struttura definisce il prodotto che la struttura sta usando.</div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="text-muted small text-uppercase mb-1">Prossima scadenza</div>
                                            <div class="fw-semibold">{{ optional($prossimaScadenzaLicenza?->data_scadenza)->format('d/m/Y') ?: 'Nessuna scadenza licenza' }}</div>
                                            <div class="small text-muted mt-1">{{ $prossimaScadenzaLicenza?->articolo?->nome ?: 'Nessuna licenza in scadenza' }}</div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="text-muted small text-uppercase mb-1">Importo licenze</div>
                                            <div class="fw-semibold">{{ number_format((float) $licenzeCollection->sum('prezzo'), 2, ',', '.') }}</div>
                                            <div class="small text-muted mt-1">{{ $licenzeDaPagare->count() }} licenze ancora da pagare</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border shadow-sm mb-3">
                                <div class="card-header bg-light-subtle">
                                    <div class="fw-semibold">Elenco licenze struttura</div>
                                    <div class="small text-muted">Qui controlli tracking, prezzo, stato, scadenza e azioni della singola licenza collegata a questa struttura.</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Licenza</th>
                                                    <th>Tracking</th>
                                                    <th>Prezzo</th>
                                                    <th>Stato</th>
                                                    <th>Scadenza</th>
                                                    <th class="text-end">Gestione</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($licenzeAssegnate as $assegnazione)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $assegnazione->articolo?->nome }}</div>
                                                            <div class="small text-muted">{{ $assegnazione->numero_licenza ?: '—' }}</div>
                                                        </td>
                                                        <td class="small">{{ $assegnazione->codice_tracking }}</td>
                                                        <td class="fw-semibold">{{ number_format((float) $assegnazione->prezzo, 2, ',', '.') }}</td>
                                                        <td>
                                                            <span class="badge {{ $assegnazione->stato_pagamento === 'pagato' ? 'bg-success-subtle text-success' : ($assegnazione->stato_pagamento === 'parziale' ? 'bg-warning-subtle text-warning' : ($assegnazione->stato_pagamento === 'sospeso' ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                                                {{ ucfirst(str_replace('_', ' ', $assegnazione->stato_pagamento)) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional($assegnazione->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
                                                        <td class="text-end">
                                                            <div class="d-inline-flex gap-1">
                                                                <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdminStrutturaLicenzaEdit{{ $assegnazione->id }}" title="Modifica licenza">
                                                                    <i class="ri-edit-line fs-16 align-middle"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-soft-secondary btn-sm" onclick="window.open('{{ route('admin.pagamenti.licenze.print', $assegnazione->id) }}', '_blank')" title="Stampa licenza">
                                                                    <i class="ri-printer-line fs-16 align-middle"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdminStrutturaLicenzaDelete{{ $assegnazione->id }}" title="Elimina licenza">
                                                                    <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <div class="modal fade" id="modalAdminStrutturaLicenzaEdit{{ $assegnazione->id }}" tabindex="-1" aria-labelledby="modalAdminStrutturaLicenzaEditLabel{{ $assegnazione->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <div>
                                                                        <h5 class="modal-title mb-1" id="modalAdminStrutturaLicenzaEditLabel{{ $assegnazione->id }}">Modifica licenza struttura</h5>
                                                                        <div class="small text-muted">{{ $assegnazione->numero_licenza ?: 'Licenza senza numero' }} · {{ $assegnazione->codice_tracking }}</div>
                                                                    </div>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                                </div>
                                                                <form method="POST" action="{{ route('admin.pagamenti.licenze.update', $assegnazione->id) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-body">
                                                                        <div class="row g-3">
                                                                            <div class="col-md-8">
                                                                                <label class="form-label">Articolo / licenza</label>
                                                                                <select name="articolo_id" class="form-select" required>
                                                                                    @foreach($articoli as $articolo)
                                                                                        <option value="{{ $articolo->id }}" @selected($assegnazione->articolo_id === $articolo->id)>{{ $articolo->parent ? $articolo->parent->nome.' / ' : '' }}{{ $articolo->nome }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Quantita</label>
                                                                                <input type="number" name="quantita" class="form-control" min="1" max="99999" value="{{ $assegnazione->quantita }}">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Proprietario</label>
                                                                                <input type="text" class="form-control" value="{{ $struttura->proprietario?->nome ?: 'Nessun proprietario' }}" readonly>
                                                                                <input type="hidden" name="proprietario_id" value="{{ $assegnazione->proprietario_id }}">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label">Struttura</label>
                                                                                <input type="text" class="form-control" value="{{ $struttura->nome_struttura }}" readonly>
                                                                                <input type="hidden" name="struttura_id" value="{{ $assegnazione->struttura_id }}">
                                                                            </div>
                                                                            <div class="col-md-3">
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
                                                                        <input type="hidden" name="return_to_structure_id" value="{{ $struttura->id }}">
                                                                        <input type="hidden" name="active_tab" value="licenze">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Chiudi</button>
                                                                        <button type="submit" class="btn btn-primary btn-sm">Salva licenza</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal fade" id="modalAdminStrutturaLicenzaDelete{{ $assegnazione->id }}" tabindex="-1" aria-labelledby="modalAdminStrutturaLicenzaDeleteLabel{{ $assegnazione->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalAdminStrutturaLicenzaDeleteLabel{{ $assegnazione->id }}">Elimina licenza struttura</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="fw-semibold mb-1">{{ $assegnazione->articolo?->nome }}</div>
                                                                    <div class="small text-muted mb-2">{{ $assegnazione->numero_licenza ?: 'Licenza senza numero' }} · {{ $assegnazione->codice_tracking }}</div>
                                                                    <p class="mb-0">Vuoi eliminare questa licenza collegata alla struttura {{ $struttura->nome_struttura }}?</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Annulla</button>
                                                                    <form method="POST" action="{{ route('admin.pagamenti.licenze.destroy', $assegnazione->id) }}">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="return_to_structure_id" value="{{ $struttura->id }}">
                                                                        <input type="hidden" name="active_tab" value="licenze">
                                                                        <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted">Nessuna licenza collegata a questa struttura.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>



                            <div class="card border shadow-sm mb-3">
                                <div class="card-header bg-light-subtle">
                                    <div class="fw-semibold">Movimenti e documenti della struttura</div>
                                    <div class="small text-muted">Qui vedi se questa struttura e gia entrata in proforme o documenti del proprietario. In questo modo la struttura resta collegata al conto generale del proprietario.</div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr class="table-light">
                                                    <th>Documento</th>
                                                    <th>Data</th>
                                                    <th>Proprietario</th>
                                                    <th>Voci struttura</th>
                                                    <th>Stato</th>
                                                    <th class="text-end">Totale documento</th>
                                                    <th class="text-end">Gestione</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($movimentiStruttura ?? collect()) as $movimento)
                                                    @php
                                                        $righeStruttura = $movimento->righe->where('struttura_id', $struttura->id);
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-semibold">{{ $movimento->numero }}</td>
                                                        <td>{{ optional($movimento->data_documento)->format('d/m/Y') ?: '—' }}</td>
                                                        <td>{{ $movimento->proprietario?->nome ?: '—' }}</td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $righeStruttura->count() }}</div>
                                                            <div class="small text-muted">{{ $righeStruttura->pluck('descrizione')->filter()->unique()->take(2)->join(', ') ?: 'Nessun dettaglio' }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{
                                                                $movimento->stato === 'fatturata' ? 'bg-success-subtle text-success' :
                                                                ($movimento->stato === 'chiusa' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info')
                                                            }}">
                                                                {{ ucfirst($movimento->stato) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end fw-semibold">{{ number_format((float) $movimento->totale, 2, ',', '.') }}</td>
                                                        <td class="text-end">
                                                            <button
                                                                type="button"
                                                                class="btn btn-soft-secondary btn-sm"
                                                                onclick="window.location.href='{{ route('admin.proprietari.proforme.show', ['proprietario' => $movimento->proprietario_id, 'proforma' => $movimento->id]) }}'"
                                                                title="Apri documento"
                                                            >
                                                                <i class="ri-external-link-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4 text-muted">Nessun documento del proprietario include ancora questa struttura.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card border shadow-sm mb-0">
                                <div class="card-header bg-light-subtle">
                                    <div class="fw-semibold">Assegna nuova licenza alla struttura</div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.pagamenti.licenze.store') }}" class="row g-3">
                                        @csrf
                                        <input type="hidden" name="return_to_structure_id" value="{{ $struttura->id }}">
                                        <input type="hidden" name="active_tab" value="licenze">
                                        <input type="hidden" name="proprietario_id" value="{{ $struttura->proprietario_id }}">
                                        <input type="hidden" name="struttura_id" value="{{ $struttura->id }}">

                                        <div class="col-md-6">
                                            <label class="form-label">Articolo / licenza</label>
                                            <select name="articolo_id" class="form-select" id="admin_struttura_licenza_articolo" required>
                                                <option value="">Seleziona articolo</option>
                                                @foreach($articoli as $articolo)
                                                    <option value="{{ $articolo->id }}" data-prezzo="{{ $articolo->prezzo_base }}" data-accesso="{{ $articolo->accesso_key ?: ($articolo->codice ?: 'Accesso non definito') }}">{{ $articolo->parent ? $articolo->parent->nome.' / ' : '' }}{{ $articolo->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Accesso licenza</label>
                                            <input type="text" id="admin_struttura_licenza_accesso" class="form-control" value="Si compila dal catalogo articolo" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantita</label>
                                            <input type="number" name="quantita" class="form-control" min="1" value="1">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prezzo</label>
                                            <input type="number" name="prezzo" id="admin_struttura_licenza_prezzo" class="form-control" step="0.01" min="0" value="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Attiva</label>
                                            <select name="attiva" class="form-select">
                                                <option value="1">Sì</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Stato pagamento</label>
                                            <select name="stato_pagamento" class="form-select">
                                                @foreach($statiLicenza as $statoLicenza)
                                                    <option value="{{ $statoLicenza }}">{{ ucfirst(str_replace('_', ' ', $statoLicenza)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Data inizio</label>
                                            <x-calendario name="data_inizio" variant="single" placeholder="gg/mm/aaaa" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Data scadenza</label>
                                            <x-calendario name="data_scadenza" variant="single" placeholder="gg/mm/aaaa" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Note</label>
                                            <textarea name="note" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">Assegna licenza</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.strutture.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" form="admin-struttura-main-form" class="btn btn-success">Salva</button>
                </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var articoloSelect = document.getElementById('admin_struttura_licenza_articolo');
    var prezzoInput = document.getElementById('admin_struttura_licenza_prezzo');
    var accessoInput = document.getElementById('admin_struttura_licenza_accesso');
    var activeTabInput = document.getElementById('admin_struttura_active_tab');

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabButton) {
        tabButton.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target') || '';
            var tabName = target.replace('#admin-struttura-pane-', '');
            if (activeTabInput && tabName) activeTabInput.value = tabName;
        });
    });

    function updateStrutturaLicenzaFields() {
        if (!articoloSelect || !prezzoInput || !accessoInput) return;
        var selected = articoloSelect.options[articoloSelect.selectedIndex];
        if (!selected || !articoloSelect.value) {
            prezzoInput.value = '0';
            accessoInput.value = 'Si compila dal catalogo articolo';
            return;
        }

        prezzoInput.value = selected.dataset.prezzo || '0';
        accessoInput.value = selected.dataset.accesso || 'Accesso non definito in catalogo';
    }

    articoloSelect?.addEventListener('change', updateStrutturaLicenzaFields);
    updateStrutturaLicenzaFields();
});
</script>
@endpush
