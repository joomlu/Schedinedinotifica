@extends('layouts.master')
@section('title') Amministratori @endsection

@php
    $activeTab = old('active_tab', request('tab', 'personale'));
    $oldSelected = collect(old('proprietari', $selectedProprietari ?? []))->map(fn ($value) => (int) $value)->all();
@endphp

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') SuperAdmin @endslot
        @slot('title') {{ $mode === 'create' ? 'Nuovo amministratore' : 'Modifica amministratore' }} @endslot
    @endcomponent

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Controlla i dati della scheda amministratore.</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('superadmin.amministratori.store') : route('superadmin.amministratori.update', $admin->id) }}">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                @endif
                <input type="hidden" name="active_tab" id="admin_active_tab" value="{{ $activeTab }}">

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Proprietari gestiti</div>
                            <div class="display-6 mb-0">{{ $summary['proprietari'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Strutture gestite</div>
                            <div class="display-6 mb-0">{{ $summary['strutture'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small text-uppercase mb-1">Strutture attive</div>
                            <div class="display-6 mb-0">{{ $summary['strutture_attive'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'personale' ? 'active' : '' }}" id="admin-tab-personale" data-bs-toggle="tab" data-bs-target="#admin-pane-personale" type="button" role="tab" aria-controls="admin-pane-personale" aria-selected="{{ $activeTab === 'personale' ? 'true' : 'false' }}">Amministratore</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'ubicazione' ? 'active' : '' }}" id="admin-tab-ubicazione" data-bs-toggle="tab" data-bs-target="#admin-pane-ubicazione" type="button" role="tab" aria-controls="admin-pane-ubicazione" aria-selected="{{ $activeTab === 'ubicazione' ? 'true' : 'false' }}">Ubicazione</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'fiscale' ? 'active' : '' }}" id="admin-tab-fiscale" data-bs-toggle="tab" data-bs-target="#admin-pane-fiscale" type="button" role="tab" aria-controls="admin-pane-fiscale" aria-selected="{{ $activeTab === 'fiscale' ? 'true' : 'false' }}">Fiscale</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'proprietari' ? 'active' : '' }}" id="admin-tab-proprietari" data-bs-toggle="tab" data-bs-target="#admin-pane-proprietari" type="button" role="tab" aria-controls="admin-pane-proprietari" aria-selected="{{ $activeTab === 'proprietari' ? 'true' : 'false' }}">Proprietari</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'servizio' ? 'active' : '' }}" id="admin-tab-servizio" data-bs-toggle="tab" data-bs-target="#admin-pane-servizio" type="button" role="tab" aria-controls="admin-pane-servizio" aria-selected="{{ $activeTab === 'servizio' ? 'true' : 'false' }}">Servizio</button>
                    </li>
                    @if($mode === 'edit')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'fatturazione' ? 'active' : '' }}" id="admin-tab-fatturazione" data-bs-toggle="tab" data-bs-target="#admin-pane-fatturazione" type="button" role="tab" aria-controls="admin-pane-fatturazione" aria-selected="{{ $activeTab === 'fatturazione' ? 'true' : 'false' }}">Proforme</button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade {{ $activeTab === 'personale' ? 'show active' : '' }}" id="admin-pane-personale" role="tabpanel" aria-labelledby="admin-tab-personale">
                        <div class="border rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">Amministratore</h5>
                                    <div class="text-muted">Qui salvi la scheda anagrafica dell amministratore e i riferimenti di contatto principali.</div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome e cognome</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qualifica / ruolo</label>
                                    <input type="text" name="qualifica" class="form-control" value="{{ old('qualifica', $admin->qualifica) }}" placeholder="Es. referente commerciale">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $admin->telefono) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PEC</label>
                                    <input type="email" name="pec" class="form-control" value="{{ old('pec', $admin->pec) }}" placeholder="pec@dominio.it">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password @if($mode==='edit')<small class="text-muted">(lascia vuoto per non cambiare)</small>@else<small class="text-muted">(obbligatoria)</small>@endif</label>
                                    <input type="password" name="password" class="form-control" {{ $mode === 'create' ? 'required' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'ubicazione' ? 'show active' : '' }}" id="admin-pane-ubicazione" role="tabpanel" aria-labelledby="admin-tab-ubicazione">
                        <div class="row g-3">
                            <div class="col-12">
                                <x-geo.italia
                                    title="Ubicazione amministratore"
                                    prefix="admin_geo"
                                    :required="false"
                                    :value="[
                                        'nazione_text' => old('nazione', $admin->nazione ?? 'Italia'),
                                        'regione_text' => old('regione', $admin->regione),
                                        'provincia_text' => old('provincia', $admin->provincia),
                                        'comune_text' => old('citta', $admin->citta),
                                        'cap' => old('cap', $admin->cap),
                                        'cap_text' => old('cap', $admin->cap),
                                        'manual' => (bool) old('geo_manual', $admin->geo_manual ?? false),
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
                                                <input type="text" name="indirizzo" class="form-control" value="{{ old('indirizzo', $admin->indirizzo) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Numero civico</label>
                                                <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $admin->numero_civico) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Latitudine</label>
                                                <input type="text" id="admin_latitudine" name="latitudine" class="form-control" inputmode="decimal" value="{{ old('latitudine', $admin->latitudine) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Longitudine</label>
                                                <input type="text" id="admin_longitudine" name="longitudine" class="form-control" inputmode="decimal" value="{{ old('longitudine', $admin->longitudine) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'fiscale' ? 'show active' : '' }}" id="admin-pane-fiscale" role="tabpanel" aria-labelledby="admin-tab-fiscale">
                        <div class="card mb-0 border-0 shadow-sm">
                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                <i class="ri-file-list-2-line me-2 text-primary"></i>
                                <h5 class="card-title mb-0">Dati fiscali / amministrativi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Ragione sociale</label>
                                        <input type="text" name="ragione_sociale" class="form-control" value="{{ old('ragione_sociale', $admin->ragione_sociale) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Partita IVA</label>
                                        <input type="text" name="partita_iva" class="form-control" inputmode="numeric" maxlength="11" placeholder="11 cifre" value="{{ old('partita_iva', $admin->partita_iva) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Codice fiscale</label>
                                        <input type="text" name="codice_fiscale" class="form-control text-uppercase" maxlength="16" placeholder="16 caratteri" value="{{ old('codice_fiscale', $admin->codice_fiscale) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Codice univoco / destinatario</label>
                                        <input type="text" name="codice_unico" class="form-control text-uppercase" maxlength="7" placeholder="7 caratteri, es. ABC1234" value="{{ old('codice_unico', $admin->codice_unico ?: $admin->codice_destinatario) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Note amministrative</label>
                                        <textarea name="note_amministrative" class="form-control" rows="2" placeholder="Dati utili per fatture, accordi o riferimenti amministrativi">{{ old('note_amministrative', $admin->note_amministrative) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'servizio' ? 'show active' : '' }}" id="admin-pane-servizio" role="tabpanel" aria-labelledby="admin-tab-servizio">
                        @php
                            $serviziAttivi = $servizi->where('attivo', true);
                            $totaleServiziBase = $serviziAttivi->sum(fn ($servizio) => (float) ($servizio->importo ?? 0) * max(1, (int) ($servizio->quantita_default ?? 1)));
                            $quantitaServiziBase = $serviziAttivi->sum(fn ($servizio) => max(1, (int) ($servizio->quantita_default ?? 1)));
                        @endphp
                        <div class="border rounded-3 p-3">
                            <h5 class="mb-1">Servizio e perimetro gestito</h5>
                            <div class="text-muted mb-3">Qui definisci i servizi che Tango fattura a questo amministratore. Ogni voce può avere quantità, prezzo e modalità di costo, così la proforma nasce già con una base coerente.</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="fw-semibold mb-2">Situazione attuale</div>
                                        <div class="small text-muted mb-1">Proprietari assegnati: <span class="text-body fw-semibold">{{ $summary['proprietari'] ?? 0 }}</span></div>
                                        <div class="small text-muted mb-1">Strutture totali gestite: <span class="text-body fw-semibold">{{ $summary['strutture'] ?? 0 }}</span></div>
                                        <div class="small text-muted">Strutture attive: <span class="text-body fw-semibold">{{ $summary['strutture_attive'] ?? 0 }}</span></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="fw-semibold mb-2">Servizi attivi</div>
                                        <div class="small text-muted mb-1">Voci attive: <span class="text-body fw-semibold">{{ $serviziAttivi->count() }}</span></div>
                                        <div class="small text-muted mb-1">Quantità base: <span class="text-body fw-semibold">{{ $quantitaServiziBase }}</span></div>
                                        <div class="small text-muted">Totale base: <span class="text-body fw-semibold">{{ number_format($totaleServiziBase, 2, ',', '.') }}</span></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="fw-semibold mb-2">Servizio concordato</div>
                                        <div class="small text-muted">Stato attuale:</div>
                                        <div class="fw-semibold mb-2">{{ filled($admin->note_servizio) ? 'Attivo' : 'Da definire' }}</div>
                                        <div class="small text-muted">Note di servizio:</div>
                                        <div class="small text-body">{{ filled($admin->note_servizio) ? \Illuminate\Support\Str::limit($admin->note_servizio, 120) : 'Nessuna nota concordata.' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Servizio attuale</label>
                                    <textarea name="note_servizio" class="form-control" rows="2" placeholder="Es. assistenza operativa, onboarding clienti, coordinamento strutture, reperibilità o note contrattuali">{{ old('note_servizio', $admin->note_servizio) }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="card border shadow-sm mb-0">
                                        <div class="card-header bg-light-subtle">
                                            <div class="fw-semibold">Elenco servizi con prezzo</div>
                                        </div>
                                        <div class="card-body">
                                            @if($mode === 'edit')
                                                <div class="table-responsive mb-3">
                                                    <table class="table align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th>Servizio</th>
                                                                <th>Modalità costo</th>
                                                                <th>Quantità</th>
                                                                <th>Importo</th>
                                                                <th>Totale base</th>
                                                                <th>Stato</th>
                                                                <th>Note</th>
                                                                <th class="text-end">Gestione</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($servizi as $servizio)
                                                                @php
                                                                    $servizioTotaleBase = (float) ($servizio->importo ?? 0) * max(1, (int) ($servizio->quantita_default ?? 1));
                                                                @endphp
                                                                <tr data-servizio-row="{{ $servizio->id }}">
                                                                    <td class="fw-semibold">
                                                                        {{ $servizio->nome }}
                                                                        <input type="hidden" name="servizi[{{ $servizio->id }}][delete]" value="0" data-servizio-delete="{{ $servizio->id }}">
                                                                    </td>
                                                                    <td>{{ ucfirst(str_replace('_', ' ', $servizio->tipo_costo)) }}</td>
                                                                    <td>{{ max(1, (int) ($servizio->quantita_default ?? 1)) }}</td>
                                                                    <td>{{ number_format((float) ($servizio->importo ?? 0), 2, ',', '.') }}</td>
                                                                    <td class="fw-semibold">{{ number_format($servizioTotaleBase, 2, ',', '.') }}</td>
                                                                    <td>
                                                                        <span class="badge {{ $servizio->attivo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                                            {{ $servizio->attivo ? 'Attivo' : 'Non attivo' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $servizio->note ?: '—' }}</td>
                                                                    <td class="text-end text-nowrap">
                                                                        <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal" data-bs-target="#servizioModal{{ $servizio->id }}" title="Modifica servizio">
                                                                            <i class="ri-edit-line"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#servizioDeleteModal{{ $servizio->id }}" title="Elimina servizio">
                                                                            <i class="ri-delete-bin-line"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                <div class="modal fade" id="servizioModal{{ $servizio->id }}" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Modifica servizio</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <div class="row g-3">
                                                                                    <div class="col-md-5">
                                                                                        <label class="form-label">Servizio</label>
                                                                                        <input type="text" name="servizi[{{ $servizio->id }}][nome]" class="form-control" value="{{ old("servizi.{$servizio->id}.nome", $servizio->nome) }}">
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <label class="form-label">Modalità costo</label>
                                                                                        <select name="servizi[{{ $servizio->id }}][tipo_costo]" class="form-select">
                                                                                            <option value="per_struttura" @selected(old("servizi.{$servizio->id}.tipo_costo", $servizio->tipo_costo) === 'per_struttura')>Per struttura</option>
                                                                                            <option value="flat" @selected(old("servizi.{$servizio->id}.tipo_costo", $servizio->tipo_costo) === 'flat')>Forfait</option>
                                                                                            <option value="percentuale" @selected(old("servizi.{$servizio->id}.tipo_costo", $servizio->tipo_costo) === 'percentuale')>Percentuale</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label">Quantità</label>
                                                                                        <input type="number" min="1" max="99999" name="servizi[{{ $servizio->id }}][quantita_default]" class="form-control" value="{{ old("servizi.{$servizio->id}.quantita_default", $servizio->quantita_default ?? 1) }}">
                                                                                    </div>
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label">Importo</label>
                                                                                        <input type="number" step="0.01" min="0" name="servizi[{{ $servizio->id }}][importo]" class="form-control" value="{{ old("servizi.{$servizio->id}.importo", $servizio->importo) }}">
                                                                                    </div>
                                                                                    <div class="col-md-12">
                                                                                        <label class="form-label">Note</label>
                                                                                        <textarea name="servizi[{{ $servizio->id }}][note]" class="form-control" rows="3">{{ old("servizi.{$servizio->id}.note", $servizio->note) }}</textarea>
                                                                                    </div>
                                                                                    <div class="col-md-12">
                                                                                        <input type="hidden" name="servizi[{{ $servizio->id }}][attivo]" value="0">
                                                                                        <div class="form-check form-switch">
                                                                                            <input class="form-check-input" type="checkbox" role="switch" id="servizio_attivo_{{ $servizio->id }}" name="servizi[{{ $servizio->id }}][attivo]" value="1" @checked(old("servizi.{$servizio->id}.attivo", $servizio->attivo))>
                                                                                            <label class="form-check-label" for="servizio_attivo_{{ $servizio->id }}">Servizio attivo</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                                                                <button type="button" class="btn btn-success js-submit-keep-tab" data-tab="servizio">Conferma modifiche</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal fade" id="servizioDeleteModal{{ $servizio->id }}" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Elimina servizio</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                Vuoi rimuovere <span class="fw-semibold">{{ $servizio->nome }}</span> dall elenco servizi di questo amministratore?
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                                                                                <button type="button" class="btn btn-danger" data-servizio-remove="{{ $servizio->id }}">Elimina</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="7" class="text-muted">Nessun servizio configurato per questo amministratore.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="border rounded-3 p-3 bg-light-subtle">
                                                    <div class="fw-semibold mb-2">Nuovo servizio</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Servizio</label>
                                                            <input type="text" name="nuovo_servizio_nome" class="form-control" value="{{ old('nuovo_servizio_nome') }}" placeholder="Es. assistenza operativa">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Modalità costo</label>
                                                            <select name="nuovo_servizio_tipo_costo" class="form-select">
                                                                <option value="per_struttura" @selected(old('nuovo_servizio_tipo_costo', 'per_struttura') === 'per_struttura')>Per struttura</option>
                                                                <option value="flat" @selected(old('nuovo_servizio_tipo_costo') === 'flat')>Forfait</option>
                                                                <option value="percentuale" @selected(old('nuovo_servizio_tipo_costo') === 'percentuale')>Percentuale</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Quantità</label>
                                                            <input type="number" min="1" max="99999" name="nuovo_servizio_quantita_default" class="form-control" value="{{ old('nuovo_servizio_quantita_default', 1) }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Importo</label>
                                                            <input type="number" step="0.01" min="0" name="nuovo_servizio_importo" class="form-control" value="{{ old('nuovo_servizio_importo') }}">
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-end">
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" role="switch" checked disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label">Note</label>
                                                            <input type="text" name="nuovo_servizio_note" class="form-control" value="{{ old('nuovo_servizio_note') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-light border mb-0">
                                                    Salva prima l amministratore. Dopo il primo salvataggio potrai creare l elenco dei servizi con prezzo direttamente da questa scheda.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($mode === 'edit')
                        <div class="tab-pane fade {{ $activeTab === 'fatturazione' ? 'show active' : '' }}" id="admin-pane-fatturazione" role="tabpanel" aria-labelledby="admin-tab-fatturazione">
                            <div class="border rounded-3 p-3">
                                <h5 class="mb-1">Proforme servizi</h5>
                                <div class="text-muted mb-3">Qui leggi la base economica dei servizi disponibili per questo amministratore. Ogni proforma può includere solo le voci che scegli tu, quando ti servono.</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 mb-0">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-sm flex-shrink-0">
                                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-4">
                                                            <i class="ri-stack-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-muted small text-uppercase mb-1">Catalogo servizi</div>
                                                        <div class="fs-3 mb-1">{{ $servizi->count() }}</div>
                                                        <div class="small text-muted">Sono i servizi che Tango fattura a questo amministratore dal suo accordo operativo.</div>
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
                                                        <div class="text-muted small text-uppercase mb-1">Base economica</div>
                                                        <div class="fs-3 mb-1">{{ number_format((float) ($fatturazione['totale'] ?? 0), 2, ',', '.') }}</div>
                                                        <div class="small text-muted">Somma teorica del catalogo servizi, utile come base di lettura.</div>
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
                                                            <i class="ri-file-list-3-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-muted small text-uppercase mb-1">Righe di fatturazione</div>
                                                        <div class="fs-3 mb-1">{{ $fatturazione['righe']->count() }}</div>
                                                        <div class="small text-muted">Ogni riga qui sotto è disponibile per le proforme, ma non entra automaticamente nei documenti.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                            <div class="text-muted small">Il dettaglio qui sotto è la base di partenza. Le personalizzazioni vere le fai nella schermata della proforma.</div>
                                            <button
                                                type="button"
                                                class="btn btn-success"
                                                onclick="window.location.href='{{ route('superadmin.amministratori.proforme.create', $admin->id) }}'">
                                                <i class="ri-add-line align-bottom me-1"></i>
                                                Nuova proforma
                                            </button>
                                        </div>
                                        <div class="table-responsive border rounded-3">
                                            <table class="table align-middle mb-0">
                                                <thead>
                                                    <tr class="table-light">
                                                        <th>Servizio</th>
                                                        <th>Modalità</th>
                                                        <th>Quantità</th>
                                                        <th>Prezzo unitario</th>
                                                        <th class="text-end">Totale</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($fatturazione['righe'] as $riga)
                                                        <tr>
                                                            <td>
                                                                <div class="fw-semibold">{{ $riga['descrizione'] }}</div>
                                                                @if(!empty($riga['note']))
                                                                    <div class="small text-muted">{{ $riga['note'] }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_', ' ', $riga['tipo_costo'] ?? 'flat')) }}</span>
                                                            </td>
                                                            <td>{{ $riga['quantita'] }}</td>
                                                            <td>{{ number_format((float) $riga['prezzo_unitario'], 2, ',', '.') }}</td>
                                                            <td class="text-end fw-semibold">{{ number_format((float) $riga['totale'], 2, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">Nessun servizio ancora disponibile per la proforma di questo amministratore.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle">
                                                <div class="fw-semibold">Storico proforme</div>
                                            </div>
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
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-soft-info btn-sm"
                                                                            onclick="window.location.href='{{ route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}'">
                                                                            <i class="ri-eye-line"></i>
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-soft-secondary btn-sm"
                                                                            onclick="window.open('{{ route('superadmin.amministratori.proforme.print', ['id' => $admin->id, 'fatturazione' => $proforma->id]) }}', '_blank')">
                                                                            <i class="ri-printer-line"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="6" class="text-center text-muted py-4">Nessuna proforma creata per questo amministratore.</td>
                                                                </tr>
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

                    <div class="tab-pane fade {{ $activeTab === 'proprietari' ? 'show active' : '' }}" id="admin-pane-proprietari" role="tabpanel" aria-labelledby="admin-tab-proprietari">
                        @php
                            $proprietariInGestione = $proprietari->filter(fn ($proprietario) => in_array((int) $proprietario->id, $oldSelected, true))->values();
                            $proprietariDisponibili = $proprietari->reject(fn ($proprietario) => in_array((int) $proprietario->id, $oldSelected, true))->values();
                        @endphp
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-2">Portafoglio proprietari di questo amministratore</div>
                            <p class="text-muted mb-3">Da qui puoi creare un nuovo proprietario già assegnato a questo amministratore, aggiungere proprietari esistenti al suo portafoglio oppure rimuovere un assegnazione per spostarla su un altro referente.</p>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="text-muted small text-uppercase mb-1">In gestione</div>
                                        <div class="display-6 mb-0">{{ $proprietariInGestione->count() }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="text-muted small text-uppercase mb-1">Strutture dei proprietari</div>
                                        <div class="display-6 mb-0">{{ $proprietariInGestione->sum('strutture_count') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="text-muted small text-uppercase mb-1">Disponibili da assegnare</div>
                                        <div class="display-6 mb-0">{{ $proprietariDisponibili->count() }}</div>
                                    </div>
                                </div>
                            </div>
                            @if($mode !== 'edit')
                                <div class="alert alert-light border mb-3">
                                    Salva prima l amministratore. Dopo il primo salvataggio potrai creare e assegnare nuovi proprietari direttamente da questa scheda.
                                </div>
                            @endif
                            <div class="card border shadow-sm mb-3">
                                <div class="card-header bg-light-subtle d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold">Elenco proprietari in gestione</div>
                                    <span class="text-muted small">Le variazioni si confermano con il salvataggio della scheda.</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Proprietario</th>
                                                    <th>Email</th>
                                                    <th>Telefono</th>
                                                    <th>Strutture</th>
                                                    <th>Stato</th>
                                                    <th class="text-end">Gestione</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($proprietariInGestione as $proprietario)
                                                    <tr data-admin-owner-row="assigned-{{ $proprietario->id }}">
                                                        <td class="fw-semibold">
                                                            {{ $proprietario->nome }}
                                                            <input class="d-none" type="checkbox" name="proprietari[]" value="{{ $proprietario->id }}" id="proprietario_{{ $proprietario->id }}" checked>
                                                        </td>
                                                        <td>{{ $proprietario->email ?: '—' }}</td>
                                                        <td>{{ $proprietario->telefono ?: '—' }}</td>
                                                        <td>{{ $proprietario->strutture_count ?? 0 }}</td>
                                                        <td>
                                                            <span class="badge bg-success-subtle text-success" data-admin-owner-status="{{ $proprietario->id }}">In gestione</span>
                                                        </td>
                                                        <td class="text-end text-nowrap">
                                                            <a href="{{ route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => 'personale']) }}" class="btn btn-soft-info btn-sm" title="Accedi al proprietario">
                                                                <i class="ri-login-box-line"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-soft-danger btn-sm" data-owner-toggle="remove" data-owner-id="{{ $proprietario->id }}" title="Rimuovi assegnazione">
                                                                <i class="ri-user-unfollow-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-muted px-3 py-4">Questo amministratore non ha ancora proprietari assegnati.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="card border shadow-sm mb-0">
                                <div class="card-header bg-light-subtle">
                                    <div class="fw-semibold">Proprietari disponibili o da riassegnare</div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Proprietario</th>
                                                    <th>Email</th>
                                                    <th>Telefono</th>
                                                    <th>Strutture</th>
                                                    <th>Admin attuale</th>
                                                    <th class="text-end">Gestione</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($proprietariDisponibili as $proprietario)
                                                    @php $currentAdmin = optional($proprietario->admin); @endphp
                                                    <tr data-admin-owner-row="available-{{ $proprietario->id }}">
                                                        <td class="fw-semibold">
                                                            {{ $proprietario->nome }}
                                                            <input class="d-none" type="checkbox" name="proprietari[]" value="{{ $proprietario->id }}" id="proprietario_{{ $proprietario->id }}">
                                                        </td>
                                                        <td>{{ $proprietario->email ?: '—' }}</td>
                                                        <td>{{ $proprietario->telefono ?: '—' }}</td>
                                                        <td>{{ $proprietario->strutture_count ?? 0 }}</td>
                                                        <td>
                                                            @if($currentAdmin->name)
                                                                <span class="badge bg-info-subtle text-info">{{ $currentAdmin->name }}</span>
                                                                <div class="small text-muted">{{ $currentAdmin->email }}</div>
                                                            @else
                                                                <span class="text-muted">Nessuno</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-nowrap">
                                                            <a href="{{ route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => 'personale']) }}" class="btn btn-soft-info btn-sm" title="Accedi al proprietario">
                                                                <i class="ri-login-box-line"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-soft-success btn-sm" data-owner-toggle="assign" data-owner-id="{{ $proprietario->id }}" title="Assegna al portafoglio">
                                                                <i class="ri-user-add-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-muted px-3 py-4">Non ci sono proprietari disponibili da assegnare in questo momento.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @if($mode === 'edit')
                                <div class="card border shadow-sm mt-3 mb-0">
                                    <div class="card-header bg-light-subtle">
                                        <div class="fw-semibold">Crea nuovo proprietario se non esiste nell elenco</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-muted small mb-3">Usa questa scheda solo quando il proprietario non esiste ancora. Se è già presente sopra, ti conviene assegnarlo dall elenco dei disponibili.</div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Nome proprietario</label>
                                                <input type="text" name="nuovo_proprietario_nome" class="form-control" value="{{ old('nuovo_proprietario_nome') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="nuovo_proprietario_email" class="form-control" value="{{ old('nuovo_proprietario_email') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Telefono</label>
                                                <input type="text" name="nuovo_proprietario_telefono" class="form-control" value="{{ old('nuovo_proprietario_telefono') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Note</label>
                                                <input type="text" name="nuovo_proprietario_note" class="form-control" value="{{ old('nuovo_proprietario_note') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('superadmin.amministratori.index') }}" class="btn btn-outline-secondary me-2">Annulla</a>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var activeTabInput = document.getElementById('admin_active_tab');
    var geoRoot = document.querySelector('[data-ui="geo-italia"][data-prefix="admin_geo"]');
    var latInput = document.getElementById('admin_latitudine');
    var lngInput = document.getElementById('admin_longitudine');
    var comuneSelect = document.getElementById('admin_geo_comune_id');
    var http = window.http || window.axios || null;

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabButton) {
        tabButton.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target') || '';
            var tabName = target.replace('#admin-pane-', '');
            if (activeTabInput && tabName) {
                activeTabInput.value = tabName;
            }
        });
    });

    function hydrateCoordinates(comuneId) {
        if (!http || !comuneId || !latInput || !lngInput) {
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
                return;
            }

            hydrateCoordinates(comuneId);
        });

        if (comuneSelect && comuneSelect.value && (!latInput.value || !lngInput.value)) {
            hydrateCoordinates(comuneSelect.value);
        }
    }

    document.querySelectorAll('[data-servizio-remove]').forEach(function (button) {
        button.addEventListener('click', function () {
            var servizioId = button.getAttribute('data-servizio-remove');
            var deleteInput = document.querySelector('[data-servizio-delete="' + servizioId + '"]');
            var row = document.querySelector('[data-servizio-row="' + servizioId + '"]');
            if (deleteInput) {
                deleteInput.value = '1';
            }
            if (row) {
                row.style.opacity = '0.45';
                row.style.textDecoration = 'line-through';
            }
            var modal = button.closest('.modal');
            if (modal && window.bootstrap) {
                bootstrap.Modal.getInstance(modal)?.hide();
            }
        });
    });

    document.querySelectorAll('[data-owner-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var ownerId = button.getAttribute('data-owner-id');
            var mode = button.getAttribute('data-owner-toggle');
            var checkbox = document.getElementById('proprietario_' + ownerId);
            var status = document.querySelector('[data-admin-owner-status="' + ownerId + '"]');
            var row = button.closest('tr');

            if (!checkbox) {
                return;
            }

            if (mode === 'assign') {
                checkbox.checked = true;
                if (row) {
                    row.classList.add('table-success');
                }
                button.classList.remove('btn-soft-success');
                button.classList.add('btn-success');
                if (status) {
                    status.textContent = 'Assegnazione confermata al salvataggio';
                    status.className = 'badge bg-success-subtle text-success';
                }
            }

            if (mode === 'remove') {
                checkbox.checked = false;
                if (row) {
                    row.classList.add('table-warning');
                }
                button.classList.remove('btn-soft-danger');
                button.classList.add('btn-warning');
                if (status) {
                    status.textContent = 'Rimozione confermata al salvataggio';
                    status.className = 'badge bg-warning-subtle text-warning';
                }
            }
        });
    });

    document.querySelectorAll('.js-submit-keep-tab').forEach(function (button) {
        button.addEventListener('click', function () {
            var tab = button.getAttribute('data-tab');
            if (activeTabInput && tab) {
                activeTabInput.value = tab;
            }
            var form = button.closest('.modal')?.closest('form') || document.querySelector('form');
            if (form) {
                form.requestSubmit();
            }
        });
    });
});
</script>
@endpush
