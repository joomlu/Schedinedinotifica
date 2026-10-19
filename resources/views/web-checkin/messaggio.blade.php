@extends('layouts.master')
@section('title', 'Anteprima invito Web Check-in')
@section('content')
@php($testo = $comunicazione->contenuto)
<div class="card"><div class="card-body">
<h1 class="h4">Anteprima della comunicazione preparata</h1>
@foreach(['success'=>'success','error'=>'danger','warning'=>'warning'] as $key=>$style)@if(session($key))<div class="alert alert-{{ $style }}" role="status">{{ session($key) }}</div>@endif @endforeach
<p>Destinatario: <strong>{{ $testo['destinatario'] ?? 'Testo da copiare' }}</strong></p>
<p>Stato: <strong>{{ \App\Services\WebCheckinComunicazioneStato::esito($comunicazione) }}</strong></p>
@if($comunicazione->accettato_at)<p>Ultimo invio di questo messaggio: {{ $comunicazione->accettato_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} · {{ $testo['destinatario'] }}</p>@endif
@if($comunicazione->tentativo_at)<p>Tentativo del {{ $comunicazione->tentativo_at->timezone('Europe/Rome')->format('d/m/Y H:i:s T') }}.</p>@endif
<h2 class="h5">{{ $testo['oggetto'] }}</h2>
@if($comunicazione->canale === 'email' && isset($testo['layout']))
<style>.web-email-preview{width:100%;height:900px;border:1px solid #dce4e5;border-radius:12px;pointer-events:none}@media(max-width:575px){.web-email-preview{height:1250px}}</style>
<iframe class="web-email-preview" src="{{ route('web_checkin.comunicazione.anteprima', [$richiesta->id, $comunicazione->id]) }}" title="Anteprima visiva email HTML" tabindex="-1" inert sandbox="" referrerpolicy="no-referrer"></iframe>
<p class="small text-muted mt-2">L’anteprima visiva non apre il modulo e non registra una visita dell’ospite. Il pulsante nell’email ricevuta apre direttamente il modulo pubblico.</p>
<a class="btn btn-light mb-3" href="{{ route('web_checkin.anteprima_modulo', $richiesta->id) }}">Anteprima modulo ospite senza registrare una visita</a>
@if(!$testo['identita']['logo_path'])<div class="alert alert-warning">Configura il logo reale dell’hotel prima dell’invio. <a href="{{ route('struttura.edit') }}">Configura logo</a></div>@endif
@if($impedimentoEmail)<div class="alert alert-warning">{{ $impedimentoEmail }}</div>@endif
@if(!$comunicazione->tentativo_at && !$impedimentoEmail)
<form method="post" action="{{ route('web_checkin.comunicazione.invia', [$richiesta->id, $comunicazione->id]) }}" class="my-3" data-web-invio data-confirm-ignore>@csrf
<label class="d-block mb-2"><input type="checkbox" name="conferma_destinatario" value="1" required> Confermo il destinatario {{ $testo['destinatario'] }}</label>
<button class="btn btn-primary" type="submit" >Invia Web Check-in</button>
</form>
@endif
<p class="small text-muted">L’accettazione SMTP non certifica la consegna. Se l’esito è incerto, verifica prima con l’assistenza: questo tentativo non viene ripetuto. Un nuovo invio volontario richiede una nuova preparazione esplicita.</p>
<details><summary>Versione testuale dell’email</summary><pre style="white-space:pre-wrap;overflow-wrap:anywhere">{{ \App\Mail\WebCheckinInvito::testo($testo) }}</pre></details>
@else
<button class="btn btn-soft-secondary mb-3" id="copia-invito" type="button">Copia oggetto e testo</button>
<textarea class="form-control mb-3" id="testo-invito" rows="8" readonly aria-label="Testo della comunicazione">{{ $testo['oggetto']."\n\n".$testo['corpo'] }}</textarea>
@if($comunicazione->canale === 'whatsapp')<form method="post" action="{{ route('web_checkin.comunicazione.apri', [$richiesta->id, $comunicazione->id]) }}">@csrf<button class="btn btn-primary" type="submit">Apri WhatsApp esterno</button></form><p>L’apertura della conversazione non dimostra invio o consegna.</p>@endif
@if($comunicazione->canale === 'email')<p>Invito precedente in formato testuale: prepara una nuova comunicazione per utilizzare l’email HTML.</p>@endif
@endif
<p class="small text-muted mt-3">Preparazione del {{ $comunicazione->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }}. Modifiche di dati/logo, revoca, rigenerazione o scadenza possono richiedere un nuovo invito. Il contenuto finale è conservato cifrato.</p>
@if($richiesta->stato !== 'convertito')
<a class="btn btn-primary" href="{{ route('web_checkin.comunicazioni', $richiesta->id) }}">{{ $comunicazione->stato === 'accettato' && !$impedimentoEmail ? 'Reinvia Web Check-in' : 'Modifica invito e prepara un nuovo tentativo' }}</a>
@endif
<a class="btn btn-light" href="{{ route('web_checkin.edit', $richiesta->id) }}">Correggi richiesta o link</a>
<a class="btn btn-light" href="{{ route('schedina.web') }}">Torna all’elenco</a>
</div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('form[data-web-invio]').forEach(f=>f.addEventListener('submit',()=>{const b=f.querySelector('button[type=submit]');if(b){b.disabled=true;b.textContent='Invio in corso…';}}));const button=document.getElementById('copia-invito'),text=document.getElementById('testo-invito');button?.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(text.value);button.textContent='Testo copiato';}catch(_){text.focus();text.select();}});});
</script>
@endpush
