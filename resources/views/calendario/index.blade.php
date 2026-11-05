@extends('layouts.master')
@section('title', 'Calendario')
@section('css')
<style>
#calendarWorkspace .card,#calendarWorkspace .card-header,#calendarWorkspace .card-footer,#calendarWorkspace .modal-content{background-color:var(--vz-secondary-bg);color:var(--vz-body-color)}#calendarWorkspace .form-control,#calendarWorkspace .form-select{background-color:var(--vz-secondary-bg);color:var(--vz-body-color)}.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:1px;background:var(--vz-border-color);border:1px solid var(--vz-border-color);border-radius:8px;overflow:hidden}.calendar-weekday{background:var(--vz-light);padding:10px;text-align:center}.calendar-day{border:0;background:var(--vz-secondary-bg);color:var(--vz-body-color);min-height:126px;padding:10px;text-align:left;display:flex;flex-direction:column;gap:6px;min-width:0}.calendar-day:hover,.calendar-day:focus-visible{box-shadow:inset 0 0 0 2px var(--vz-primary)}.calendar-day[aria-current=date]{box-shadow:inset 0 0 0 2px var(--vz-primary)}.calendar-day.is-outside{background:var(--vz-tertiary-bg)}.calendar-day .calendar-summary{font-size:12px;white-space:normal;overflow-wrap:anywhere}.calendar-entry summary{list-style:none;cursor:pointer}.calendar-entry summary::-webkit-details-marker{display:none}.calendar-entry summary::after{content:'＋';margin-left:auto;flex-shrink:0}.calendar-entry[open] summary::after{content:'−'}.calendar-entry summary:focus-visible{outline:2px solid var(--vz-primary);outline-offset:4px}.calendar-entry{border-bottom:1px solid var(--vz-border-color);padding:14px 0}.calendar-entry:last-child{border-bottom:0}.calendar-description{white-space:pre-wrap;overflow-wrap:anywhere}.calendar-view-buttons{flex-wrap:wrap}.calendar-day-number{font-weight:600}.calendar-entry-title{overflow-wrap:anywhere;min-width:0}@media(max-width:575.98px){.calendar-day{min-height:76px;padding:5px;gap:4px}.calendar-day .calendar-summary{font-size:11px}.calendar-summary-label{display:none}.calendar-day .calendar-summary{display:flex;gap:3px;align-items:center}.calendar-weekday{padding:7px 0;font-size:12px}.calendar-view-buttons{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));width:100%}.calendar-view-buttons .btn{padding:9px 5px;font-size:12px}.calendar-month-shell{padding:8px!important}}
@media(max-width:991.98px){.calendar-summary-label{display:none}}
#calendarWorkspace .calendar-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}.calendar-heading{min-width:0}.calendar-heading h4{font-size:20px}.calendar-heading span{display:block;overflow-wrap:anywhere}.calendar-filters>summary{cursor:pointer;font-weight:500}.calendar-filters[open]>summary{margin-bottom:16px}.calendar-section-nav{border-bottom:1px solid var(--vz-border-color)}.calendar-section-nav a{padding:12px 16px;border-bottom:2px solid transparent;color:var(--vz-body-color)}.calendar-section-nav a[aria-current=page]{border-color:var(--vz-primary);color:var(--vz-primary)}
@media(max-width:575.98px){.calendar-heading h4{font-size:18px}.calendar-entry summary{flex-wrap:wrap}.calendar-entry-title{flex-basis:60%}.calendar-section-nav a{padding:10px}}
</style>
@endsection
@section('content')
@php
    $sezione = request('sezione', 'calendario') === 'storico' ? 'storico' : 'calendario';
    $base = ['contesto' => $contesto, 'struttura_id' => $selectedStructureId, 'mese' => $month->format('Y-m'), 'giorno' => $selectedDay->toDateString(), 'vista' => $vista, 'categoria' => $categoria, 'q' => $q, 'sezione' => $sezione, 'stato_storico' => $statoStorico];
    $url = fn (array $params = []) => route('calendario.index', array_merge($base, $params));
    $categories = ['informazioni' => ['Informazioni', 'ri-information-line', 'info'], 'attivita' => ['Da fare', 'ri-checkbox-circle-line', 'success'], 'eventi' => ['Eventi', 'ri-calendar-event-line', 'warning']];
    $navDate = $vista === 'month' || $vista === 'agenda' ? $month : $selectedDay;
    $previous = match ($vista) { 'day' => $navDate->copy()->subDay(), 'week' => $navDate->copy()->subWeek(), default => $navDate->copy()->subMonthNoOverflow() };
    $next = match ($vista) { 'day' => $navDate->copy()->addDay(), 'week' => $navDate->copy()->addWeek(), default => $navDate->copy()->addMonthNoOverflow() };
    $title = match ($contesto) { 'personale' => 'Calendario personale', 'portfolio' => $portfolioLabel, default => $struttura?->nome_struttura ?: 'Calendario struttura' };
    $periodLabel = match ($vista) { 'day' => $selectedDay->format('d/m/Y'), 'week' => $periodStart->format('d/m') . ' – ' . $periodEnd->format('d/m/Y'), default => ucfirst($month->locale('it')->translatedFormat('F Y')) };
