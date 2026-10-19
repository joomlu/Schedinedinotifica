@extends('layouts.master')
@section('title', $storico ? 'Storico Web Check-in' : 'Web Check-in')
@section('content')
<h1 class="h3 mb-3">{{ $storico ? 'Storico Web Check-In' : 'Elenco web check-in' }}</h1>
@include('web-checkin.partials.navigation')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<p class="text-muted">{{ $storico ? 'Richieste elaborate: dati ricevuti, comunicazioni e schedina collegata restano consultabili.' : 'Prepara l’invito, consulta i dati degli ospiti e verifica tutto prima di effettuare il check-in.' }}</p>
<div class="card"><div class="card-body">
<form method="GET" action="{{ $storico ? route('web_checkin.storico') : route('schedina.web') }}" class="row g-3 mb-4 align-items-end">
<div class="col-xl-7"><label for="web-search" class="form-label">Cerca prenotazione o referente</label><input id="web-search" class="form-control" name="q" value="{{ request('q') }}" maxlength="150"></div>
<div class="col-xl-3"><label for="web-state" class="form-label">Compilazione</label><select id="web-state" class="form-select" name="stato"><option value="">Tutti gli stati</option>@foreach(['da_inviare'=>'In attesa di compilazione', 'in_compilazione'=>'In compilazione', 'compilato'=>'Compilazione completata', 'convertito'=>'Check-in effettuato'] as $v=>$label)<option value="{{ $v }}" @selected(($statoFiltro ?? '')===$v)>{{ $label }}</option>@endforeach</select></div>
<div class="col-xl-2"><button class="btn btn-primary w-100" type="submit">Cerca</button></div>
</form>
<table class="table align-middle web-elenco"><thead><tr><th>Prenotazione e referente</th><th>Soggiorno</th><th>Ospiti</th><th>Compilazione e stato</th><th>Azioni</th></tr></thead><tbody>
@forelse($richieste as $r)
@php($p=$r->progresso)
<tr>
<td data-label="Prenotazione"><a class="fw-semibold" href="{{ route('web_checkin.edit',$r->id) }}">{{ $r->numero_prenotazione }}</a><div>{{ $r->nome_referente }}</div><small class="text-muted">Richiesta {{ $r->codice }}</small></td>
<td data-label="Soggiorno">{{ $r->arrivo?->format('d/m/Y') }}<br>{{ $r->partenza?->format('d/m/Y') }}</td>
<td data-label="Ospiti"><strong>{{ $r->quantita_persone }} totali</strong><div class="small">@if($r->numero_adulti!==null){{ $r->numero_adulti }} adulti · {{ $r->numero_bambini }} bambini @else Ripartizione da definire @endif</div></td>
<td data-label="Compilazione"><div class="small mb-2"><strong>Comunicazione:</strong> {{ $r->comunicazioneUI['stato'] }}@if($r->comunicazioneUI['ultimo'] && in_array($r->comunicazioneUI['ultimo']->stato,['non_riuscito','esito_incerto'],true))<details><summary>Dettagli invio</summary>{{ \App\Services\WebCheckinComunicazioneStato::esito($r->comunicazioneUI['ultimo']) }}</details>@endif
@if($ultimoInvio=$r->comunicazioneUI['inviato'])<div>Ultimo invio: {{ $ultimoInvio->accettato_at->timezone('Europe/Rome')->format('d/m H:i') }} · {{ $ultimoInvio->contenuto['destinatario'] ?? '—' }}</div>@endif</div><span class="badge {{ $r->processata() ? 'bg-primary' : ($r->compilato_at ? 'bg-success' : 'bg-secondary') }}">{{ $r->processata() ? 'Check-in effettuato' : ($r->compilato_at ? 'Compilazione completata' : ($p['caricate'] > 0 ? 'Compilazione in corso' : 'In attesa di compilazione')) }}</span><div class="small mt-1">{{ $p['caricate'] }} caricate · {{ $p['complete'] }} complete · {{ $p['pendenti'] }} da completare</div><small>{{ app(\App\Services\WebCheckinLink::class)->valid($r) ? 'Accesso valido' : ($r->link_revoked_at ? 'Link revocato' : ($r->link_issued_at ? 'Accesso scaduto' : 'Accesso non emesso')) }}</small>@if($r->comunicazioneUI['impedimento'])<details class="small mt-1"><summary>Dettagli impedimento</summary><p>{{ $r->comunicazioneUI['impedimento'] }}</p><a href="{{ route('web_checkin.edit',$r->id) }}">Modifica richiesta</a> · <a href="{{ route('web_checkin.attivita',[$r->id,'link']) }}">Gestisci link</a></details>@endif</td>
<td data-label="Azioni"><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary btn-sm" href="{{ route('web_checkin.edit',$r->id) }}">Modifica richiesta</a>
<a class="btn btn-primary btn-sm" href="{{ route('web_checkin.comunicazioni',$r->id) }}">Anteprima e invio</a>
<details class="web-azioni"><summary class="btn btn-light btn-sm">Azioni ▾</summary><div class="d-grid gap-2 mt-2">
@foreach(['ospiti'=>'Rivedi dati ospiti','link'=>'Gestisci link','check-in'=>'Effettua check-in','cestino'=>'Sposta nel cestino'] as $azione=>$etichetta)
@if($azione==='check-in' && ($r->processata() || !$r->schedina))<span class="small">Check-in non disponibile: {{ $r->processata() ? 'già effettuato' : 'scheda ospiti assente' }}.</span>@else<a class="btn btn-light btn-sm" href="{{ route('web_checkin.attivita',[$r->id,$azione]) }}">{{ $etichetta }}</a>@endif
@endforeach
<a class="btn btn-light btn-sm" href="{{ route('web_checkin.comunicazioni',[$r->id,'storico'=>1]) }}">Storico comunicazioni</a>
</div></details></div></td>
</tr>
@empty<tr><td colspan="5">{{ $storico ? 'Nessun Web Check-in ancora elaborato.' : 'Nessuna richiesta trovata. Puoi preparare un nuovo web check-in.' }}</td></tr>@endforelse
</tbody></table>
{{ $richieste->links() }}
</div></div>
@if($schedePrecedenti && $schedePrecedenti->count())
<div class="card"><div class="card-body"><h2 class="h5">Schede Web precedenti</h2><p>Questi dati sono conservati senza una richiesta associata. Prepara la richiesta indicando referente e prenotazione: gli ospiti resteranno nella stessa scheda.</p><ul class="list-group list-group-flush">@foreach($schedePrecedenti as $sheet)<li class="list-group-item px-0 d-flex flex-wrap justify-content-between gap-2"><div><strong>{{ trim($sheet->name.' '.$sheet->surname) ?: 'Ospite da completare' }}</strong><div>{{ $sheet->arrive ? \Carbon\Carbon::parse($sheet->arrive)->format('d/m/Y') : 'Arrivo da definire' }} · {{ $sheet->cant_people }} ospiti · {{ $sheet->scheda }}</div></div><div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="{{ route('web_checkin.create',['scheda_precedente'=>$sheet->id]) }}">Prepara richiesta conservando gli ospiti</a><a class="btn btn-light" href="{{ route('schedina.edit',$sheet->id) }}">Apri dati precedenti</a></div></li>@endforeach</ul>{{ $schedePrecedenti->links() }}</div></div>
@endif
<style>.web-elenco .web-comunicazione{flex-basis:100%;min-width:0}.web-elenco td[data-label="Soggiorno"]{white-space:nowrap}.web-elenco td{overflow-wrap:anywhere}.web-elenco .btn{min-height:48px;white-space:normal;display:inline-flex;align-items:center;justify-content:center}.web-elenco .web-azioni>summary{border:1px solid #dce2e7}@media(max-width:1199px){.web-elenco thead{display:none}.web-elenco,.web-elenco tbody,.web-elenco tr,.web-elenco td{display:block;width:100%}.web-elenco tr{border:1px solid #dce2e7;border-radius:12px;padding:12px;margin:12px 0}.web-elenco td{border:0;padding:8px 0}.web-elenco td:before{content:attr(data-label);display:block;font-size:.8rem;color:#59626d}.web-elenco td .btn{flex:1}.web-elenco .web-comunicazione .btn-primary{flex-basis:100%}}</style>
@endsection
