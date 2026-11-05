@extends('layouts.master')
@section('title', 'Email Web Check-in')
@section('content')
@php($testo=$comunicazione->contenuto)
<div class="card"><div class="card-body">
@include('web-checkin.partials.dettaglio-header',['sezione'=>'Email'])
@foreach(['success'=>'success','error'=>'danger','warning'=>'warning'] as $key=>$style)@if(session($key))<div class="alert alert-{{ $style }}" role="status">{{ session($key) }}</div>@endif @endforeach
<p>Stato: <strong>{{ \App\Services\WebCheckinComunicazioneStato::esito($comunicazione) }}</strong></p>
@if($comunicazione->accettato_at)<p>Ultimo invio: {{ $comunicazione->accettato_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} · {{ $testo['destinatario'] }}</p>@endif
@if($comunicazione->canale==='email' && isset($testo['layout']))
<button class="btn btn-primary mb-3" type="button" id="apri-email">Visualizza email</button>
@include('web-checkin.partials.email-dialog')
@include('web-checkin.partials.email-ui')
<details><summary>Versione testuale dell’email</summary><pre style="white-space:pre-wrap;overflow-wrap:anywhere">{{ \App\Mail\WebCheckinInvito::testo($testo) }}</pre></details>
@else
<h2 class="h5">{{ $testo['oggetto'] }}</h2><button class="btn btn-light mb-3" id="copia-invito" type="button">Copia oggetto e testo</button><textarea class="form-control mb-3" id="testo-invito" rows="8" readonly aria-label="Testo della comunicazione">{{ $testo['oggetto']."\n\n".$testo['corpo'] }}</textarea>
@if($comunicazione->canale==='whatsapp')<form method="post" action="{{ route('web_checkin.comunicazione.apri',[$richiesta->id,$comunicazione->id]) }}">@csrf<button class="btn btn-primary" type="submit">Apri WhatsApp esterno</button></form><p>L’apertura non dimostra invio o consegna.</p>@endif
@endif
@if($richiesta->stato!=='convertito')<a class="btn btn-light mt-3" href="{{ route('web_checkin.comunicazioni',$richiesta->id) }}">{{ $comunicazione->tentativo_at ? 'Prepara un reinvio volontario' : 'Torna alla modifica' }}</a>@endif
</div></div>
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>{const b=document.getElementById('copia-invito'),t=document.getElementById('testo-invito');b?.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(t.value);b.textContent='Testo copiato';}catch(_){t.focus();t.select();}});});</script>
@endpush