@endphp
<div id="calendarWorkspace">
<div class="card mb-3">
    <nav class="d-flex calendar-section-nav" aria-label="Sezioni calendario">
        <a href="{{ $url(['sezione' => 'calendario']) }}" @if($sezione === 'calendario') aria-current="page" @endif>Calendario</a>
        <a href="{{ $url(['sezione' => 'storico']) }}" @if($sezione === 'storico') aria-current="page" @endif>Storico annotazioni</a>
    </nav>
    <div class="card-body">
        <div class="calendar-toolbar">
            <div class="calendar-heading"><h4 class="mb-1">{{ $sezione === 'storico' ? 'Storico annotazioni' : $periodLabel }}</h4><span class="text-muted small">{{ $title }}</span></div>
            <button class="btn btn-primary js-open-new-event" type="button" data-date="{{ $selectedDay->toDateString() }}"><i class="ri-add-line me-1" aria-hidden="true"></i>Aggiungi annotazione</button>
        </div>
        @if($sezione === 'calendario')
        <div class="calendar-toolbar mt-3">
            <nav class="d-flex gap-1 calendar-view-buttons" aria-label="Vista calendario">
                @foreach(['month' => 'Mese', 'week' => 'Settimana', 'day' => 'Giorno'] as $key => $label)
                    <a class="btn {{ $vista === $key ? 'btn-primary' : 'btn-light' }}" href="{{ $url(['vista' => $key]) }}" @if($vista === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="d-flex align-items-center gap-1">
                <a class="btn btn-light" href="{{ $url(['mese' => $previous->format('Y-m'), 'giorno' => $previous->toDateString()]) }}" aria-label="Periodo precedente"><i class="ri-arrow-left-s-line" aria-hidden="true"></i></a>
                <a class="btn btn-light" href="{{ $url(['mese' => now()->format('Y-m'), 'giorno' => now()->toDateString()]) }}">Oggi</a>
                <a class="btn btn-light" href="{{ $url(['mese' => $next->format('Y-m'), 'giorno' => $next->toDateString()]) }}" aria-label="Periodo successivo"><i class="ri-arrow-right-s-line" aria-hidden="true"></i></a>
            </div>
        </div>
        @endif
        <div class="d-flex align-items-center gap-2 flex-wrap mt-3" aria-label="Categorie calendario">
            <a class="btn btn-sm {{ $categoria === 'tutte' ? 'btn-dark' : 'btn-light' }}" href="{{ $url(['categoria' => 'tutte']) }}">Tutto</a>
            @foreach($categories as $key => [$label, $icon, $color])
                <a class="btn btn-sm {{ $categoria === $key ? 'btn-'.$color : 'btn-soft-'.$color }}" href="{{ $url(['categoria' => $key]) }}" @if($categoria === $key) aria-current="true" @endif><i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $label }}</a>
            @endforeach
        </div>
        <details class="calendar-filters mt-3 pt-3 border-top" @if($q !== '' || $errors->any() || $sezione === 'storico' || $contesto === 'personale') open @endif>
            <summary>Filtri e ricerca @if($q !== '' || $statoStorico !== '')<span class="badge bg-primary">Filtri attivi</span>@endif</summary>
            <form method="GET" action="{{ route('calendario.index') }}" class="row g-3 align-items-end">
                @foreach(['contesto', 'vista', 'categoria', 'sezione'] as $key)<input type="hidden" name="{{ $key }}" value="{{ $base[$key] }}">@endforeach
                <div class="col-md-5"><label for="calendar-search" class="form-label">Cerca {{ $sezione === 'storico' ? 'nelle annotazioni' : 'nel calendario' }}</label><input id="calendar-search" type="search" name="q" maxlength="180" class="form-control" value="{{ $q }}" placeholder="Titolo, cliente o descrizione"></div>
                <div class="col-md-4"><label for="calendar-date" class="form-label">{{ $sezione === 'storico' ? 'Giorno di riferimento' : 'Vai al giorno' }}</label><x-ui.datepicker id="calendar-date" name="giorno" :value="$selectedDay->toDateString()" format="Y-m-d" :alt-input="true" alt-format="d/m/Y" min-date="1900-01-01" max-date="2100-12-31" /></div>
                @if($canSelectStructure && $contesto !== 'personale')
                    <div class="col-md-6"><label for="calendar-structure" class="form-label">Strutture gestite</label><x-ui.select id="calendar-structure" name="struttura_id" placeholder="Tutte le strutture">@if($contesto === 'portfolio')<option value="">Tutte le strutture</option>@endif @foreach($struttureDisponibili as $item)<option value="{{ $item->id }}" @selected((string)$selectedStructureId === (string)$item->id)>{{ $item->nome_struttura }}</option>@endforeach</x-ui.select></div>
                @else
                    <input type="hidden" name="struttura_id" value="{{ $selectedStructureId }}">
                @endif
                @if($sezione === 'storico')
                    <div class="col-md-6"><label for="calendar-history-state" class="form-label">Stato annotazione</label><x-ui.select id="calendar-history-state" name="stato_storico" placeholder="Tutti gli stati"><option value="">Tutti gli stati</option>@foreach(['da_fare' => 'Da fare', 'vista' => 'Vista', 'completata' => 'Completata', 'chiusa' => 'Chiusa'] as $key => $label)<option value="{{ $key }}" @selected($statoStorico === $key)>{{ $label }}</option>@endforeach</x-ui.select></div>
                @endif
                <div class="col-auto"><button class="btn btn-primary" type="submit">Applica</button></div>
            </form>
            <nav class="d-flex flex-wrap align-items-center gap-2 mt-3" aria-label="Visibilità annotazioni">
                @if($struttureDisponibili->isNotEmpty())
                    <a class="btn btn-sm {{ $contesto !== 'personale' ? 'btn-soft-primary' : 'btn-light' }}" href="{{ $url(['contesto' => $canSelectStructure ? 'portfolio' : 'struttura', 'struttura_id' => $canSelectStructure ? null : $struttureDisponibili->first()->id]) }}">{{ $canSelectStructure ? 'Calendario strutture gestite' : 'Calendario della struttura' }}</a>
                @endif
                <a class="btn btn-sm {{ $contesto === 'personale' ? 'btn-soft-primary' : 'btn-light' }}" href="{{ $url(['contesto' => 'personale']) }}">Note personali riservate</a>
            </nav>
        </details>
    </div>
