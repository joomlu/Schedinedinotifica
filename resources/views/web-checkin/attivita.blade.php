@extends('layouts.master')
@section('title', $titoloAttivita)
@section('content')
@php($richiestaBloccata=$richiesta->processata())
@include('web-checkin.partials.dettaglio-header',['sezione'=>'Ospiti'])
<h2 class="h5 mb-3">{{ $titoloAttivita }}</h2>
@if($richiestaBloccata)<p class="badge bg-primary">Check-in effettuato · Storico</p>@endif

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if($attivita==='ospiti' || $attivita==='check-in')
@if(!$richiestaBloccata)<p><span class="badge bg-secondary">{{ $richiesta->compilato_at ? 'Compilazione completata' : ($progresso['caricate'] > 0 ? 'Compilazione in corso' : 'In attesa di compilazione') }}</span> · Check-in operativo: <strong>Da effettuare</strong></p>@endif
@if($richiesta->schedina)<a class="btn btn-light mb-3" href="{{ route('schedina.edit',$richiesta->schedina->id) }}">{{ $richiestaBloccata ? 'Apri schedina collegata' : 'Correggi dati ospiti' }}</a>@endif
<div class="card"><div class="card-body"><h2 class="h5">Dati degli ospiti</h2>
@if($richiestaBloccata && $richiesta->conversione_snapshot)
<p class="text-muted">Copia protetta dei dati utilizzati al check-in. Le modifiche successive alla schedina non cambiano questa copia.</p>
@include('web-checkin.partials.persona',['persona'=>$richiesta->conversione_snapshot['titolare'],'titolo'=>'Ospite principale'])
@foreach($richiesta->conversione_snapshot['componenti'] as $i=>$p)@include('web-checkin.partials.persona',['persona'=>$p,'titolo'=>'Ospite '.($i+2)])@endforeach
@elseif($richiesta->schedina)
@include('web-checkin.partials.persona',['persona'=>$richiesta->schedina->getAttributes(),'titolo'=>'Ospite principale'])
@foreach($componentiRicevuti as $i=>$p)@include('web-checkin.partials.persona',['persona'=>$p->getAttributes(),'titolo'=>'Ospite '.($i+2)])@endforeach
@else<p>Dati ospite non collegati. I riferimenti originali della richiesta restano conservati.</p>@endif
@if($richiesta->compilazione_snapshot)<details class="mt-3"><summary>Dati ricevuti dall’ospite prima della revisione</summary><div class="mt-3">@include('web-checkin.partials.persona',['persona'=>$richiesta->compilazione_snapshot['titolare'],'titolo'=>'Ospite principale'])@foreach($richiesta->compilazione_snapshot['componenti'] as $i=>$p)@include('web-checkin.partials.persona',['persona'=>$p,'titolo'=>'Ospite '.($i+2)])@endforeach</div></details>@endif
</div></div>
@endif
@if($attivita==='check-in')
@if(!$richiestaBloccata && $richiesta->schedina)<div id="effettua-check-in" class="card border-success"><div class="card-body"><h2 class="h5">Effettua check-in</h2><p>La conferma dell’ospite non effettua il check-in. Controlla i dati e completa camera e letti; questa azione valida nuovamente e crea una sola schedina.</p>@if($datiPendenti)<details class="mb-3"><summary>Controlli ancora da completare</summary><ul>@foreach($datiPendenti as $dato)<li>{{ $dato }}</li>@endforeach</ul></details>@endif
<form method="POST" action="{{ route('web_checkin.to_schedina',$richiesta->id,false) }}" data-confirm-title="Conferma check-in" data-confirm-text="Confermi i dati ospiti, camera e letti e la creazione della schedina?" data-confirm-button="Conferma check-in">@csrf<input type="hidden" name="web_versione_richiesta" value="{{ app(\App\Services\WebCheckinVersione::class)->of($richiesta) }}"><div class="row g-3"><div class="col-sm-4"><label for="web-camera" class="form-label">Camera</label><input id="web-camera" class="form-control" name="room" type="number" min="1" max="999" required value="{{ old('room',$richiesta->schedina->room) }}"></div><div class="col-sm-4"><label for="web-letti" class="form-label">Letti</label><input id="web-letti" class="form-control" name="beds" type="number" min="1" max="999" required value="{{ old('beds',$richiesta->schedina->beds) }}"></div><div class="col-12"><button class="btn btn-success" type="submit">Effettua check-in</button></div></div></form></div></div>@endif
@endif
@if($attivita==='link')
<div class="card"><div class="card-body"><h2 class="h5">Gestisci link</h2><p>Accesso {{ $richiesta->link_revoked_at ? 'revocato' : (app(\App\Services\WebCheckinLink::class)->valid($richiesta) ? 'valido' : ($richiesta->link_issued_at ? 'scaduto' : 'non emesso')) }}. Scadenza: {{ $richiesta->link_expires_at?->timezone('Europe/Rome')->format('d/m/Y H:i T') ?? 'non emessa' }}.</p><div class="d-flex flex-wrap gap-2 mb-3"><form method="POST" action="{{ route('web_checkin.revoke',$richiesta->id,false) }}" data-confirm-ignore>@csrf<button class="btn btn-outline-danger" type="submit">Revoca link</button></form>@if(!$richiestaBloccata)<form method="POST" action="{{ route('web_checkin.regenerate',$richiesta->id,false) }}" data-confirm-title="Emetti nuovo link" data-confirm-text="Tutti i collegamenti precedenti saranno invalidati. Nessuna comunicazione sarà inviata automaticamente." data-confirm-button="Emetti nuovo link">@csrf<button class="btn btn-outline-primary" type="submit">Emetti nuovo link</button></form>@endif</div>
@if($publicUrl && app(\App\Services\WebCheckinLink::class)->valid($richiesta))<label for="link-ospite" class="form-label">Link ospite</label><input id="link-ospite" class="form-control mb-2" readonly value="{{ $publicUrl }}"><button type="button" class="btn btn-light mb-3" onclick="const e=document.getElementById('link-ospite');navigator.clipboard.writeText(e.value).then(()=>this.textContent='Link copiato').catch(()=>{e.focus();e.select();})">Copia link</button>@endif
<p class="small text-muted">La nuova emissione invalida tutti i collegamenti precedenti. Accettazione del trasporto e consegna al destinatario sono risultati distinti.</p></div></div>
@endif
@if($attivita==='cestino')
<div class="card"><div class="card-body"><h2 class="h5">Sposta nel Cestino</h2><p>La richiesta non sarà più accessibile dal link pubblico. Schedina e clienti collegati vengono conservati; il ripristino richiederà un nuovo accesso esplicito.</p><form action="{{ route('web_checkin.destroy',$richiesta->id,false) }}" method="POST" data-confirm-title="Sposta nel Cestino" data-confirm-text="Il link ospite verrà disattivato. Schedina e clienti restano conservati." data-confirm-button="Sì, sposta nel Cestino">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Sposta richiesta nel Cestino</button></form></div></div>
@endif
@endsection
