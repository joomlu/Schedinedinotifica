@extends('layouts.master')

@section('title', 'Calendario')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Calendario @endslot
    @slot('title') {{ $contesto === 'personale' ? 'Calendario personale' : 'Calendario struttura' }} @endslot
@endcomponent

@php
    use Illuminate\Support\Str;
    $giorniSettimana = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    $mesiItaliani = [
        1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile', 5 => 'maggio', 6 => 'giugno',
        7 => 'luglio', 8 => 'agosto', 9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre',
    ];
    $baseRouteParams = ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista];
    $sezione = request('sezione', 'calendario');
    $calendarTitle = match ($contesto) {
        'personale' => 'Calendario personale',
        'portfolio' => $portfolioLabel,
        default => 'Calendario struttura',
    };
    $calendarSubtitle = match ($contesto) {
        'personale' => 'Agenda privata del ruolo corrente. Le note qui restano personali e indipendenti dalle strutture.',
        'portfolio' => 'Vista aggregata delle strutture accessibili, con arrivi, partenze, compleanni, scadenze e note manuali.',
        default => 'Agenda comune della struttura corrente, con note manuali e automatismi del sistema.',
    };
@endphp

@php
    $periodoLabel = $vista === 'day'
        ? 'Giorno selezionato: ' . $selectedDay->format('d/m/Y')
        : ucfirst($mesiItaliani[(int) $month->format('n')] ?? $month->locale('it')->translatedFormat('F')) . ' ' . $month->format('Y');
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="card-title mb-1">{{ $calendarTitle }}</h4>
                <div class="text-muted">{{ $calendarSubtitle }}</div>
                @if($contesto === 'struttura' && $struttura)
                    <div class="small text-muted mt-1">Struttura selezionata: <span class="fw-semibold text-body">{{ $struttura->nome_struttura }}</span></div>
                @elseif($contesto === 'portfolio')
                    <div class="small text-muted mt-1">{{ $portfolioLabel }}@if($struttureDisponibili->count()) · {{ $struttureDisponibili->count() }} strutture @endif</div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoEvento">
                    <i class="ri-add-line align-bottom me-1"></i> Nuova nota
                </button>
            </div>
        </div>
    </div>
    <div class="card-body py-3">
        <div class="rounded-3 border bg-light-subtle px-3 py-3 mb-3">
            <form method="GET" action="{{ route('calendario.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="contesto" value="{{ $contesto }}">
                <input type="hidden" name="sezione" value="{{ $sezione }}">
                <div class="col-12">
                    <label class="form-label mb-1">Filtro rapido</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ri-search-line"></i></span>
                        <input type="text" name="q" id="calendarioToolbarSearch" class="form-control" value="{{ $q ?? '' }}" placeholder="Cerca nota, compleanno, check-in, check-out, cliente o struttura...">
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <label class="form-label mb-1">Data di riferimento</label>
                    <x-calendario name="giorno" variant="single" :value="$selectedDay->toDateString()" id="calendarioToolbarDay" />
                </div>
                <div class="col-xl-2 col-md-6">
                    <label class="form-label mb-1">Vista</label>
                    <x-ui.select name="vista" id="calendarioToolbarVista">
                        <option value="month" @selected($vista === 'month')>Mese</option>
                        <option value="day" @selected($vista === 'day')>Giorno</option>
                    </x-ui.select>
                </div>
                @if($canSelectStructure)
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label mb-1">Struttura</label>
                        <x-ui.select name="struttura_id" id="calendarioToolbarStruttura">
                            @if($contesto === 'portfolio')
                                <option value="">Tutte</option>
                            @endif
                            @foreach($struttureDisponibili as $item)
                                <option value="{{ $item->id }}" @selected((string) $selectedStructureId === (string) $item->id)>{{ $item->nome_struttura }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                @endif
                <div class="col-xl-{{ $canSelectStructure ? '3' : '6' }} col-md-12">
                    <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                        <a href="{{ route('calendario.index', ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => now()->format('Y-m'), 'giorno' => now()->toDateString(), 'vista' => 'month', 'sezione' => $sezione]) }}" class="btn btn-light">Oggi</a>
                        <button type="submit" class="btn btn-primary">Aggiorna</button>
                    </div>
                </div>
            </form>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 pt-3 border-top">
                <div>
                    <div class="text-muted small">Periodo attivo</div>
                    <div class="fw-semibold fs-5">{{ $periodoLabel }}</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('calendario.index', ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => $prevMonth, 'giorno' => $selectedDay->copy()->subMonthNoOverflow()->toDateString(), 'vista' => $vista, 'sezione' => $sezione, 'q' => $q]) }}" class="btn btn-light">
                        <i class="ri-arrow-left-s-line align-bottom me-1"></i> Precedente
                    </a>
                    <a href="{{ route('calendario.index', ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => $nextMonth, 'giorno' => $selectedDay->copy()->addMonthNoOverflow()->toDateString(), 'vista' => $vista, 'sezione' => $sezione, 'q' => $q]) }}" class="btn btn-light">
                        Successivo <i class="ri-arrow-right-s-line align-bottom ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <div class="rounded-3 border bg-white px-3 py-2 h-100">
                    <div class="text-muted small">Note aperte</div>
                    <div class="fs-4 fw-semibold">{{ $contatori['manuali_aperte'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rounded-3 border bg-white px-3 py-2 h-100">
                    <div class="text-muted small">Eventi del giorno</div>
                    <div class="fs-4 fw-semibold">{{ $contatori['oggi'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rounded-3 border bg-white px-3 py-2 h-100">
                    <div class="text-muted small">{{ $contesto === 'personale' ? 'Note del mese' : 'Compleanni del mese' }}</div>
                    <div class="fs-4 fw-semibold">{{ $contatori['compleanni'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rounded-3 border bg-white px-3 py-2 h-100">
                    <div class="text-muted small">{{ $contesto === 'personale' ? 'Eventi automatici' : 'Check-in / Check-out' }}</div>
                    <div class="fs-4 fw-semibold">{{ $contatori['movimenti'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="step-arrow-nav mb-4">
    <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
        <li class="nav-item" role="presentation">
                <a class="nav-link {{ $contesto === 'personale' ? 'active' : '' }}" href="{{ route('calendario.index', ['contesto' => 'personale', 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista, 'sezione' => $sezione, 'q' => $q]) }}">
                    <span class="d-block">Calendario personale</span>
                </a>
            </li>
        @if($struttureDisponibili->isNotEmpty() && !$hasStrutturaContext)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $contesto === 'portfolio' ? 'active' : '' }}" href="{{ route('calendario.index', ['contesto' => 'portfolio', 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista, 'sezione' => $sezione, 'struttura_id' => $selectedStructureId, 'q' => $q]) }}">
                    <span class="d-block">{{ $portfolioLabel }}</span>
                </a>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            @if($hasStrutturaContext)
                <a class="nav-link {{ $contesto === 'struttura' ? 'active' : '' }}" href="{{ route('calendario.index', ['contesto' => 'struttura', 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista, 'sezione' => $sezione, 'struttura_id' => $selectedStructureId, 'q' => $q]) }}">
                    <span class="d-block">{{ $struttura?->nome_struttura ?: 'Calendario struttura' }}</span>
                </a>
            @else
                <button class="nav-link disabled" type="button">
                    <span class="d-block">Calendario struttura</span>
                </button>
            @endif
        </li>
    </ul>
</div>

<div class="step-arrow-nav mb-4">
    <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $sezione === 'calendario' ? 'active' : '' }}" href="{{ route('calendario.index', ['contesto' => $contesto, 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista, 'sezione' => 'calendario']) }}">
                <span class="d-block">Calendario</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $sezione === 'storico' ? 'active' : '' }}" href="{{ route('calendario.index', ['contesto' => $contesto, 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => 'day', 'sezione' => 'storico']) }}">
                <span class="d-block">Storico</span>
            </a>
        </li>
    </ul>
</div>

@if($sezione === 'calendario' && $vista === 'month')
    <div class="card border shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row g-2 mb-2">
                @foreach($giorniSettimana as $giorno)
                    <div class="col">
                        <div class="small text-uppercase text-muted fw-semibold px-2 py-1">{{ $giorno }}</div>
                    </div>
                @endforeach
            </div>

            @foreach($calendarWeeks as $week)
                <div class="row g-2 mb-2">
                    @foreach($week as $day)
                        @php
                            $dateKey = $day->toDateString();
                            $dayEvents = $eventsByDay->get($dateKey, collect());
                            $isCurrentMonth = $day->month === $month->month;
                            $isSelected = $day->isSameDay($selectedDay);
                            $isToday = $day->isSameDay(now());
                        @endphp
                        <div class="col">
                            <div class="card border shadow-sm mb-0 js-calendar-day {{ $isCurrentMonth ? '' : 'bg-light-subtle opacity-75' }} {{ $isSelected ? 'border-primary' : '' }}" data-date="{{ $dateKey }}" style="min-height: 148px; cursor: pointer;">
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold {{ $isToday ? 'text-primary' : 'text-body' }}">{{ $day->format('j') }}</span>
                                        @if($dayEvents->count() > 0)
                                            <span class="badge bg-light text-body">{{ $dayEvents->count() }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column gap-1 overflow-hidden">
                                        @foreach($dayEvents->take(4) as $event)
                                            <div class="rounded-2 px-2 py-1 small text-truncate {{ $event['badge_class'] }} {{ $event['priorita'] === 'urgente' ? 'border border-danger' : '' }}">
                                                @if($contesto === 'portfolio' && !empty($event['struttura_label']))
                                                    <span class="fw-semibold">{{ Str::limit($event['struttura_label'], 12) }}</span>
                                                    <span class="mx-1">·</span>
                                                @endif
                                                @if($event['ora_evento'])
                                                    <span class="fw-semibold">{{ substr($event['ora_evento'], 0, 5) }}</span>
                                                    <span class="mx-1">·</span>
                                                @endif
                                                {{ $event['titolo'] }}
                                            </div>
                                        @endforeach
                                        @if($dayEvents->count() > 4)
                                            <button type="button" class="btn btn-link btn-sm text-start px-1 py-0 js-calendar-day-more" data-date="{{ $dateKey }}">+{{ $dayEvents->count() - 4 }} altri</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($sezione === 'calendario' && $vista === 'day')
    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card border shadow-sm mb-0">
        <div class="card-header border-0 bg-light-subtle d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="card-title mb-1">{{ $contesto === 'personale' ? 'Agenda personale del giorno' : 'Agenda del giorno' }}</h5>
                <p class="text-muted mb-0">{{ $contesto === 'personale' ? 'Tutte le annotazioni personali del giorno scelto, in ordine di orario e priorita.' : 'Tutte le annotazioni e gli automatismi del giorno scelto, in ordine di orario e priorita.' }}</p>
            </div>
                    <button type="button" class="btn btn-primary js-open-new-event" data-date="{{ $selectedDay->toDateString() }}">Nuova nota</button>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @forelse($agendaEvents as $event)
                            <div class="rounded-3 border p-3 {{ $event['priorita'] === 'urgente' ? 'border-danger' : '' }}">
                                <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge {{ $event['badge_class'] }}">{{ $event['tipo_label'] }}</span>
                                            <span class="badge bg-light text-body">{{ $event['stato_label'] }}</span>
                                            @if($event['priorita'] === 'urgente')
                                                <span class="badge bg-danger text-white">Urgente</span>
                                            @endif
                                            @if($event['ora_evento'])
                                                <span class="badge bg-light text-body">{{ substr($event['ora_evento'], 0, 5) }}</span>
                                            @endif
                                        </div>
                                        <div class="fw-semibold fs-15">{{ $event['titolo'] }}</div>
                                        @if(!empty($event['struttura_label']) && $contesto !== 'struttura')
                                            <div class="text-muted small">{{ $event['struttura_label'] }}</div>
                                        @endif
                                        <div class="text-muted small">{{ $event['creator_role'] }}: {{ $event['creator_label'] }}</div>
                                    </div>
                                    @if($event['is_manual'])
                                        <div class="d-flex gap-2 align-items-start">
                                            <button type="button" class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#modalEvento{{ $event['model']->id }}">Apri</button>
                                            @if($event['stato'] !== 'chiusa')
                                                <form method="POST" action="{{ route('calendario.status', $event['model']->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="stato" value="chiusa">
                                                    <button type="submit" class="btn btn-soft-danger">Chiudi</button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif(!empty($event['detail_link']))
                                        <div class="d-flex gap-2 align-items-start">
                                            <a href="{{ $event['detail_link'] }}" class="btn btn-soft-primary">
                                                {{ $event['detail_label'] ?? 'Apri gestione' }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-body" style="white-space: pre-wrap;">{{ $event['descrizione'] ?: 'Nessuna nota aggiuntiva.' }}</div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4">Nessun evento registrato per questo giorno.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-sm mb-3">
                <div class="card-header border-0 bg-light-subtle">
                    <h5 class="card-title mb-1">Riepilogo giorno</h5>
                    <p class="text-muted mb-0">Vista rapida di quello che richiede attenzione nel giorno scelto.</p>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <div class="rounded-3 bg-light-subtle px-3 py-2">
                        <div class="text-muted small">Totale eventi</div>
                        <div class="fw-semibold fs-4">{{ $agendaEvents->count() }}</div>
                    </div>
                    <div class="rounded-3 bg-danger-subtle text-danger px-3 py-2">
                        <div class="small">Urgenti</div>
                        <div class="fw-semibold fs-4">{{ $agendaEvents->where('priorita', 'urgente')->count() }}</div>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning px-3 py-2">
                        <div class="small">Compleanni</div>
                        <div class="fw-semibold fs-4">{{ $agendaEvents->where('tipo', 'compleanno')->count() }}</div>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info px-3 py-2">
                        <div class="small">Movimenti</div>
                        <div class="fw-semibold fs-4">{{ $agendaEvents->whereIn('tipo', ['checkin', 'checkout'])->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm mb-0">
                <div class="card-header border-0 bg-light-subtle">
                    <h5 class="card-title mb-1">{{ $contesto === 'personale' ? 'Contesto attivo' : 'Automatismi attivi' }}</h5>
                    <p class="text-muted mb-0">{{ $contesto === 'personale' ? 'Nel calendario personale non entrano gli automatismi della struttura: qui lavori solo sulle tue note e promemoria.' : 'Eventi inseriti dal sistema senza scrittura manuale.' }}</p>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    @if($contesto === 'personale')
                        <div>
                            <div class="fw-semibold">Agenda privata del ruolo</div>
                            <div class="text-muted small">Queste note restano personali e non vengono mischiate con il calendario operativo della struttura.</div>
                        </div>
                        <div>
                            <div class="fw-semibold">Quando passi alla struttura</div>
                            <div class="text-muted small">Se selezioni una struttura e apri il calendario struttura, rivedi compleanni, check-in e check-out legati a quella struttura.</div>
                        </div>
                    @else
                        <div>
                            <div class="fw-semibold">Compleanni clienti</div>
                            <div class="text-muted small">Annuncio automatico per clienti e componenti nel giorno del compleanno.</div>
                        </div>
                        <div>
                            <div class="fw-semibold">Check-in previsti</div>
                            <div class="text-muted small">Generati dalle schedine con arrivo nel giorno scelto.</div>
                        </div>
                        <div>
                            <div class="fw-semibold">Check-out previsti</div>
                            <div class="text-muted small">Generati dalle schedine con partenza nel giorno scelto.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if($sezione === 'storico')
<div class="row g-3">
    <div class="col-12">
        <div class="card border shadow-sm mb-0">
            <div class="card-header border-0 bg-light-subtle">
                <h5 class="card-title mb-1">Storico calendario</h5>
                <p class="text-muted mb-0">Qui restano consultabili le note chiuse, completate e le giornate gia trascorse.</p>
            </div>
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('calendario.index') }}" class="row g-3 align-items-end">
                    <input type="hidden" name="contesto" value="{{ $contesto }}">
                    <input type="hidden" name="struttura_id" value="{{ $selectedStructureId }}">
                    <input type="hidden" name="sezione" value="storico">
                    <input type="hidden" name="vista" value="day">
                    <div class="col-xl-4">
                        <label class="form-label mb-1">Vai al giorno</label>
                        <x-calendario name="giorno" variant="single" :value="$selectedDay->toDateString()" />
                    </div>
                    <div class="col-xl-3">
                        <label class="form-label mb-1">Stato</label>
                        <x-ui.select name="stato_storico">
                            <option value="">Tutti</option>
                            <option value="completata" @selected($statoStorico === 'completata')>Completata</option>
                            <option value="chiusa" @selected($statoStorico === 'chiusa')>Chiusa</option>
                            <option value="vista" @selected($statoStorico === 'vista')>Vista</option>
                            <option value="da_fare" @selected($statoStorico === 'da_fare')>Da fare</option>
                        </x-ui.select>
                    </div>
                    <div class="col-xl-4">
                        <label class="form-label mb-1">Filtro rapido</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ri-search-line"></i></span>
                            <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Cerca evento, nota o struttura...">
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                            <a href="{{ route('calendario.index', ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => now()->format('Y-m'), 'giorno' => now()->toDateString(), 'vista' => 'day', 'sezione' => 'storico']) }}" class="btn btn-light">Oggi</a>
                            <button type="submit" class="btn btn-primary">Aggiorna</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Evento</th>
                                <th>Creato da</th>
                                <th>Stato</th>
                                <th class="text-end">Apri</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history->take(20) as $evento)
                                <tr>
                                    <td>
                                        <div>{{ optional($evento->data_evento)->format('d/m/Y') }}</div>
                                        <div class="text-muted small">{{ $evento->ora_evento ? substr($evento->ora_evento, 0, 5) : 'Senza orario' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $evento->titolo }}</div>
                                        <div class="text-muted small">{{ Str::limit($evento->descrizione, 100) ?: 'Nessuna nota aggiuntiva.' }}</div>
                                    </td>
                                    <td>{{ $evento->creator?->displayLabel() ?? '-' }}</td>
                                    <td><span class="badge {{ $evento->badgeClass() }}">{{ $evento->statoLabel() }}</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#modalEvento{{ $evento->id }}">Apri</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Nessun evento nello storico.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div id="modalNuovoEvento" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuova nota calendario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST" action="{{ route('calendario.store') }}">
                @csrf
                <input type="hidden" name="contesto" value="{{ $contesto }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <label class="form-label">Titolo *</label>
                            <input type="text" name="titolo" class="form-control" value="{{ old('titolo') }}" required>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Data</label>
                            <x-calendario name="data_evento" variant="single" :value="old('data_evento', $selectedDay->toDateString())" id="modalNuovoEventoData" />
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Ora</label>
                            <input type="time" name="ora_evento" class="form-control" value="{{ old('ora_evento', '09:00') }}">
                        </div>
                        @if($contesto !== 'personale' && $canSelectStructure)
                            <div class="col-lg-3">
                                <label class="form-label">Struttura</label>
                                <x-ui.select name="struttura_id" required>
                                    @foreach($struttureDisponibili as $item)
                                        <option value="{{ $item->id }}" @selected((string) old('struttura_id', $selectedStructureId) === (string) $item->id)>{{ $item->nome_struttura }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        @elseif($contesto === 'struttura' && $struttura)
                            <input type="hidden" name="struttura_id" value="{{ $struttura->id }}">
                        @endif
                        <div class="col-lg-4">
                            <label class="form-label">Priorita *</label>
                            <x-ui.select name="priorita">
                                <option value="bassa">Bassa</option>
                                <option value="normale" selected>Normale</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </x-ui.select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Stato iniziale *</label>
                            <x-ui.select name="stato">
                                <option value="da_fare" selected>Da fare</option>
                                <option value="vista">Vista</option>
                                <option value="completata">Completata</option>
                                <option value="chiusa">Chiusa</option>
                            </x-ui.select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrizione</label>
                            <textarea name="descrizione" class="form-control" rows="6" placeholder="Scrivi qui la comunicazione o la nota operativa del giorno.">{{ old('descrizione') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva nota</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $modalEvents = $manualMonthEvents->concat($history)->unique('id')->values();
@endphp

@foreach($modalEvents as $evento)
    <div id="modalEvento{{ $evento->id }}" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Evento calendario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <form method="POST" action="{{ route('calendario.update', $evento->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="contesto" value="{{ $contesto }}">
                    @if($evento->struttura_id)
                        <input type="hidden" name="struttura_id" value="{{ $evento->struttura_id }}">
                    @endif
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Creato da</div>
                                <div>{{ $evento->creator?->ruoloOperativoLabel() ?? 'Utente' }}: {{ $evento->creator?->displayLabel() ?? '-' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Ultimo aggiornamento</div>
                                <div>{{ $evento->updated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <label class="form-label">Titolo *</label>
                                <input type="text" name="titolo" class="form-control" value="{{ $evento->titolo }}" required>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Data</label>
                                <x-calendario name="data_evento" variant="single" :value="optional($evento->data_evento)->toDateString()" id="evento-data-{{$evento->id}}" />
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Ora</label>
                                <input type="time" name="ora_evento" class="form-control" value="{{ $evento->ora_evento ? substr($evento->ora_evento, 0, 5) : '09:00' }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Priorita *</label>
                                <x-ui.select name="priorita">
                                    <option value="bassa" @selected($evento->priorita === 'bassa')>Bassa</option>
                                    <option value="normale" @selected($evento->priorita === 'normale')>Normale</option>
                                    <option value="alta" @selected($evento->priorita === 'alta')>Alta</option>
                                    <option value="urgente" @selected($evento->priorita === 'urgente')>Urgente</option>
                                </x-ui.select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Stato *</label>
                                <x-ui.select name="stato">
                                    <option value="da_fare" @selected($evento->stato === 'da_fare')>Da fare</option>
                                    <option value="vista" @selected($evento->stato === 'vista')>Vista</option>
                                    <option value="completata" @selected($evento->stato === 'completata')>Completata</option>
                                    <option value="chiusa" @selected($evento->stato === 'chiusa')>Chiusa</option>
                                </x-ui.select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrizione</label>
                                <textarea name="descrizione" class="form-control" rows="6">{{ $evento->descrizione }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            @if($evento->stato !== 'vista')
                                <button type="submit" class="btn btn-info" form="status-evento-vista-{{ $evento->id }}">Segna vista</button>
                            @endif
                            @if($evento->stato !== 'completata')
                                <button type="submit" class="btn btn-success" form="status-evento-completata-{{ $evento->id }}">Segna completata</button>
                            @endif
                            @if($evento->stato !== 'chiusa')
                                <button type="submit" class="btn btn-danger" form="status-evento-chiusa-{{ $evento->id }}">Chiudi</button>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary">Salva modifiche</button>
                        </div>
                    </div>
                </form>
                @if($evento->stato !== 'vista')
                    <form id="status-evento-vista-{{ $evento->id }}" method="POST" action="{{ route('calendario.status', $evento->id) }}" class="d-none">
                        @csrf
                        <input type="hidden" name="contesto" value="{{ $contesto }}">
                        @if($evento->struttura_id)
                            <input type="hidden" name="struttura_id" value="{{ $evento->struttura_id }}">
                        @endif
                        <input type="hidden" name="stato" value="vista">
                    </form>
                @endif
                @if($evento->stato !== 'completata')
                    <form id="status-evento-completata-{{ $evento->id }}" method="POST" action="{{ route('calendario.status', $evento->id) }}" class="d-none">
                        @csrf
                        <input type="hidden" name="contesto" value="{{ $contesto }}">
                        @if($evento->struttura_id)
                            <input type="hidden" name="struttura_id" value="{{ $evento->struttura_id }}">
                        @endif
                        <input type="hidden" name="stato" value="completata">
                    </form>
                @endif
                @if($evento->stato !== 'chiusa')
                    <form id="status-evento-chiusa-{{ $evento->id }}" method="POST" action="{{ route('calendario.status', $evento->id) }}" class="d-none">
                        @csrf
                        <input type="hidden" name="contesto" value="{{ $contesto }}">
                        @if($evento->struttura_id)
                            <input type="hidden" name="struttura_id" value="{{ $evento->struttura_id }}">
                        @endif
                        <input type="hidden" name="stato" value="chiusa">
                    </form>
                @endif
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toolbarDay = document.getElementById('calendarioToolbarDay');
        const toolbarVista = document.getElementById('calendarioToolbarVista');
        const toolbarStruttura = document.getElementById('calendarioToolbarStruttura');
        const toolbarMese = document.getElementById('calendarioToolbarMese');
        const toolbarAnno = document.getElementById('calendarioToolbarAnno');
        const toolbarForm = toolbarDay ? toolbarDay.closest('form') : null;
        const newEventModalEl = document.getElementById('modalNuovoEvento');
        const newEventModal = newEventModalEl ? new bootstrap.Modal(newEventModalEl) : null;
        const dateInput = document.getElementById('modalNuovoEventoData');
        const titleInput = newEventModalEl ? newEventModalEl.querySelector('[name="titolo"]') : null;

        function syncMonthYear() {
            if (!toolbarMese || !toolbarAnno) return;
            const value = String(toolbarMese.value || '');
            const parts = value.split('-');
            if (parts.length === 2) {
                toolbarAnno.value = parts[0];
            }
        }

        function applyMonthYearToDate() {
            if (!toolbarMese || !toolbarAnno || !toolbarDay) return;
            const monthValue = String(toolbarMese.value || '');
            const yearValue = String(toolbarAnno.value || '').padStart(4, '0');
            const parts = monthValue.split('-');
            const monthPart = parts.length === 2 ? parts[1] : String(new Date().getMonth() + 1).padStart(2, '0');
            const currentDate = String(toolbarDay.value || '');
            const dayPart = currentDate ? currentDate.split('-')[2] : '01';
            toolbarDay.value = `${yearValue}-${monthPart}-${dayPart}`;
        }

        if (toolbarDay) {
            toolbarDay.addEventListener('change', function () {
                toolbarForm?.submit();
            });
        }

        if (toolbarVista) {
            toolbarVista.addEventListener('change', function () {
                toolbarForm?.submit();
            });
        }

        if (toolbarStruttura) {
            toolbarStruttura.addEventListener('change', function () {
                toolbarForm?.submit();
            });
        }

        if (toolbarMese) {
            toolbarMese.addEventListener('change', function () {
                syncMonthYear();
                applyMonthYearToDate();
                toolbarForm?.submit();
            });
        }

        if (toolbarAnno) {
            toolbarAnno.addEventListener('change', function () {
                applyMonthYearToDate();
                toolbarForm?.submit();
            });
        }

        document.querySelectorAll('.js-calendar-day').forEach(function (card) {
            let clickTimer = null;

            card.addEventListener('click', function () {
                const date = card.dataset.date;
                if (!date) return;
                clearTimeout(clickTimer);
                clickTimer = window.setTimeout(function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('giorno', date);
                    url.searchParams.set('vista', 'day');
                    window.location.href = url.toString();
                }, 220);
            });

            card.addEventListener('dblclick', function (event) {
                event.preventDefault();
                clearTimeout(clickTimer);
                const date = card.dataset.date;
                if (dateInput) {
                    dateInput.value = date;
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (newEventModal) {
                    newEventModal.show();
                }
                if (titleInput) {
                    window.setTimeout(function () {
                        titleInput.focus();
                    }, 150);
                }
            });
        });

        document.querySelectorAll('.js-calendar-day-more').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.stopPropagation();
                const date = button.dataset.date;
                const url = new URL(window.location.href);
                url.searchParams.set('giorno', date);
                url.searchParams.set('vista', 'day');
                window.location.href = url.toString();
            });
        });

        document.querySelectorAll('.js-open-new-event').forEach(function (button) {
            button.addEventListener('click', function () {
                const date = button.dataset.date;
                if (dateInput) {
                    dateInput.value = date;
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (newEventModal) {
                    newEventModal.show();
                }
            });
        });
    });
</script>
@endpush