</div>
@if($sezione === 'calendario')
<div class="d-flex flex-wrap gap-2 mb-3 small" aria-label="Legenda avvisi"><span class="badge bg-danger">Rosso · Urgente o scaduto</span><span class="badge bg-warning text-dark">Giallo · Attenzione o scade oggi</span><span class="badge bg-success">Verde · Ordinario o completato</span></div>
@endif
@if($sezione === 'storico')
    <div class="card"><div class="card-body"><h5>Storico annotazioni</h5><p class="text-muted">Ultime 80 annotazioni concluse o precedenti al giorno di riferimento, dalla più recente. Sono esclusi gli avvisi automatici.</p>
        @forelse($historyEvents->groupBy('date_key') as $date => $entries)
            <section class="mb-3"><h6 class="border-bottom pb-2">{{ ucfirst(\Carbon\Carbon::parse($date)->locale('it')->translatedFormat('l d F Y')) }}</h6>@include('calendario.partials.entries', ['entries' => $entries, 'showDate' => false, 'showState' => true])</section>
        @empty
            <p class="text-muted text-center py-4 mb-0">Nessuna annotazione nello storico con i filtri selezionati.</p>
        @endforelse
    </div></div>
@elseif($vista === 'month')
    <div class="card"><div class="card-body calendar-month-shell">
        <div class="calendar-grid">
            @foreach(['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'] as $label)<div class="calendar-weekday">{{ $label }}</div>@endforeach
            @foreach($calendarWeeks->flatten() as $day)
                @php $dayEntries = $eventsByDay->get($day->toDateString(), collect()); @endphp
                <button type="button" class="calendar-day js-calendar-day {{ $day->month !== $month->month ? 'is-outside' : '' }}" data-date="{{ $day->toDateString() }}" data-label="{{ $day->format('d/m/Y') }}" aria-label="{{ $day->format('d/m/Y') }}, {{ $dayEntries->count() }} voci" @if($day->isToday()) aria-current="date" @endif>
                    <span class="calendar-day-number">{{ $day->format('j') }} @if($day->isToday())<span class="visually-hidden">oggi</span>@endif</span>
                    @foreach(['danger' => 'Urgente / scaduto', 'warning' => 'Attenzione', 'success' => 'Ordinario'] as $alertColor => $alertLabel)
                        @php $alertCount = $dayEntries->where('alert_color', $alertColor)->count(); @endphp
                        @if($alertCount)<span class="calendar-summary text-{{ $alertColor }}" data-calendar-alert="{{ $alertColor }}"><i class="ri-alarm-warning-line" aria-hidden="true"></i> {{ $alertCount }} <span class="calendar-summary-label">{{ $alertLabel }}</span><span class="visually-hidden d-sm-none">{{ $alertLabel }}</span></span>@endif
                    @endforeach
                    @foreach(['compleanno' => ['Compleanni', 'ri-cake-2-line', 'warning'], 'checkin' => ['Check-in', 'ri-login-box-line', 'success'], 'checkout' => ['Check-out', 'ri-logout-box-line', 'info'], 'scadenze' => ['Scadenze automatiche', 'ri-alarm-warning-line', 'danger']] as $type => [$label, $icon, $color])
                        @php $count = $type === 'scadenze' ? $dayEntries->whereIn('tipo', ['scadenza_servizio', 'scadenza_licenza'])->count() : $dayEntries->where('tipo', $type)->count(); @endphp
                        @if($count)<span class="calendar-summary text-{{ $color }}" data-calendar-automatic="{{ $type }}"><i class="{{ $icon }}" aria-hidden="true"></i> {{ $count }} <span class="calendar-summary-label">{{ $label }}</span><span class="visually-hidden d-sm-none">{{ $label }}</span></span>@endif
                    @endforeach
                    @foreach($categories as $key => [$label, $icon, $color])
                        @php $count = $dayEntries->where('is_manual', true)->where('categoria', $key)->count(); @endphp
                        @if($count)<span class="calendar-summary text-{{ $color }}"><i class="{{ $icon }}" aria-hidden="true"></i> {{ $count }} <span class="calendar-summary-label">{{ $label }}</span><span class="visually-hidden d-sm-none">{{ $label }}</span></span>@endif
                    @endforeach
                </button>
                <template id="calendar-day-{{ $day->toDateString() }}">@include('calendario.partials.entries', ['entries' => $dayEntries])</template>
            @endforeach
        </div>

    </div></div>
