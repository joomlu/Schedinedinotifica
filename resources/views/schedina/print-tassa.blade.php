@extends('layouts.master')
@section('title') Ricevuta Tassa di soggiorno @endsection
@section('content')
@if(isset($erroreCalcolo))
    <div class="card"><div class="card-body"><h5>Calcolo fiscale non disponibile</h5>
        <p>Struttura {{ $struttura?->nome_struttura }} — Schedina {{ $schedina->id }} — anno {{ $annoFiscale }}</p>
        <div class="alert alert-warning">{{ $erroreCalcolo }}</div>
        <a class="btn btn-primary" href="{{ route('tassa_di_soggiorno.edit', ['anno_fiscale' => $annoFiscale]) }}">Configurazione fiscale</a>
        <a class="btn btn-light" href="{{ route('schedina.edit', $schedina->id) }}">Torna alla Schedina</a>
    </div></div>
@else
<div class="d-flex justify-content-between mb-3 no-print"><a class="btn btn-light" href="{{ route('schedina.edit', $schedina->id) }}">Torna alla Schedina</a><button type="button" class="btn btn-primary" onclick="window.print()">Stampa ricevuta</button></div>
<article id="ricevuta-tassa-card" class="ricevuta-tassa" data-anteprima="{{ ($anteprima ?? false) ? 'si' : 'no' }}">
    @if($anteprima ?? false)<p class="ricevuta-note">Anteprima ricevuta — consultazione senza emissione o consolidamento.</p>@endif
    <header class="ricevuta-header">
        <div class="ricevuta-hotel">
            @if($struttura?->logo)<img src="{{ asset($struttura->logo) }}" alt="Logo struttura" class="ricevuta-logo">@endif
            <strong>{{ $struttura->nome_struttura ?? 'Struttura' }}</strong>
            <small>{{ $struttura->localita ?? '' }}</small>
            <small>{{ trim(($struttura->indirizzo ?? '').' '.($struttura->numero_civico ?? '')) }}</small>
            <small>{{ $struttura->cap ?? '' }} {{ $struttura->citta ?? '' }}</small>
        </div>
        <div class="ricevuta-titolo"><small>TASSA DI SOGGIORNO</small><h1>Ricevuta</h1><p>Un soggiorno che valorizza il territorio</p></div>
        <div class="ricevuta-comune">
            @if($logoComune)<img src="{{ asset($logoComune) }}" alt="Stemma Comune" class="ricevuta-logo">@endif
            <small>Comune di</small><strong>{{ $struttura->citta ?? 'Bellaria-Igea Marina' }}</strong>
        </div>
    </header>
    <section class="ricevuta-messaggio">
    <h2>Grazie per aver scelto {{ $struttura->citta ?? 'Bellaria-Igea Marina' }}!</h2>
    <p>Il vostro soggiorno contribuisce a sostenere il territorio, la qualità dei servizi turistici e la valorizzazione della nostra città. Vi ringraziamo per la fiducia e vi auguriamo una splendida permanenza.</p>
</section>

    <section class="ricevuta-dati">
        <div><small>Ospite principale</small><strong>{{ trim(($schedina->surname ?? '').' '.($schedina->name ?? '')) ?: '—' }}</strong></div>
        <div><small>N. Schedina</small><strong>{{ $schedina->scheda ?: $schedina->id }}</strong></div>
        <div><small>Periodo soggiorno</small><strong>{{ $arrivo?->format('d/m/Y') }} — {{ $partenza?->format('d/m/Y') }}</strong></div>
        <div><small>Stato documento</small><strong>{{ ($anteprima ?? false) ? 'Anteprima del calcolo' : ($storicoVersione ? 'Storico consolidato' : 'Calcolo corrente') }}</strong></div>
    </section>
    <div class="ricevuta-tabella-wrap"><table class="ricevuta-tabella">
        <thead><tr><th>Nome e Cognome</th><th>Età</th><th>Esente</th><th>Motivo</th><th>Notti</th><th>Imponibili</th><th>Esenti</th><th>Oltre max</th><th>Tariffa €</th><th>Subtotale €</th></tr></thead>
        <tbody>@foreach($dettaglio['righe'] as $riga)
            <tr><td>{{ $riga['nome'] }}</td><td>{{ $riga['eta'] ?? '—' }}</td><td>{{ !empty($riga['esenzione_parziale']) ? 'Parziale' : ($riga['esente'] ? 'Sì' : 'No') }}</td><td>{{ $riga['motivo'] ?? 'Ordinario' }}</td><td>{{ $riga['notti_totali'] }}</td><td>{{ $riga['notti_tassate'] }}</td><td>{{ $riga['notti_esenti'] }}</td><td>{{ $riga['notti_oltre_max'] }}</td><td>{{ $riga['aliquota'] === null ? 'Variabile' : number_format($riga['aliquota'], 2, ',', '.') }}</td><td>{{ number_format($riga['subtotale'], 2, ',', '.') }}</td></tr>
        @endforeach</tbody>
    </table></div>
    @foreach($dettaglio['righe'] as $riga)
        @if($riga['notti_oltre_max'] > 0 && $riga['subtotale'] == 0)<p class="ricevuta-note">{{ $riga['nome'] }}: limite massimo raggiunto, {{ $riga['notti_oltre_max'] }} notti oltre il limite escluse dall’imposta.</p>@endif
        @if($riga['aliquota'] === null)<p class="ricevuta-note">Tariffe per {{ $riga['nome'] }}: @foreach($riga['segmenti'] as $segmento){{ $segmento['notti_imponibili'] }} notti a € {{ number_format($segmento['aliquota'], 2, ',', '.') }}{{ $loop->last ? '.' : '; ' }}@endforeach</p>@endif
    @endforeach
    <p class="ricevuta-note">Le notti oltre il limite massimo non producono imposta. Le notti fuori dal periodo di applicazione sono escluse dal calcolo.</p>
    <div class="ricevuta-totale"><span>Totale tassa di soggiorno</span><strong data-tassa-totale="{{ $dettaglio['totale'] }}">€ {{ number_format($dettaglio['totale'], 2, ',', '.') }}</strong></div>
    <p class="ricevuta-note">{{ $storicoVersione ? 'Versione storica consolidata '.$storicoVersione : 'Calcolo corrente: le versioni consolidate si consultano dal rapporto Tassa.' }}</p>
    <p class="ricevuta-note">Documento del calcolo relativo alla Schedina indicata. Non attesta il pagamento o l’accettazione da parte del Comune.</p>
    @if($tassaConfig?->ricevuta_immagine)<figure class="ricevuta-immagine"><img src="{{ str_starts_with($tassaConfig->ricevuta_immagine, 'tassa-ricevute/') ? route('tassa_di_soggiorno.immagine', $storicoVersione ? ['export_id' => request('export_id')] : []) : asset($tassaConfig->ricevuta_immagine) }}" alt="Immagine turistica scelta dalla struttura"></figure>@endif
    <p class="ricevuta-saluto">Grazie e arrivederci!</p>
    <div class="ricevuta-software">@include('layouts.footer')</div>
