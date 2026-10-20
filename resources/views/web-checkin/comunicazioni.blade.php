@extends('layouts.master')
@section('title', 'Comunicazioni Web Check-in')
@section('content')
<div class="card"><div class="card-body">
@include('web-checkin.partials.dettaglio-header',['sezione'=>'Email'])
<h2 class="h5">{{ request()->boolean('storico') ? 'Storico comunicazioni' : 'Personalizza email' }}</h2>
@if(!request()->boolean('storico'))
<ol class="web-email-steps" aria-label="Passaggi email"><li class="active" aria-current="step">1. Personalizza</li><li>2. Visualizza email</li><li>3. Conferma e invia</li></ol>
<a class="btn btn-light mb-3" href="{{ route('web_checkin.anteprima_modulo',$richiesta->id) }}">Anteprima modulo ospite</a>
<p><strong>Destinatario email:</strong> {{ $richiesta->email ?: 'Mancante' }} · <a href="{{ route('web_checkin.edit',$richiesta->id) }}">Correggi destinatario e dati richiesta</a></p>
@if($comunicazioneUI['impedimento'])<div class="alert alert-warning">{{ $comunicazioneUI['impedimento'] }}</div>@endif
@if($comunicazioneUI['inviato'])<p>Ultimo invio: {{ $comunicazioneUI['inviato']->accettato_at->timezone('Europe/Rome')->format('d/m/Y H:i') }}. Nuovo invio volontario: verifica destinatario e messaggio. Verrà registrato un nuovo tentativo senza modificare quelli precedenti.</p>@endif

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(!$identita['logo_path'])
<div class="alert alert-warning">Manca un logo hotel valido. Configuralo prima dell’invio email. La richiesta e il modulo ospite restano accessibili. <a href="{{ route('struttura.edit') }}" target="_blank" rel="noopener">Configura il logo della struttura (nuova scheda)</a></div>
@else
<img class="mb-3" src="{{ $identita['logo_url'] }}" alt="{{ $identita['nome'] }}" style="max-width:180px;max-height:80px;object-fit:contain">
@endif
<details class="mb-3"><summary>Identità della struttura</summary><form method="post" action="{{ route('web_checkin.identita', $richiesta->id) }}" class="mb-4 d-flex flex-wrap align-items-end gap-2">
@csrf <div><label class="form-label" for="invito-colore">Colore dell’hotel per email e modulo</label><input type="color" id="invito-colore" name="colore" value="{{ $identita['colore'] }}" class="form-control form-control-color"></div><button class="btn btn-light" type="submit">Salva colore della struttura</button>
</form></details>
<form method="post" action="{{ route('web_checkin.prepara', $richiesta->id) }}" id="invito-form" aria-busy="true"><fieldset id="web-email-fields" disabled>
@csrf
@if($comunicazioneUI['ultimo']?->stato === 'esito_incerto')<div class="alert alert-warning"><label><input type="checkbox" name="conferma_nuovo_tentativo" value="1" required> Ho verificato il tentativo incerto con l’amministratore e richiedo un nuovo invio volontario, consapevole del possibile duplicato.</label></div>@endif
<input type="hidden" name="idempotenza" value="{{ (string) Illuminate\Support\Str::uuid() }}">
<input type="hidden" name="azione" id="modello-azione" value=""><input type="hidden" name="versione" id="modello-versione" value="1">
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="invito-modello">Modello</label><select class="form-select" name="modello_id" id="invito-modello">
@forelse($modelli as $m)<option value="{{ $m->id }}" @selected((string)old('modello_id') === (string)$m->id) data-oggetto="{{ $m->oggetto }}" data-corpo="{{ $m->corpo }}" data-versione="{{ $m->versione }}" data-nome="{{ $m->nome }}" data-impostazioni="{{ json_encode($m->impostazioni ?? array_replace(\App\Services\WebCheckinMessaggio::layoutIniziale(), filled($richiesta->messaggio_ospite) ? ['testo'=>$richiesta->messaggio_ospite] : [])) }}">{{ $m->nome }}</option>@empty<option value="">Nessun modello: prepara un nuovo invito</option>@endforelse
</select></div>
<div class="col-md-6"><label class="form-label" for="invito-canale">Canale</label><select class="form-select" id="invito-canale" name="canale"><option value="email">Email HTML con versione testuale</option><option value="whatsapp">WhatsApp esterno</option><option value="testo">Testo da copiare</option></select></div>
<div class="col-12"><label class="form-label" for="invito-oggetto">Oggetto</label><input class="form-control" id="invito-oggetto" name="oggetto" maxlength="255" required></div>
@foreach(['saluto' => ['Saluto', 500], 'testo' => ['Testo dell’email', 4000], 'pulsante' => ['Etichetta del pulsante principale', 80], 'firma' => ['Firma', 1000]] as $key => [$label, $max])
<div class="{{ in_array($key, ['saluto','pulsante']) ? 'col-md-6' : 'col-12' }}"><label class="form-label" for="email-{{ $key }}">{{ $label }}</label><textarea class="form-control" name="impostazioni[{{ $key }}]" id="email-{{ $key }}" rows="{{ in_array($key, ['testo','firma']) ? 3 : 1 }}" maxlength="{{ $max }}" required></textarea></div>
@endforeach
<div class="col-12"><p class="small text-muted">Logo, riepilogo prenotazione e destinazione del pulsante provengono sempre dalla richiesta e dalla sua struttura. Un modello non assegna il tipo alloggiato. Il testo sotto il pulsante si adatta al numero degli ospiti.</p></div>
<details class="col-12"><summary>Testo per WhatsApp e condivisione</summary><div class="col-12"><label class="form-label" for="invito-corpo">Testo da copiare o condividere su WhatsApp</label><textarea class="form-control" id="invito-corpo" name="corpo" rows="5" maxlength="12000" required></textarea><button class="btn btn-sm btn-light mt-2" type="button" id="whatsapp-breve">Usa testo breve WhatsApp</button></div>
<div class="col-12"><p class="small text-muted mb-2">I dati tra parentesi vengono compilati automaticamente. Seleziona un campo del messaggio, poi il dato da inserire.</p><div id="web-dati-messaggio" class="d-flex flex-wrap gap-2" aria-label="Dati da inserire nel messaggio"></div></div>
</details><div class="col-12 d-flex flex-wrap gap-2">
@if($richiesta->stato !== 'convertito' && app(\App\Services\WebCheckinLink::class)->valid($richiesta))<button class="btn btn-primary" type="submit" data-confirm-ignore id="visualizza-email">Visualizza email</button>@else<p>Accesso scaduto, revocato o già preso in carico. <a href="{{ route('web_checkin.attivita',[$richiesta->id,'link']) }}">Gestisci link</a> per l’eventuale nuova emissione esplicita.</p>@endif
<details class="w-100 mt-3"><summary>Salva o aggiorna un modello</summary><div class="col-md-6"><label class="form-label" for="invito-nome">Nome del modello riutilizzabile</label><input class="form-control" id="invito-nome" name="nome" maxlength="191"></div>
<div class="d-flex flex-wrap gap-2 mt-2"><button class="btn btn-light" type="submit" name="azione" value="nuovo" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Salva nuovo modello</button>
@if($modelli->isNotEmpty())<button class="btn btn-light" type="submit" name="azione" value="duplica" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Duplica modello</button><button class="btn btn-warning" type="submit" name="azione" value="aggiorna" formaction="{{ route('web_checkin.modello', $richiesta->id) }}">Aggiorna modello</button>@endif
</div></details></div></div></fieldset></form><div id="email-preview-error" class="alert alert-danger mt-3" role="alert" tabindex="-1" hidden></div><div id="web-email-slot"></div>
@include('web-checkin.partials.email-ui')
<details class="mt-4"><summary>Gestisci modelli</summary>
@foreach($modelli as $m)<form method="post" action="{{ route('web_checkin.modello.elimina', [$richiesta->id, $m->id], false) }}" data-confirm-title="Elimina modello" data-confirm-text="Il modello non verrà ricreato automaticamente. Gli inviti già preparati restano conservati." data-confirm-button="Sì, elimina" class="d-flex flex-wrap align-items-center gap-2 mb-2">@csrf<input type="hidden" name="versione" value="{{ $m->versione }}"><span>{{ $m->nome }}</span><button type="submit" class="btn btn-sm btn-outline-danger">Elimina modello</button></form>@endforeach
<form method="post" action="{{ route('web_checkin.modelli.ripristina', $richiesta->id) }}">@csrf<button class="btn btn-sm btn-light" type="submit">Ripristina modelli iniziali</button></form>
<p class="small text-muted">Eliminare un modello non cancella gli inviti preparati. I modelli eliminati non vengono ricreati all’apertura; il ripristino è esplicito e conserva le personalizzazioni.</p>
</details>
@endif
@if(request()->boolean('storico'))<details open class="mt-4"><summary>Storico comunicazioni</summary>
<form method="post" action="{{ route('web_checkin.cronologia.rimuovi', $richiesta->id) }}">@csrf
@forelse($storico as $c)<label class="d-block mb-2"><input type="checkbox" name="comunicazioni[]" value="{{ $c->id }}"> <a href="{{ route('web_checkin.comunicazione', [$richiesta->id, $c->id]) }}">{{ $c->created_at->timezone('Europe/Rome')->format('d/m/Y H:i:s') }} — {{ $c->canale }} — {{ ['preparato'=>'Preparato', 'apertura_richiesta'=>'Apertura esterna richiesta', 'invio_in_corso'=>'Tentativo in corso', 'accettato'=>'Inviato · accettato dal trasporto', 'non_riuscito'=>'Invio non riuscito', 'esito_incerto'=>'Esito da verificare'][$c->stato] ?? $c->stato }}</a></label>@empty<p>Nessuna comunicazione nella cronologia visibile.</p>@endforelse
@if($storico->isNotEmpty())<button type="submit" class="btn btn-outline-danger">Elimina comunicazioni selezionate dalla cronologia</button>@endif
</form>{{ $storico->links() }}
<p class="small text-muted">La rimozione dalla cronologia non annulla messaggi già inviati o tentativi in corso. Richiesta, ospiti e metadati tecnici d’idempotenza sono conservati.</p>
</details>@endif</div></div>
@endsection
@push('scripts')
@php($previousInput = array_replace($ripresa, \Illuminate\Support\Arr::only(session()->getOldInput(), ['nome','oggetto','corpo','canale','impostazioni','modello_id'])))
@php($layoutDefaults = array_replace(\App\Services\WebCheckinMessaggio::layoutIniziale(), filled($richiesta->messaggio_ospite) ? ['testo'=>$richiesta->messaggio_ospite] : []))
<script>
document.addEventListener('DOMContentLoaded', () => {
const defaults=@json($layoutDefaults), previous=@json($previousInput), select=document.getElementById('invito-modello');if(!select)return;
const breve=@json($testoBreve), oggettoDefault=@json($oggettoDefault);

const dataLabels={"struttura":"Nome struttura","referente":"Nome cliente","prenotazione":"Numero prenotazione","arrivo":"Data di arrivo","partenza":"Data di partenza","persone":"Numero ospiti","link":"Link web check-in","scadenza":"Scadenza del link","email_struttura":"Email struttura","telefono_struttura":"Telefono struttura","messaggio_ospite":"Messaggio per l’ospite"};
const friendly=value=>String(value??'').replace(/\{\{\s*([a-z_]+)\s*\}\}/g,(match,key)=>dataLabels[key]?'['+dataLabels[key]+']':match);
const technical=value=>String(value??'').replace(/\[([^\]]+)\]/g,(match,label)=>{const key=Object.keys(dataLabels).find(key=>dataLabels[key]===label);return key?'{{'+key+'}}':match;});
const editorFields=()=>[document.getElementById('invito-oggetto'),document.getElementById('invito-corpo'),...document.querySelectorAll('#invito-form textarea[name^="impostazioni["]')];
const showFriendly=()=>editorFields().forEach(field=>{field.value=friendly(field.value);});
const editorForm=document.getElementById('invito-form');
editorForm.addEventListener('formdata',event=>{for(const field of editorFields())event.formData.set(field.name,technical(field.value));});
let activeEditor=document.getElementById('invito-oggetto');
editorFields().forEach(field=>field.addEventListener('focus',()=>{activeEditor=field;}));
for(const label of Object.values(dataLabels)){const button=document.createElement('button');button.type='button';button.className='btn btn-sm btn-light';button.textContent=label;button.addEventListener('click',()=>{activeEditor.setRangeText('['+label+']',activeEditor.selectionStart,activeEditor.selectionEnd,'end');activeEditor.focus();activeEditor.dispatchEvent(new Event('input',{bubbles:true}));});document.getElementById('web-dati-messaggio').append(button);}

const load=()=>{const o=select.selectedOptions[0];for(const key of ['nome','oggetto','corpo'])document.getElementById('invito-'+key).value=o?.dataset[key]??({nome:'',oggetto:oggettoDefault,corpo:breve}[key]);document.getElementById('modello-versione').value=o?.dataset.versione??1;const layout=o?.dataset.impostazioni?JSON.parse(o.dataset.impostazioni):defaults;for(const key of Object.keys(defaults))document.getElementById('email-'+key).value=layout[key]??defaults[key];showFriendly();};
if(previous.modello_id)select.value=previous.modello_id;select.addEventListener('change',load);load();document.getElementById('whatsapp-breve').addEventListener('click',()=>{document.getElementById('invito-corpo').value=friendly(breve);document.getElementById('invito-canale').value='whatsapp';document.getElementById('invito-canale').dispatchEvent(new Event('change'));});
document.querySelectorAll('#invito-form button[name="azione"]').forEach(b=>b.addEventListener('click',()=>document.getElementById('modello-azione').value=b.value));
for(const key of ['nome','oggetto','corpo','canale'])if(Object.hasOwn(previous,key))document.getElementById('invito-'+key).value=previous[key]??'';
for(const[key,value]of Object.entries(previous.impostazioni??{}))if(Object.hasOwn(defaults,key))document.getElementById('email-'+key).value=value;
showFriendly();document.getElementById('invito-canale').dispatchEvent(new Event('change'));document.getElementById('web-email-fields').disabled=false;document.getElementById('invito-form').removeAttribute('aria-busy');
});
</script>
@endpush