@else
    @php
        $days = $vista === 'day' ? collect([$selectedDay]) : ($vista === 'week' ? $calendarWeeks->flatten() : $periodEvents->groupBy('date_key')->keys()->map(fn ($date) => \Carbon\Carbon::parse($date)));
    @endphp
    @forelse($days as $day)
        <section class="card"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2"><h5 class="mb-0">{{ ucfirst($day->locale('it')->translatedFormat('l d F')) }}</h5><button class="btn btn-sm btn-soft-primary js-open-new-event" type="button" data-date="{{ $day->toDateString() }}">Aggiungi</button></div><div class="card-body py-1">@include('calendario.partials.entries', ['entries' => $eventsByDay->get($day->toDateString(), collect())])</div></section>
    @empty
        <div class="card"><div class="card-body text-center text-muted py-5">Nessuna voce nel periodo con questi filtri.</div></div>
    @endforelse
@endif
<div class="modal fade" id="calendarDayModal" tabindex="-1" aria-labelledby="calendarDayTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="calendarDayTitle">Dettaglio giorno</h5><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Chiudi"></button></div><div class="modal-body" id="calendarDayBody"></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button><button type="button" class="btn btn-primary js-open-new-event" id="calendarDayAdd">Aggiungi a questo giorno</button></div></div></div></div>
@include('calendario.partials.editor', ['evento' => null])
@foreach($manualMonthEvents->concat($history)->unique('id') as $evento)
    @include('calendario.partials.editor', ['evento' => $evento])