</article>
@endif
@endsection
@push('css')
<style>
.ricevuta-tassa{max-width:1000px;margin:0 auto;background:#fff;padding:36px;color:#243d46;border-top:5px solid #287c85;box-shadow:0 8px 28px #243d4612}
.ricevuta-header{display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:20px;align-items:start;border-bottom:1px solid #ccdede;padding-bottom:24px}.ricevuta-header small,.ricevuta-header strong{display:block}.ricevuta-logo{max-width:100px;max-height:70px;object-fit:contain;margin-bottom:10px}.ricevuta-titolo,.ricevuta-comune{text-align:center}.ricevuta-titolo h1{font-family:Georgia,serif;font-size:36px;color:#287c85;margin:8px 0}.ricevuta-titolo>small{letter-spacing:2px}.ricevuta-titolo p,.ricevuta-note{font-size:12px;color:#607b83}.ricevuta-messaggio{background:#f0f7f6;padding:20px;margin:24px 0;border-left:3px solid #7eafa5}.ricevuta-messaggio h2{font-size:19px;color:#287c85}.ricevuta-messaggio p{margin:0;font-size:13px;line-height:1.65}.ricevuta-dati{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;margin-bottom:22px}.ricevuta-dati small,.ricevuta-dati strong{display:block}.ricevuta-dati small{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#607b83}.ricevuta-tabella-wrap{overflow:auto}.ricevuta-tabella{width:100%;font-size:12px;border-collapse:collapse}.ricevuta-tabella th{background:#287c85;color:#fff;font-weight:500}.ricevuta-tabella th,.ricevuta-tabella td{padding:9px 5px;border-bottom:1px solid #dce8e7}.ricevuta-tabella td{overflow-wrap:anywhere}.ricevuta-note{margin-top:12px}.ricevuta-totale{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;background:#f0f7f6;border-bottom:2px solid #287c85}.ricevuta-totale strong{font-size:26px;color:#287c85}.ricevuta-immagine{margin:20px 0 0}.ricevuta-immagine img{width:100%;height:100px;object-fit:cover}.ricevuta-saluto{text-align:center;font-family:Georgia,serif;font-size:22px;margin:20px 0}.ricevuta-software .footer{display:block!important;position:static!important;height:auto;padding:15px 0 0;background:transparent;border-top:1px solid #dce8e7}.ricevuta-software .small{font-size:10px}
@media(max-width:600px){.ricevuta-tassa{padding:16px}.ricevuta-header{grid-template-columns:1fr}.ricevuta-header>div{text-align:center}.ricevuta-dati{grid-template-columns:1fr}.ricevuta-totale{gap:10px;flex-wrap:wrap}}
@media screen and (max-width:600px){.ricevuta-tabella{min-width:760px}.ricevuta-tabella th:nth-child(4),.ricevuta-tabella td:nth-child(4){min-width:160px;overflow-wrap:normal;word-break:normal}}
@media print{@page{size:A4 portrait;margin:12mm}body{background:#fff!important}body *:not(:has(#ricevuta-tassa-card)):not(#ricevuta-tassa-card):not(#ricevuta-tassa-card *){display:none!important}body *:has(#ricevuta-tassa-card){position:static!important;margin:0!important;padding:0!important;width:100%!important;min-height:0!important}#ricevuta-tassa-card{position:static;width:100%;max-width:none;margin:0;padding:0;box-shadow:none}body .no-print{display:none!important}.ricevuta-header{grid-template-columns:1fr 1.4fr 1fr!important}.ricevuta-dati{grid-template-columns:repeat(2,1fr)!important}.ricevuta-tabella-wrap{overflow:visible}.ricevuta-tabella{font-size:9px;table-layout:fixed}.ricevuta-tabella th,.ricevuta-tabella td{padding:6px 3px}.ricevuta-tabella th:first-child{width:18%}.ricevuta-tabella th:nth-child(4){width:18%}thead{display:table-header-group}tr,.ricevuta-totale,.ricevuta-header,.ricevuta-software,.ricevuta-immagine{break-inside:avoid}.ricevuta-tassa{print-color-adjust:exact;-webkit-print-color-adjust:exact}.ricevuta-software .row{display:block}.ricevuta-software .col-sm-6{width:100%}.ricevuta-software .text-sm-end{text-align:left!important}}
</style>
@endpush
