@php
    $senzaDati = $riepilogo->every(fn ($m) => array_sum(array_intersect_key($m['totale'], array_flip(['presenti','arrivi','partenze','presenze']))) === 0);
@endphp
<p>Residenza degli ospiti: Italia, estero e non disponibile. Saldo iniziale prima dei movimenti del primo giorno, incluse le partenze di quel giorno. Presenze = persone × notti, arrivo incluso e partenza esclusa.</p>
@if($senzaDati)
    <div class="alert alert-info">Nessun soggiorno o movimento nel periodo selezionato.</div>
@else
    @foreach($riepilogo as $mese)
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3"><h6>{{ \Illuminate\Support\Str::title($mese['mese_label']) }}</h6><a class="btn btn-light no-print" target="_blank" href="{{ route('presenze.print.dettaglio', ['dal' => $mese['dal']->toDateString(), 'al' => $mese['al']->toDateString()]) }}">Stampa dettaglio mese</a></div>
        <div class="table-responsive"><table class="table table-bordered align-middle">
            <thead><tr><th>Residenza</th><th>Saldo iniziale</th><th>Arrivi</th><th>Partenze</th><th>Saldo finale</th><th>Presenze (notti)</th></tr></thead>
            <tbody>
            @foreach(['italiane' => 'Italia', 'straniere' => 'Estero', 'sconosciute' => 'Non disponibile', 'totale' => 'Totale'] as $key => $label)
                @php($d = $mese[$key])
                <tr><td>{{ $label }}</td><td>{{ $d['presenti'] }}</td><td>{{ $d['arrivi'] }}</td><td>{{ $d['partenze'] }}</td><td>{{ $d['presenti'] + $d['arrivi'] - $d['partenze'] }}</td><td>{{ $d['presenze'] }}</td></tr>
            @endforeach
            </tbody>
        </table></div>
    @endforeach
    @php($inizio = $riepilogo->first()['totale']['presenti'])
    @php($arrivi = $riepilogo->sum('totale.arrivi'))
    @php($partenze = $riepilogo->sum('totale.partenze'))
    <p class="fw-semibold">Totale periodo: saldo iniziale {{ $inizio }}, arrivi {{ $arrivi }}, partenze {{ $partenze }}, saldo finale {{ $inizio + $arrivi - $partenze }}, presenze {{ $riepilogo->sum('totale.presenze') }}.</p>
@endif