@endforeach
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Flatpickr mantiene il valore ISO nel campo originale e presenta la data italiana.
    window.addEventListener('load', () => document.querySelectorAll('#calendarWorkspace input[data-ui="datepicker"]').forEach(input => {
        const display = input._flatpickr?.altInput;
        if (!display) return;
        display.id = input.id + 'Display';
        document.querySelector('label[for="' + input.id + '"]')?.setAttribute('for', display.id);
    }));
    const dayEl = document.getElementById('calendarDayModal');
    const newEl = document.getElementById('modalNuovoEvento');
    const dayModal = bootstrap.Modal.getOrCreateInstance(dayEl);
    const newModal = bootstrap.Modal.getOrCreateInstance(newEl);
    let dayState = 'hidden';
    dayEl.addEventListener('show.bs.modal', () => { dayState = 'showing'; });
    dayEl.addEventListener('shown.bs.modal', () => { dayState = 'shown'; });
    dayEl.addEventListener('hide.bs.modal', () => { dayState = 'hiding'; });
    dayEl.addEventListener('hidden.bs.modal', () => { dayState = 'hidden'; });
    function closeDayThen(showEditor) {
        if (dayState === 'hidden') { showEditor(); return; }
        dayEl.addEventListener('hidden.bs.modal', showEditor, {once: true});
        if (dayState === 'showing') {
            dayEl.addEventListener('shown.bs.modal', () => dayModal.hide(), {once: true});
        } else if (dayState === 'shown') dayModal.hide();
    }
    function openNew(date) {
        const input = document.getElementById('modalNuovoEventoData');
        if (input._flatpickr) input._flatpickr.setDate(date, true); else input.value = date;
        closeDayThen(() => newModal.show());
    }
    document.querySelectorAll('.js-calendar-day').forEach(button => button.addEventListener('click', () => {
        const template = document.getElementById('calendar-day-' + button.dataset.date);
        document.getElementById('calendarDayTitle').textContent = 'Giorno ' + button.dataset.label;
        document.getElementById('calendarDayBody').replaceChildren(template.content.cloneNode(true));
        document.getElementById('calendarDayAdd').dataset.date = button.dataset.date;
        dayModal.show();
    }));
    const savedDay = @json(session('calendar_day'));
    if (savedDay) document.querySelector('.js-calendar-day[data-date="' + savedDay + '"]')?.click();
    document.querySelectorAll('.js-open-new-event').forEach(button => button.addEventListener('click', () => openNew(button.dataset.date)));
    newEl.addEventListener('shown.bs.modal', () => newEl.querySelector('[name="titolo"]').focus());
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-calendar-edit]');
        if (!button) return;
        const target = document.getElementById(button.dataset.calendarEdit);
        const show = () => bootstrap.Modal.getOrCreateInstance(target).show();
        closeDayThen(show);
    });
    @if($errors->hasAny(['tipo', 'titolo', 'descrizione', 'data_evento', 'ora_evento', 'priorita', 'stato']))
        const failed = document.getElementById(@json(old('_editor', 'modalNuovoEvento')));
        if (failed) bootstrap.Modal.getOrCreateInstance(failed).show();
    @endif
});
</script>
@endpush
