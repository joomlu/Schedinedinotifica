@php($ui = $comunicazioneUI ?? app(\App\Services\WebCheckinComunicazioneStato::class)->of($richiesta))
<div class="web-comunicazione">
<strong>Comunicazione: {{ $ui['stato'] }}</strong>
@if($ui['inviato'])<div class="small">Ultimo invio: {{ $ui['inviato']->accettato_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} · {{ $ui['inviato']->contenuto['destinatario'] ?? 'Destinatario precedente non disponibile' }}</div><div class="small text-muted">Accettato dal trasporto; consegna e lettura non confermate.</div>@endif
@if($ui['ultimo'] && in_array($ui['ultimo']->stato,['non_riuscito','esito_incerto','invio_in_corso'],true))<p class="small mb-2">{{ \App\Services\WebCheckinComunicazioneStato::esito($ui['ultimo']) }}</p>@endif
@if(!$richiesta->processata())
@if($ui['impedimento'])<p class="small mt-2 mb-2">{{ $ui['impedimento'] }}</p><div class="d-flex flex-wrap gap-2 mb-2"><a class="btn btn-outline-primary btn-sm" href="{{ route('web_checkin.edit',$richiesta->id) }}">Correggi richiesta o link</a><a class="btn btn-light btn-sm" href="{{ route('struttura.edit') }}">Configura struttura e logo</a></div>@endif
<div class="d-flex flex-wrap gap-2 mt-2">
@if($ui['pronto'])<a class="btn btn-primary" href="{{ route('web_checkin.comunicazione',[$richiesta->id,$ui['ultimo']->id]) }}">Invia Web Check-in</a>@elseif($ui['ultimo']?->stato === 'non_riuscito' && !$ui['impedimento'])<a class="btn btn-primary" href="{{ route('web_checkin.comunicazioni',$richiesta->id) }}">Riprova invio</a>@elseif($ui['inviato'] && !$ui['impedimento'])<a class="btn btn-primary" href="{{ route('web_checkin.comunicazioni',$richiesta->id) }}">Reinvia Web Check-in</a>@endif
<a class="btn {{ !$ui['ultimo'] && !$ui['impedimento'] ? 'btn-primary' : 'btn-light' }} btn-sm" href="{{ route('web_checkin.comunicazioni',$richiesta->id) }}">{{ $ui['ultimo'] ? 'Modifica invito' : 'Prepara invito' }}</a>
@if(app(\App\Services\WebCheckinLink::class)->valid($richiesta))<button type="button" class="btn btn-light btn-sm" data-web-copy="{{ filled($richiesta->short_token) ? route('web_checkin.public.short.show',$richiesta->short_token) : route('web_checkin.public.show',$richiesta->token) }}">Copia link</button>@endif
</div>
@endif
</div>
@once
@push('scripts')
<script>document.addEventListener('click',async e=>{const b=e.target.closest('[data-web-copy]');if(!b)return;try{await navigator.clipboard.writeText(b.dataset.webCopy);b.textContent='Link copiato';}catch(_){b.textContent='Copia non disponibile: apri la richiesta';}});</script>
@endpush
@endonce
