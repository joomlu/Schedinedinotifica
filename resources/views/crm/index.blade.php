@extends('layouts.master')

@section('title', 'CRM contatti')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') CRM @endslot
    @slot('title') Contatti commerciali @endslot
@endcomponent

@php
    $contesto = $isSuperAdmin ? 'SuperAdmin' : 'Admin';
    $activeTab = request('tab', 'contatti') === 'agenda' ? 'agenda' : 'contatti';
    $giorniSettimanaCrm = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    $gridStartCrm = $selectedMonth->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
    $gridEndCrm = $selectedMonth->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $calendarWeeksCrm = collect(\Carbon\CarbonPeriod::create($gridStartCrm, '1 day', $gridEndCrm))->chunk(7);
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Quadro CRM</h5>
        <p class="text-muted mb-0">Richieste dal sito, contatti commerciali, comunicazioni, agenda e piccoli ticket di relazione in un solo spazio di lavoro.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Contesto</div>
                    <div class="fw-semibold mt-1">{{ $contesto }}</div>
                    <div class="small text-muted mt-2">CRM commerciale integrato al sito</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Nuovi</div>
                    <div class="fw-semibold mt-1">{{ $contatori['nuovi'] }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Da contattare</div>
                    <div class="fw-semibold mt-1">{{ $contatori['da_contattare'] }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Demo</div>
                    <div class="fw-semibold mt-1">{{ $contatori['demo'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Agenda CRM</div>
                    <div class="fw-semibold mt-1">{{ $contatori['agenda'] }}</div>
                    <div class="small text-muted mt-2">{{ $contatori['chiusi'] }} contatti già chiusi</div>
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
                    <button class="nav-link {{ $activeTab === 'contatti' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-pane-contatti" type="button" role="tab" aria-selected="{{ $activeTab === 'contatti' ? 'true' : 'false' }}">Contatti e richieste</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'agenda' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#crm-pane-agenda" type="button" role="tab" aria-selected="{{ $activeTab === 'agenda' ? 'true' : 'false' }}">Agenda CRM</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'contatti' ? 'show active' : '' }}" id="crm-pane-contatti" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Nuovo contatto CRM</h5>
                        <p class="text-muted mb-0">Crea una scheda CRM manuale quando il contatto arriva da telefono, fiera, passaparola o inserimento diretto amministrativo.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route($routePrefix . '.store') }}" class="row g-3">
                            @csrf
                            <div class="col-lg-4">
                                <label class="form-label">Struttura</label>
                                <input type="text" name="struttura" class="form-control" value="{{ old('struttura') }}" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Nome e cognome</label>
                                <input type="text" name="nome_cognome" class="form-control" value="{{ old('nome_cognome') }}" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Persona di contatto</label>
                                <input type="text" name="persona_contatto" class="form-control" value="{{ old('persona_contatto') }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Località</label>
                                <x-ui.select name="localita" placeholder="Seleziona la località" allowClear="true">
                                    <option value=""></option>
                                    @foreach($localitaOptions as $localitaOption)
                                        <option value="{{ $localitaOption }}" @selected(old('localita') === $localitaOption)>{{ $localitaOption }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Cellulare</label>
                                <input type="text" name="cellulare" class="form-control" value="{{ old('cellulare') }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Sito internet</label>
                                <input type="text" name="sito_web" class="form-control" value="{{ old('sito_web') }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Motivo del contatto</label>
                                <x-ui.select name="modalita_contatto" placeholder="Seleziona il motivo" allowClear="true">
                                    <option value=""></option>
                                    @foreach($modalitaOptions as $modalitaKey => $modalitaLabel)
                                        <option value="{{ $modalitaKey }}" @selected(old('modalita_contatto') === $modalitaKey)>{{ $modalitaLabel }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            @if($isSuperAdmin)
                                <div class="col-lg-4">
                                    <label class="form-label">Admin assegnato</label>
                                    <x-ui.select name="assigned_admin_id" placeholder="Non assegnato" allowClear="true">
                                        <option value=""></option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" @selected((string) old('assigned_admin_id') === (string) $admin->id)>{{ $admin->displayLabel() }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            @endif
                            <div class="col-12">
                                <label class="form-label">Messaggio / appunto iniziale</label>
                                <textarea name="messaggio" rows="4" class="form-control" placeholder="Descrivi il motivo del contatto o ciò che ti ha chiesto il cliente.">{{ old('messaggio') }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary">Crea scheda CRM</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Ricerca rapida CRM</h5>
                        <p class="text-muted mb-0">Cerca struttura, referente, email, località o codice CRM e filtra i contatti commerciali nel tuo perimetro.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-3 align-items-end">
                            <input type="hidden" name="tab" value="contatti">
                            <div class="col-lg-4">
                                <label class="form-label">Ricerca rapida</label>
                                <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="CRM-000001, struttura, referente, email...">
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label">Stato</label>
                                <x-ui.select name="stato">
                                    <option value="">Tutti gli stati</option>
                                    @foreach($stati as $key => $label)
                                        <option value="{{ $key }}" @selected($filters['stato'] === $key)>{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            @if($isSuperAdmin)
                                <div class="col-lg-3 col-md-4">
                                    <label class="form-label">Admin assegnato</label>
                                    <x-ui.select name="admin_id">
                                        <option value="">Tutti</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" @selected($filters['admin_id'] === (int) $admin->id)>{{ $admin->displayLabel() }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            @endif
                            <div class="col-lg-auto d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Aggiorna</button>
                                <a href="{{ route($routePrefix . '.index', ['tab' => 'contatti']) }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Elenco contatti</h5>
                        <p class="text-muted mb-0">Ogni riga apre una scheda completa con dati, richiesta iniziale, comunicazioni, ticket, note e agenda di follow-up.</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Contatto</th>
                                        <th>Richiesta</th>
                                        <th>Comunicazione</th>
                                        <th>Stato</th>
                                        <th>Agenda</th>
                                        <th class="text-end">Apri</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $lead->struttura }}</div>
                                                <div class="small text-muted">{{ $lead->nome_cognome }}</div>
                                                @if($lead->persona_contatto)
                                                    <div class="small text-muted">{{ $lead->persona_contatto }}</div>
                                                @endif
                                                @if($lead->localita)
                                                    <div class="small text-muted">{{ $lead->localita }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $lead->modalita_contatto ?: 'Richiesta generale' }}</div>
                                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($lead->messaggio, 120) }}</div>
                                            </td>
                                            <td>
                                                <div class="small">{{ $lead->email }}</div>
                                                @if($lead->telefono || $lead->cellulare)
                                                    <div class="text-muted small">
                                                        @if($lead->telefono && $lead->cellulare)
                                                            {{ $lead->telefono }} · {{ $lead->cellulare }}
                                                        @else
                                                            {{ $lead->telefono ?: $lead->cellulare }}
                                                        @endif
                                                    </div>
                                                @endif
                                                <div class="text-muted small mt-1">
                                                    {{ $lead->assignedAdmin?->displayLabel() ?: 'Non assegnato' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $lead->statoBadgeClass() }}">{{ $lead->statoLabel() }}</span>
                                            </td>
                                            <td>
                                                @if($lead->prossimo_contatto_at)
                                                    <div class="fw-semibold small">{{ $lead->prossimo_contatto_at->format('d/m/Y H:i') }}</div>
                                                @else
                                                    <div class="text-muted small">Nessun follow-up</div>
                                                @endif
                                                @if($lead->preferenza_contatto_label)
                                                    <div class="text-muted small">{{ $lead->preferenza_contatto_label }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route($routePrefix . '.show', $lead->id) }}" class="btn btn-sm btn-primary">Scheda CRM</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">Nessun contatto CRM trovato con i filtri correnti.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($leads->hasPages())
                        <div class="card-footer bg-white border-0">
                            {{ $leads->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'agenda' ? 'show active' : '' }}" id="crm-pane-agenda" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Calendario CRM</h5>
                        <p class="text-muted mb-0">Scegli il giorno e il mese con il calendario del sistema, poi lavora sull agenda senza uscire da questa schermata.</p>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-3 align-items-end">
                            <input type="hidden" name="tab" value="agenda">
                            <input type="hidden" name="q" value="{{ $filters['q'] }}">
                            <input type="hidden" name="stato" value="{{ $filters['stato'] }}">
                            @if($isSuperAdmin)
                                <input type="hidden" name="admin_id" value="{{ $filters['admin_id'] }}">
                            @endif
                            <div class="col-lg-4">
                                <label class="form-label">Giorno agenda</label>
                                <x-calendario name="giorno" variant="single" :value="$selectedDay->toDateString()" />
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Panoramica mese</label>
                                <x-calendario name="mese_ref" variant="single" :value="$selectedMonth->toDateString()" />
                            </div>
                            <div class="col-lg-4 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary">Vedi agenda</button>
                                <a href="{{ route($routePrefix . '.index', ['tab' => 'agenda', 'giorno' => now()->format('Y-m-d'), 'mese_ref' => now()->startOfMonth()->format('Y-m-d')]) }}" class="btn btn-light">Oggi</a>
                            </div>
                        </form>
                        <div class="rounded-3 border p-3 mt-3">
                            <div class="text-muted small">Data selezionata</div>
                            <div class="fw-semibold mt-1">{{ $selectedDay->format('d/m/Y') }}</div>
                            <div class="small text-muted mt-2">{{ $agendaByDay->count() }} appuntamenti nel giorno · {{ $agendaByMonth->flatten()->count() }} nel mese</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Agenda del giorno</h5>
                        <p class="text-muted mb-0">Qui vedi gli appuntamenti del giorno scelto, con struttura, referente e accesso diretto alla scheda CRM.</p>
                    </div>
                    <div class="card-body">
                        @forelse($agendaByDay as $activity)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $activity->titolo }}</div>
                                        <div class="small text-muted">{{ $activity->lead?->struttura }} · {{ $activity->lead?->nome_cognome }}</div>
                                        <div class="small text-muted mt-1">
                                            {{ $activity->scheduled_at?->format('H:i') ?: 'Senza orario' }}
                                            @if($activity->user)
                                                · {{ $activity->user->displayLabel() }}
                                            @endif
                                        </div>
                                        @if($activity->descrizione)
                                            <div class="small mt-2">{{ $activity->descrizione }}</div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info-subtle text-info">{{ $activity->tipoLabel() }}</span>
                                        <div class="mt-2">
                                            <a href="{{ route($routePrefix . '.show', ['id' => $activity->lead->id, 'tab' => 'agenda']) }}" class="btn btn-sm btn-soft-primary">Apri scheda</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted mb-3">Nessun appuntamento CRM nel giorno selezionato.</div>
                        @endforelse

                        <div class="border-top pt-4 mt-4">
                            <h6 class="mb-1">Nuova nota o riunione del giorno selezionato</h6>
                            <p class="text-muted small mb-3">Aggiungi rapidamente un appuntamento o una nota CRM direttamente nella data che stai consultando.</p>
                            <form method="POST" action="{{ route($routePrefix . '.agenda.store') }}" class="row g-3">
                                @csrf
                                <div class="col-lg-4">
                                    <label class="form-label">Contatto CRM</label>
                                    <x-ui.select name="lead_id" placeholder="Seleziona una scheda CRM" required>
                                        <option value=""></option>
                                        @foreach($leadOptions as $leadOption)
                                            <option value="{{ $leadOption->id }}">{{ $leadOption->struttura }} · {{ $leadOption->nome_cognome }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Tipo agenda</label>
                                    <x-ui.select name="tipo" required>
                                        <option value="riunione">Riunione</option>
                                        <option value="demo">Demo</option>
                                        <option value="telefono">Telefonata</option>
                                        <option value="whatsapp">WhatsApp</option>
                                        <option value="email">Email</option>
                                        <option value="ticket">Ticket</option>
                                        <option value="nota">Nota operativa</option>
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Giorno agenda</label>
                                    <x-calendario name="scheduled_data" variant="single" :value="$selectedDay->toDateString()" />
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Ora</label>
                                    <input type="time" name="scheduled_ora" class="form-control" value="09:00">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Titolo</label>
                                    <input type="text" name="titolo" class="form-control" placeholder="Esempio: Demo conoscitiva con la struttura" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nota agenda</label>
                                    <textarea name="descrizione" rows="3" class="form-control" placeholder="Scrivi qui l appunto operativo, la riunione o il follow-up da eseguire."></textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Salva in agenda CRM</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-0">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Panoramica mensile agenda</h5>
                        <p class="text-muted mb-0">Calendario mensile completo del CRM, con tutti gli appuntamenti distribuiti nei giorni del mese.</p>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            @foreach($giorniSettimanaCrm as $giorno)
                                <div class="col">
                                    <div class="small text-uppercase text-muted fw-semibold px-2 py-1">{{ $giorno }}</div>
                                </div>
                            @endforeach
                        </div>
                        @foreach($calendarWeeksCrm as $week)
                            <div class="row g-2 mb-2">
                                @foreach($week as $day)
                                    @php
                                        $dateKey = $day->format('Y-m-d');
                                        $dayItems = $agendaByMonth->get($dateKey, collect());
                                        $isCurrentMonth = $day->month === $selectedMonth->month;
                                        $isToday = $day->isSameDay(now());
                                    @endphp
                                    <div class="col">
                                        <div class="card border shadow-sm mb-0 {{ $isCurrentMonth ? '' : 'bg-light-subtle opacity-75' }}" style="min-height: 140px;">
                                            <div class="card-body p-2 d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <a href="{{ route($routePrefix . '.index', ['tab' => 'agenda', 'giorno' => $day->format('Y-m-d'), 'mese_ref' => $selectedMonth->format('Y-m-d')]) }}" class="fw-semibold text-decoration-none {{ $isToday ? 'text-primary' : 'text-body' }}">{{ $day->format('j') }}</a>
                                                    @if($dayItems->count())
                                                        <span class="badge bg-light text-body">{{ $dayItems->count() }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column gap-1 overflow-hidden">
                                                    @foreach($dayItems->take(3) as $activity)
                                                        <a href="{{ route($routePrefix . '.show', ['id' => $activity->lead->id, 'tab' => 'agenda']) }}" class="rounded-2 px-2 py-1 small text-decoration-none bg-info-subtle text-info text-truncate">
                                                            {{ $activity->scheduled_at?->format('H:i') ?: '--:--' }} · {{ $activity->lead?->struttura }}
                                                        </a>
                                                    @endforeach
                                                    @if($dayItems->count() > 3)
                                                        <div class="small text-muted px-1">+{{ $dayItems->count() - 3 }} altri</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if($agendaByMonth->flatten()->count() === 0)
                            <div class="text-muted mt-3">Nessun appuntamento CRM nel mese selezionato.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
