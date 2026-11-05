@extends('layouts.master')

@section('title', 'Scheda CRM')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') CRM @endslot
    @slot('title') {{ $lead->lead_code }} @endslot
@endcomponent

@php
    $activeTab = request('tab', old('tipo') || old('direzione') ? 'comunicazioni' : (old('stato') || old('assigned_admin_id') ? 'gestione' : 'contatto'));
    $agendaAperta = $lead->activities->filter(fn ($activity) => $activity->scheduled_at && !in_array($activity->stato, ['completata', 'annullata'], true))->sortBy('scheduled_at');
    $agendaStorico = $lead->activities->filter(fn ($activity) => $activity->scheduled_at && in_array($activity->stato, ['completata', 'annullata'], true))->sortByDesc('scheduled_at');
    $comunicazioniAperte = $lead->activities->filter(fn ($activity) => !$activity->scheduled_at && !in_array($activity->stato, ['completata', 'annullata'], true));
    $comunicazioniStorico = $lead->activities->filter(fn ($activity) => !$activity->scheduled_at && in_array($activity->stato, ['completata', 'annullata'], true));
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-info text-white">CRM</span>
                    <span class="badge {{ $lead->statoBadgeClass() }}">{{ $lead->statoLabel() }}</span>
                </div>
                <h5 class="card-title mb-1">Quadro contatto</h5>
                <p class="text-muted mb-0">Scheda completa del lead con dati, richiesta ricevuta, comunicazioni, follow-up e stato commerciale.</p>
            </div>
            <a href="{{ route($routePrefix . '.index') }}" class="btn btn-light btn-sm">Torna al CRM</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Struttura</div>
                    <div class="fw-semibold mt-1">{{ $lead->displayStrutturaName() }}</div>
                    @if($lead->linkedStruttura?->proprietario?->ragione_sociale)
                        <div class="small text-muted mt-2">{{ $lead->linkedStruttura->proprietario->ragione_sociale }}</div>
                    @endif
                    <div class="small text-muted mt-2">{{ $lead->localita ?: 'Località non indicata' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Contatto</div>
                    <div class="fw-semibold mt-1">{{ $lead->nome_cognome }}</div>
                    <div class="small text-muted mt-2">{{ $lead->persona_contatto ?: 'Nessun referente secondario' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Stato</div>
                    <div class="fw-semibold mt-1">{{ $lead->statoLabel() }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Admin</div>
                    <div class="fw-semibold mt-1">{{ $lead->assignedAdmin?->displayLabel() ?: 'Non assegnato' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Prossimo contatto</div>
                    <div class="fw-semibold mt-1">{{ optional($lead->prossimo_contatto_at)->format('d/m/Y H:i') ?: 'Non fissato' }}</div>
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
                    <button class="nav-link {{ $activeTab === 'contatto' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-show-pane-contatto" type="button" role="tab" aria-selected="{{ $activeTab === 'contatto' ? 'true' : 'false' }}">Dati contatto</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'richiesta' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-show-pane-richiesta" type="button" role="tab" aria-selected="{{ $activeTab === 'richiesta' ? 'true' : 'false' }}">Richiesta</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'comunicazioni' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-show-pane-comunicazioni" type="button" role="tab" aria-selected="{{ $activeTab === 'comunicazioni' ? 'true' : 'false' }}">Comunicazioni</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'agenda' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-show-pane-agenda" type="button" role="tab" aria-selected="{{ $activeTab === 'agenda' ? 'true' : 'false' }}">Agenda CRM</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'gestione' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-show-pane-gestione" type="button" role="tab" aria-selected="{{ $activeTab === 'gestione' ? 'true' : 'false' }}">Gestione</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'contatto' ? 'show active' : '' }}" id="crm-show-pane-contatto" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Contatti diretti</h5>
                                <p class="text-muted mb-0">Riferimenti completi del cliente e del contatto commerciale.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <span class="text-muted small d-block">Struttura</span>
                                        <span class="fw-semibold">{{ $lead->displayStrutturaName() }}</span>
                                        @if($lead->linkedStruttura?->proprietario?->ragione_sociale)
                                            <span class="d-block small text-muted mt-1">{{ $lead->linkedStruttura->proprietario->ragione_sociale }}</span>
                                        @endif
                                    </div>
                                    <div class="col-6"><span class="text-muted small d-block">Nome e cognome</span><span class="fw-semibold">{{ $lead->nome_cognome }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Persona di contatto</span><span class="fw-semibold">{{ $lead->persona_contatto ?: '—' }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Email</span><span class="fw-semibold">{{ $lead->email }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Località</span><span class="fw-semibold">{{ $lead->localita ?: '—' }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Telefono</span><span class="fw-semibold">{{ $lead->telefono ?: '—' }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Cellulare</span><span class="fw-semibold">{{ $lead->cellulare ?: '—' }}</span></div>
                                    <div class="col-12"><span class="text-muted small d-block">Sito internet</span><span class="fw-semibold">{{ $lead->sito_web ?: '—' }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-0">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Preferenze</h5>
                                <p class="text-muted mb-0">Modalità richiesta, preferenza di contatto e provenienza del lead.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6"><span class="text-muted small d-block">Fonte</span><span class="fw-semibold">{{ $lead->fonteLabel() }}</span></div>
                                    <div class="col-6"><span class="text-muted small d-block">Modalità contatto</span><span class="fw-semibold">{{ $lead->modalita_contatto ?: '—' }}</span></div>
                                    <div class="col-12"><span class="text-muted small d-block">Preferenza</span><span class="fw-semibold">{{ $lead->preferenza_contatto_label ?: ($lead->qualsiasi_orario ? 'Qualsiasi orario' : '—') }}</span></div>
                                    <div class="col-12"><span class="text-muted small d-block">Data richiesta</span><span class="fw-semibold">{{ $lead->created_at->format('d/m/Y H:i') }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'richiesta' ? 'show active' : '' }}" id="crm-show-pane-richiesta" role="tabpanel">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Richiesta iniziale</h5>
                        <p class="text-muted mb-0">Qui rimane la richiesta completa inviata dal sito, così non perdi mai il motivo originale del contatto.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="rounded-3 border p-3 h-100">
                                    <div class="text-muted small">Motivo</div>
                                    <div class="fw-semibold mt-1">{{ $lead->modalita_contatto ?: 'Richiesta generale' }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="rounded-3 border p-3 h-100">
                                    <div class="text-muted small">Codice</div>
                                    <div class="fw-semibold mt-1">{{ $lead->lead_code }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="rounded-3 border p-3 h-100">
                                    <div class="text-muted small">Ricevuta il</div>
                                    <div class="fw-semibold mt-1">{{ $lead->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="rounded-3 border p-3">
                                    <div class="text-muted small mb-2">Testo completo della richiesta</div>
                                    <div class="small">{{ $lead->messaggio ?: 'Nessun messaggio salvato.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'comunicazioni' ? 'show active' : '' }}" id="crm-show-pane-comunicazioni" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Nuova comunicazione</h5>
                        <p class="text-muted mb-0">Registra richiesta in entrata, risposta in uscita o nota interna. Qui nasce il vero storico del rapporto col cliente.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePrefix . '.attivita.store', $lead->id) }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="tab" value="comunicazioni">
                            <div class="col-lg-3">
                                <label class="form-label">Direzione</label>
                                <x-ui.select name="direzione">
                                    @foreach($direzioniAttivita as $key => $label)
                                        <option value="{{ $key }}" @selected(old('direzione', 'uscita') === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Tipo</label>
                                <x-ui.select name="tipo">
                                    @foreach($tipiAttivita as $key => $label)
                                        <option value="{{ $key }}" @selected(old('tipo') === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Stato attività</label>
                                <x-ui.select name="stato">
                                    @foreach($statiAttivita as $key => $label)
                                        <option value="{{ $key }}" @selected(old('stato', 'registrata') === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Titolo</label>
                                <input type="text" name="titolo" class="form-control" value="{{ old('titolo') }}" placeholder="Risposta, ticket, chiamata..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Testo / nota / risposta</label>
                                <textarea name="descrizione" rows="5" class="form-control" placeholder="Descrivi cosa è stato chiesto, cosa hai risposto, cosa resta da fare, decisioni prese o feedback del cliente.">{{ old('descrizione') }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Salva comunicazione</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Storico comunicazioni</h5>
                        <p class="text-muted mb-0">Qui vedi tutto il dialogo: richiesta dal sito, risposte, ticket, note e passaggi commerciali fino alla vendita.</p>
                    </div>
                    <div class="card-body">
                                <h6 class="fw-semibold mb-3">Comunicazioni aperte</h6>
                                @forelse($comunicazioniAperte as $activity)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <span class="badge bg-info-subtle text-info">{{ $activity->tipoLabel() }}</span>
                                                    <span class="badge {{ $activity->direzione === 'entrata' ? 'bg-warning-subtle text-warning' : ($activity->direzione === 'uscita' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary') }}">{{ $activity->direzioneLabel() }}</span>
                                                    <span class="badge {{ $activity->stato === 'completata' ? 'bg-success-subtle text-success' : ($activity->stato === 'annullata' ? 'bg-danger-subtle text-danger' : 'bg-light text-body') }}">{{ $activity->statoLabel() }}</span>
                                                </div>
                                                <div class="fw-semibold">{{ $activity->titolo }}</div>
                                                <div class="text-muted small">
                                                    {{ $activity->created_at->format('d/m/Y H:i') }}
                                                    @if($activity->user)
                                                        · {{ $activity->user->displayLabel() }}
                                                    @endif
                                                </div>
                                                @if($activity->descrizione)
                                                    <div class="mt-2 small">{{ $activity->descrizione }}</div>
                                                @endif
                                            </div>
                                            <div>
                                                <form method="POST" action="{{ route($routePrefix . '.attivita.stato', [$lead->id, $activity->id]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="tab" value="comunicazioni">
                                                    <input type="hidden" name="stato" value="{{ $activity->stato === 'completata' ? 'da_fare' : 'completata' }}">
                                                    <button type="submit" class="btn btn-sm {{ $activity->stato === 'completata' ? 'btn-light' : 'btn-soft-success' }}">
                                                        {{ $activity->stato === 'completata' ? 'Riapri' : 'Completa' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted mb-4">Nessuna comunicazione aperta per questo contatto.</div>
                                @endforelse

                                <h6 class="fw-semibold mb-3 mt-4">Storico chiuso</h6>
                                @forelse($comunicazioniStorico as $activity)
                                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <span class="badge bg-info-subtle text-info">{{ $activity->tipoLabel() }}</span>
                                                    <span class="badge {{ $activity->direzione === 'entrata' ? 'bg-warning-subtle text-warning' : ($activity->direzione === 'uscita' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary') }}">{{ $activity->direzioneLabel() }}</span>
                                                    <span class="badge {{ $activity->stato === 'completata' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $activity->statoLabel() }}</span>
                                                </div>
                                                <div class="fw-semibold">{{ $activity->titolo }}</div>
                                                <div class="text-muted small">{{ $activity->created_at->format('d/m/Y H:i') }} @if($activity->user) · {{ $activity->user->displayLabel() }} @endif</div>
                                                @if($activity->descrizione)
                                                    <div class="mt-2 small">{{ $activity->descrizione }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">Nessuna comunicazione chiusa nello storico.</div>
                                @endforelse
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'agenda' ? 'show active' : '' }}" id="crm-show-pane-agenda" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Nuovo follow-up</h5>
                        <p class="text-muted mb-0">Piccola agenda interna del CRM per demo, richiami, riunioni e scadenze commerciali.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePrefix . '.attivita.store', $lead->id) }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="tab" value="agenda">
                            <input type="hidden" name="direzione" value="interna">
                            <input type="hidden" name="stato" value="da_fare">
                            <div class="col-lg-3">
                                <label class="form-label">Tipo</label>
                                <x-ui.select name="tipo">
                                    <option value="riunione">Riunione</option>
                                    <option value="demo">Demo</option>
                                    <option value="telefono">Telefonata</option>
                                    <option value="ticket">Ticket</option>
                                    <option value="nota">Nota</option>
                                </x-ui.select>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label">Titolo agenda</label>
                                <input type="text" name="titolo" class="form-control" placeholder="Demo completa con la struttura" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Giorno</label>
                                <x-calendario name="scheduled_data" variant="single" :value="old('scheduled_data')" />
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Ora</label>
                                <input type="time" name="scheduled_ora" class="form-control" value="{{ old('scheduled_ora', '09:00') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nota operativa</label>
                                <textarea name="descrizione" rows="4" class="form-control" placeholder="Cosa va preparato, chi chiamare, quali punti mostrare nella demo...">{{ old('descrizione') }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Aggiungi all agenda</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Agenda del contatto</h5>
                        <p class="text-muted mb-0">Qui rimangono solo i promemoria, gli appuntamenti e le attività pianificate su questo lead.</p>
                    </div>
                    <div class="card-body">
                                <h6 class="fw-semibold mb-3">Agenda aperta</h6>
                                @forelse($agendaAperta as $activity)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $activity->titolo }}</div>
                                                <div class="text-muted small">{{ $activity->scheduled_at?->format('d/m/Y H:i') }}</div>
                                                <div class="small mt-2">{{ $activity->descrizione ?: 'Nessuna nota operativa.' }}</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-info-subtle text-info">{{ $activity->tipoLabel() }}</span>
                                                <div class="mt-2">
                                                    <form method="POST" action="{{ route($routePrefix . '.attivita.stato', [$lead->id, $activity->id]) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="tab" value="agenda">
                                                        <input type="hidden" name="stato" value="{{ $activity->stato === 'completata' ? 'da_fare' : 'completata' }}">
                                                        <button type="submit" class="btn btn-sm {{ $activity->stato === 'completata' ? 'btn-light' : 'btn-soft-success' }}">
                                                            {{ $activity->stato === 'completata' ? 'Riapri' : 'Completata' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted mb-4">Nessun appuntamento CRM aperto su questo contatto.</div>
                                @endforelse

                                <h6 class="fw-semibold mb-3 mt-4">Storico agenda</h6>
                                @forelse($agendaStorico as $activity)
                                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $activity->titolo }}</div>
                                                <div class="text-muted small">{{ $activity->scheduled_at?->format('d/m/Y H:i') }}</div>
                                                <div class="small mt-2">{{ $activity->descrizione ?: 'Nessuna nota operativa.' }}</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-info-subtle text-info">{{ $activity->tipoLabel() }}</span>
                                                <div class="small text-muted mt-2">{{ $activity->statoLabel() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">Nessun elemento nello storico agenda.</div>
                                @endforelse
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'gestione' ? 'show active' : '' }}" id="crm-show-pane-gestione" role="tabpanel">
                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Gestione commerciale</h5>
                        <p class="text-muted mb-0">Stato della trattativa, assegnazione dell admin, prossimo contatto e note interne di coordinamento.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePrefix . '.update', $lead->id) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="tab" value="gestione">
                            <div class="col-lg-4">
                                <label class="form-label">Stato</label>
                                <x-ui.select name="stato">
                                    @foreach($stati as $key => $label)
                                        <option value="{{ $key }}" @selected(old('stato', $lead->stato) === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Admin assegnato</label>
                                <x-ui.select name="assigned_admin_id" placeholder="Non assegnato" allowClear="true">
                                    <option value=""></option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" @selected((string) old('assigned_admin_id', $lead->assigned_admin_id) === (string) $admin->id)>{{ $admin->displayLabel() }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Struttura registrata</label>
                                <x-ui.select name="struttura_id" placeholder="Collega una struttura del sistema" allowClear="true">
                                    <option value=""></option>
                                    @foreach($struttureOptions as $strutturaOption)
                                        <option value="{{ $strutturaOption->id }}" @selected((string) old('struttura_id', $lead->struttura_id) === (string) $strutturaOption->id)>
                                            {{ $strutturaOption->nome_struttura }}
                                            @if($strutturaOption->proprietario?->ragione_sociale)
                                                · {{ $strutturaOption->proprietario->ragione_sociale }}
                                            @endif
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Prossimo contatto</label>
                                <x-calendario name="prossimo_contatto_data" variant="single" :value="old('prossimo_contatto_data', optional($lead->prossimo_contatto_at)->toDateString())" />
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Ora</label>
                                <input type="time" name="prossimo_contatto_ora" class="form-control" value="{{ old('prossimo_contatto_ora', optional($lead->prossimo_contatto_at)->format('H:i') ?: '09:00') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Note interne</label>
                                <textarea name="note_interne" rows="7" class="form-control" placeholder="Strategia commerciale, cosa è stato promesso, punti critici, preventivo, esito della trattativa...">{{ old('note_interne', $lead->note_interne) }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Salva gestione CRM</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
