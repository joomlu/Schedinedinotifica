@php $showDate = $showDate ?? false; @endphp
@php $movements = $entries->whereIn('tipo', ['checkin', 'checkout']); @endphp
@if($movements->isNotEmpty())
    <div class="text-muted small py-2 border-bottom">
        @foreach(['checkin' => 'Arrivi', 'checkout' => 'Partenze'] as $kind => $movementLabel)
            @php $items = $movements->where('tipo', $kind); @endphp
            @if($items->isNotEmpty())
                <span class="d-inline-block me-3">{{ $movementLabel }} · Soggiorni: {{ $items->count() }} · Ospiti dichiarati: {{ $items->sum('persone') }} @if($items->contains(fn ($item) => $item['persone'] === null)) (totale parziale)@endif</span>
            @endif
        @endforeach
    </div>
@endif
@forelse($entries as $entry)
    @php [$label, $icon, $color] = $categories[$entry['categoria']]; @endphp
    <details class="calendar-entry border-start border-3 border-{{ $entry['alert_color'] }} ps-2" data-calendar-alert="{{ $entry['alert_color'] }}">
        <summary class="d-flex align-items-center gap-2">
            <i class="{{ $icon }} text-{{ $color }} fs-18" aria-hidden="true"></i>
            <span class="calendar-entry-title flex-grow-1"><span class="d-block fw-medium">{{ $entry['titolo'] }}</span><span class="text-muted small">{{ !$entry['is_manual'] ? $entry['tipo_label'] : ($entry['is_deadline'] ? 'Scadenza' : $label) }}@if($showDate) · {{ \Carbon\Carbon::parse($entry['data_evento'])->format('d/m/Y') }}@endif · {{ $entry['ora_evento'] ? substr($entry['ora_evento'], 0, 5) : 'Tutto il giorno' }}@if($entry['is_manual'] && ($entry['categoria'] === 'attivita' || ($showState ?? false))) · {{ $entry['stato_label'] }}@endif @if($contesto === 'portfolio' && !empty($entry['struttura_label'])) · {{ $entry['struttura_label'] }}@endif</span></span>
            <span class="badge bg-{{ $entry['alert_color'] }}{{ $entry['alert_color'] === 'warning' ? ' text-dark' : '' }}">{{ $entry['alert_label'] }}</span>
        </summary>
        <div class="pt-3 ps-2">
            @if(!$entry['is_manual'])<div class="text-muted small mb-2">Automatico · {{ $entry['stato_label'] }}</div>@endif
            <div class="calendar-description">{{ $entry['descrizione'] ?: 'Nessuna descrizione aggiuntiva.' }}</div>
            <div class="d-flex gap-2 flex-wrap mt-3">
                @if($entry['is_manual'])
                    <button type="button" class="btn btn-sm btn-soft-primary" data-calendar-edit="modalEvento{{ $entry['id'] }}">Modifica</button>
                    @if($entry['categoria'] === 'attivita')
                        <form data-confirm="off" method="POST" action="{{ route('calendario.status', $entry['id']) }}">
                            @csrf
                            <input type="hidden" name="contesto" value="{{ $contesto }}">
                            <input type="hidden" name="struttura_id" value="{{ $entry['model']->struttura_id }}">
                            <input type="hidden" name="stato" value="{{ in_array($entry['stato'], ['completata', 'chiusa']) ? 'da_fare' : 'completata' }}">
                            <button class="btn btn-sm btn-soft-success" type="submit">{{ in_array($entry['stato'], ['completata', 'chiusa']) ? 'Riapri attività' : 'Segna completata' }}</button>
                        </form>
                    @endif
                @elseif(!empty($entry['detail_link']))
                    <a class="btn btn-sm btn-soft-primary" href="{{ $entry['detail_link'] }}">{{ $entry['detail_label'] ?? 'Apri gestione' }}</a>
                @endif
            </div>
        </div>
    </details>
@empty
    <div class="text-muted text-center py-4">Nessuna voce per questo giorno con i filtri selezionati.</div>
@endforelse
