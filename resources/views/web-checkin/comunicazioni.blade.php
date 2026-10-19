@extends('layouts.master')
@section('title', 'Comunicazioni Web Check-in')
@section('content')
<div class="card"><div class="card-body">
<h1 class="h4">Invito Web Check-in — {{ $richiesta->numero_prenotazione }}</h1>
<p>Personalizza l’invito, verifica destinatario e anteprima, poi scegli il canale. Le modifiche diventano un modello riutilizzabile solo con un’azione esplicita.</p>
<p><strong>Destinatario email:</strong> {{ $richiesta->email ?: 'Mancante' }} · <a href="{{ route('web_checkin.edit',$richiesta->id) }}">Correggi destinatario e dati richiesta</a></p>
@if($comunicazioneUI['impedimento'])<div class="alert alert-warning">{{ $comunicazioneUI['impedimento'] }}</div>@endif
@if($comunicazioneUI['inviato'])<p>Nuovo invio volontario: verifica destinatario e messaggio. Verrà registrato un nuovo tentativo senza modificare quelli precedenti.</p>@endif
<a class="btn btn-light mb-3" href="{{ route('web_checkin.edit', $richiesta->id) }}">Torna alla richiesta</a>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(!$identita['logo_path'])
<div class="alert alert-warning">Manca un logo hotel valido. Configuralo prima dell’invio email. La richiesta e il modulo ospite restano accessibili. <a href="{{ route('struttura.edit') }}">Configura il logo della struttura</a></div>
@else
<img class="mb-3" src="{{ $identita['logo_url'] }}" alt="{{ $identita['nome'] }}" style="max-width:180px;max-height:80px;object-fit:contain">
@endif
<form method="post" action="{{ route('web_checkin.identita', $richiesta->id) }}" class="mb-4 d-flex flex-wrap align-items-end gap-2">
@csrf <div><label class="form-label" for="invito-colore">Colore dell’hotel per email e modulo</label><input type="color" id="invito-colore" name="colore" value="{{ $identita['colore'] }}" class="form-control form-control-color"></div><button class="btn btn-light" type="submit">Salva colore della struttura</button>
</form>
<form method="post" action="{{ route('web_checkin.prepara', $richiesta->id) }}" id="invito-form">
@csrf
@if($comunicazioneUI['ultimo']?->stato === 'esito_incerto')<div class="alert alert-warning"><label><input type="checkbox" name="conferma_nuovo_tentativo" value="1" required> Ho verificato il tentativo incerto con l’amministratore e richiedo un nuovo invio volontario, consapevole del possibile duplicato.</label></div>@endif
<input type="hidden" name="idempotenza" value="{{ (string) Illuminate\Support\Str::uuid() }}">
<input type="hidden" name="azione" id="modello-azione" value=""><input type="hidden" name="versione" id="modello-versione" value="1">
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="invito-modello">Modello</label><select class="form-select" name="modello_id" id="invito-modello">
@forelse($modelli as $m)<option value="{{ $m->id }}" @selected((string)old('modello_id') === (string)$m->id) data-oggetto="{{ $m->oggetto }}" data-corpo="{{ $m->corpo }}" data-versione="{{ $m->versione }}" data-nome="{{ $m->nome }}" data-impostazioni="{{ json_encode($m->impostazioni ?? array_replace(\App\Services\WebCheckinMessaggio::layoutIniziale(), filled($richiesta->messaggio_ospite) ? ['testo'=>$richiesta->messaggio_ospite] : [])) }}">{{ $m->nome }}</option>@empty<option value="">Nessun modello: prepara un nuovo invito</option>@endforelse
</select></div>
<div class="col-md-6"><label class="form-label" for="invito-nome">Nome del modello riutilizzabile</label><input class="form-control" id="invito-nome" name="nome" maxlength="191"></div>
<div class="col-md-6"><label class="form-label" for="invito-canale">Canale</label><select class="form-select" id="invito-canale" name="canale"><option value="email">Email HTML con versione testuale</option><option value="whatsapp">WhatsApp esterno</option><option value="testo">Testo da copiare</option></select></div>
<div class="col-12"><label class="form-label" for="invito-oggetto">Oggetto</label><input class="form-control" id="invito-oggetto" name="oggetto" maxlength="255" required></div>
@foreach(['saluto' => ['Saluto', 500], 'testo' => ['Testo dell’email', 4000], 'pulsante' => ['Etichetta del pulsante principale', 80], 'firma' => ['Firma', 1000]] as $key => [$label, $max])
<div class="{{ in_array($key, ['saluto','pulsante']) ? 'col-md-6' : 'col-12' }}"><label class="form-label" for="email-{{ $key }}">{{ $label }}</label><textarea class="form-control" name="impostazioni[{{ $key }}]" id="email-{{ $key }}" rows="{{ in_array($key, ['testo','firma']) ? 3 : 1 }}" maxlength="{{ $max }}" required></textarea></div>
@endforeach
<div class="col-12"><p class="small text-muted">Logo, riepilogo prenotazione e destinazione del pulsante provengono sempre dalla richiesta e dalla sua struttura. Un modello non assegna il tipo alloggiato. Il testo sotto il pulsante si adatta al numero degli ospiti.</p></div>
<div class="col-12"><label class="form-label" for="invito-corpo">Testo da copiare o condividere su WhatsApp</label><textarea class="form-control" id="invito-corpo" name="corpo" rows="5" maxlength="12000" required></textarea><button class="btn btn-sm btn-light mt-2" type="button" id="whatsapp-breve">Usa testo breve WhatsApp</button></div>
<div class="col-12 small text-muted">Variabili disponibili (testo semplice): @foreach($variabili as $v)<code>{{ '{'.'{'.$v.'}'.'}' }}</code> @endforeach</div>
<div class="col-12 d-flex flex-wrap gap-2">
@if($richiesta->stato !== 'convertito' && app(\App\Services\WebCheckinLink::class)->valid($richiesta))<button class="btn btn-primary" type="submit">Prepara e verifica anteprima</button>@else<p>Accesso scaduto, revocato o già preso in carico. Gestisci la richiesta per l’eventuale nuova emissione esplicita.</p>@endif
<button class="btn btn-light" type="submit" name="azione" value="nuovo" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Salva nuovo modello</button>
@if($modelli->isNotEmpty())<button class="btn btn-light" type="submit" name="azione" value="duplica" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Duplica modello</button><button class="btn btn-warning" type="submit" name="azione" value="aggiorna" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Aggiorna modello</button>@endif
</div></div></form>
<h2 class="h5 mt-4">Gestisci modelli</h2>
@foreach($modelli as $m)<form method="post" action="{{ route('web_checkin.modello.elimina', [$richiesta->id, $m->id], false) }}" data-confirm-title="Elimina modello" data-confirm-text="Il modello non verrà ricreato automaticamente. Gli inviti già preparati restano conservati." data-confirm-button="Sì, elimina" class="d-flex flex-wrap align-items-center gap-2 mb-2">@csrf<input type="hidden" name="versione" value="{{ $m->versione }}"><span>{{ $m->nome }}</span><button type="submit" class="btn btn-sm btn-outline-danger">Elimina modello</button></form>@endforeach
<form method="post" action="{{ route('web_checkin.modelli.ripristina', $richiesta->id) }}">@csrf<button class="btn btn-sm btn-light" type="submit">Ripristina modelli iniziali</button></form>
<p class="small text-muted">Eliminare un modello non cancella gli inviti preparati. I modelli eliminati non vengono ricreati all’apertura; il ripristino è esplicito e conserva le personalizzazioni.</p>
<h2 class="h5 mt-4">Cronologia delle comunicazioni</h2>
<form method="post" action="{{ route('web_checkin.cronologia.rimuovi', $richiesta->id) }}">@csrf
@forelse($storico as $c)<label class="d-block mb-2"><input type="checkbox" name="comunicazioni[]" value="{{ $c->id }}"> <a href="{{ route('web_checkin.comunicazione', [$richiesta->id, $c->id]) }}">{{ $c->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} — {{ $c->canale }} — {{ ['preparato'=>'Preparato', 'apertura_richiesta'=>'Apertura esterna richiesta', 'invio_in_corso'=>'Tentativo in corso', 'accettato'=>'Inviato · accettato dal trasporto', 'non_riuscito'=>'Invio non riuscito', 'esito_incerto'=>'Esito da verificare'][$c->stato] ?? $c->stato }}</a></label>@empty<p>Nessuna comunicazione nella cronologia visibile.</p>@endforelse
@if($storico->isNotEmpty())<button type="submit" class="btn btn-outline-danger">Elimina comunicazioni selezionate dalla cronologia</button>@endif
</form>{{ $storico->links() }}
<p class="small text-muted">La rimozione dalla cronologia non annulla messaggi già inviati o tentativi in corso. Richiesta, ospiti e metadati tecnici d’idempotenza sono conservati.</p>
</div></div>
@endsection
@push('scripts')
@php($previousInput = array_replace($ripresa, \Illuminate\Support\Arr::only(session()->getOldInput(), ['nome','oggetto','corpo','canale','impostazioni'])))
@php($layoutDefaults = array_replace(\App\Services\WebCheckinMessaggio::layoutIniziale(), filled($richiesta->messaggio_ospite) ? ['testo'=>$richiesta->messaggio_ospite] : []))
<script>
document.addEventListener('DOMContentLoaded', () => {
const defaults=@json($layoutDefaults), previous=@json($previousInput), select=document.getElementById('invito-modello');
const breve=@json($testoBreve), oggettoDefault=@json($oggettoDefault);
const load=()=>{const o=select.selectedOptions[0];for(const key of ['nome','oggetto','corpo'])document.getElementById('invito-'+key).value=o?.dataset[key]??({nome:'',oggetto:oggettoDefault,corpo:breve}[key]);document.getElementById('modello-versione').value=o?.dataset.versione??1;const layout=o?.dataset.impostazioni?JSON.parse(o.dataset.impostazioni):defaults;for(const key of Object.keys(defaults))document.getElementById('email-'+key).value=layout[key]??defaults[key];};
select.addEventListener('change',load);load();document.getElementById('whatsapp-breve').addEventListener('click',()=>{document.getElementById('invito-corpo').value=breve;document.getElementById('invito-canale').value='whatsapp';});
document.querySelectorAll('#invito-form button[name="azione"]').forEach(b=>b.addEventListener('click',()=>document.getElementById('modello-azione').value=b.value));
for(const key of ['nome','oggetto','corpo','canale'])if(Object.hasOwn(previous,key))document.getElementById('invito-'+key).value=previous[key]??'';
for(const[key,value]of Object.entries(previous.impostazioni??{}))if(Object.hasOwn(defaults,key))document.getElementById('email-'+key).value=value;
});
</script>
@endpush
