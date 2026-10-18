@extends('layouts.master-without-nav')
@section('title', 'Web Check-in · '.$identitaHotel['nome'])
@section('body')<body>@endsection
@section('content')
@php
$hasOthers = (int)$webCheckinRichiesta->quantita_persone > 1 || $componenti->isNotEmpty();
$headErrors = collect(array_keys($campiPendentiOspite))->contains(fn($key) => !str_starts_with($key, 'componenti') && !in_array($key, ['cant_people', 'room', 'beds']));
$completedGuests = $headErrors ? 0 : 1;
foreach($componenti->values() as $i => $guest) {
    if(!collect(array_keys($campiPendentiOspite))->contains(fn($key) => str_starts_with($key, 'componenti.'.$i.'.'))) $completedGuests++;
}
$completedGuests = min($completedGuests, (int)$webCheckinRichiesta->quantita_persone);
$publicMessages = $errors->getMessages();
@endphp
<main id="web-ospite" class="container py-4 py-md-5" style="max-width:1060px;--hotel-colore:{{ $identitaHotel['colore'] }};--hotel-testo:{{ $identitaHotel['testo_colore'] }}">
<div class="card border-0 shadow-sm"><div class="card-body p-3 p-md-5">
<header class="mb-4">
@if($identitaHotel['logo_url'])<img class="hotel-logo mb-3" src="{{ $identitaHotel['logo_url'] }}" width="{{ $identitaHotel['logo_width'] }}" height="{{ $identitaHotel['logo_height'] }}" alt="{{ $identitaHotel['nome'] }}">@endif
<div class="hotel-nome">{{ $identitaHotel['nome'] }}</div><h1 class="h3 mt-2">Benvenuto, prepariamo il tuo arrivo</h1>
<p class="text-muted">Compila i dati degli ospiti prima dell’arrivo. Puoi salvare e continuare più tardi.</p>
<div class="hotel-riepilogo"><span><strong>Prenotazione</strong><br>{{ $webCheckinRichiesta->numero_prenotazione }}</span><span><strong>Arrivo</strong><br>{{ $webCheckinRichiesta->arrivo->format('d/m/Y') }}</span><span><strong>Partenza</strong><br>{{ $webCheckinRichiesta->partenza->format('d/m/Y') }}</span><span><strong>Ospiti</strong><br>{{ $webCheckinRichiesta->quantita_persone }}</span></div>
<p class="small text-muted mt-3">Referente della prenotazione: {{ $webCheckinRichiesta->nome_referente }}. I dati sotto appartengono all’ospite titolare, che può essere una persona diversa.</p>
</header>
@if($webPreview ?? false)<div class="alert alert-info">Anteprima riservata alla reception: nessuna visita dell’ospite registrata e nessun salvataggio disponibile. <a href="{{ route('web_checkin.edit', $webCheckinRichiesta->id) }}">Torna alla richiesta</a></div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">Controlla i campi segnalati. I dati inseriti sono ancora nel modulo.</div>@endif
<p id="ospiti-completati" class="small">Ultimo salvataggio: <strong>Ospiti completati {{ $completedGuests }} di {{ $webCheckinRichiesta->quantita_persone }}</strong>. Il conteggio si aggiorna al salvataggio; la conferma rivalida tutti i dati.</p>
@if($campiPendentiOspite)<details class="mb-4"><summary>Campi da completare prima della conferma</summary><ul class="mt-2">@foreach(collect($campiPendentiOspite)->flatten()->unique() as $pending)<li>{{ $pending }}</li>@endforeach</ul></details>@endif
<nav id="web-passaggi" hidden aria-label="Passaggi del Web Check-in" class="d-flex flex-wrap gap-2 mb-4"><button type="button" class="btn btn-light" data-web-step="0">1. I tuoi dati</button>@if($hasOthers)<button type="button" class="btn btn-light" data-web-step="1">2. Altri ospiti</button>@endif<button type="button" class="btn btn-light" data-web-step="2">{{ $hasOthers ? 3 : 2 }}. Riepilogo e conferma</button></nav>
<div id="web-riepilogo" hidden class="hotel-riepilogo mb-3" aria-live="polite"><div><h2 class="h5">Riepilogo e conferma</h2><p id="web-riepilogo-testo"></p><p>Confermando invii i dati alla reception per la verifica.</p></div></div>
<fieldset @disabled($webPreview ?? false) class="border-0 p-0 m-0">
@include('schedina.partials.form', [
'schedina'=>$schedina, 'titoli'=>$titoli, 'tipiVia'=>$tipiVia, 'tipiDocumento'=>$tipiDocumento,
'nations'=>$nations, 'regions'=>$regions, 'provinces'=>$provinces, 'ciudades'=>$ciudades,
'esenzioni'=>$esenzioni, 'strutturaInfo'=>$strutturaInfo, 'geoEndpoints'=>$geoEndpoints,
'showCircuitSaveButtons'=>false, 'showIstatFields'=>false, 'primarySaveLabel'=>'Salva e continua più tardi',
'showPrintTassa'=>false, 'geoEndpointBase'=>$geoEndpointBase, 'disableCrudConfirm'=>true,
'disableClientValidation'=>true, 'usePutMethod'=>false, 'formAction'=>$formAction, 'formTitle'=>'I dati degli ospiti'])
</fieldset>
<p class="small text-muted mt-4">Accesso disponibile fino al {{ $webCheckinRichiesta->link_expires_at?->timezone('Europe/Rome')->format('d/m/Y H:i:s T') }}. Dopo la scadenza contatta la reception: i dati restano conservati.</p>
</div></div></main>
<style>
body{background:#f3f5f4}#web-ospite.web-guidato .tab-content .tab-pane[data-web-tassa-riservata]{display:none!important}#web-ospite .hotel-logo{max-height:90px;max-width:220px;object-fit:contain}#web-ospite .hotel-nome{color:#263238;border-left:4px solid var(--hotel-colore);padding-left:12px;font-weight:700}#web-ospite .hotel-riepilogo{display:flex;flex-wrap:wrap;gap:20px;background:#f4f7f7;padding:20px;border:1px solid #dce5e4;border-radius:10px}#web-ospite .hotel-riepilogo>span{flex:1;min-width:110px}#web-ospite .btn-primary,#web-ospite .btn-success{background:var(--hotel-colore);border-color:var(--hotel-colore);color:var(--hotel-testo)}#web-ospite.web-guidato .step-arrow-nav,#web-ospite.web-guidato .tab-content .nexttab,#web-ospite.web-guidato .tab-content .prevtab,#web-ospite.web-guidato .tab-content button[name=save_mode]{display:none!important}#web-ospite.web-guidato .tab-pane[hidden]{display:none!important}#web-ospite.web-guidato .tab-pane:not([hidden]){display:block!important;opacity:1}#web-ospite .web-azioni{display:flex;flex-wrap:wrap;gap:10px;padding-top:20px;border-top:1px solid #dce5e4;margin-top:20px}@media(max-width:575px){#web-ospite .web-azioni .btn{flex:1 1 100%}#web-ospite .hotel-riepilogo{gap:12px;padding:14px}#web-ospite [data-web-step]{flex:1 1 auto;white-space:normal}}
</style>
@endsection
@section('script')
@include('schedina.partials.scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
const root=document.getElementById('web-ospite'),form=document.getElementById('schedina-form'),others=@json($hasOthers),messages=@json($publicMessages);
if(!form)return;
const panes=[...form.querySelectorAll('.tab-pane')],group=id=>id.includes('-comp-')?1:id.includes('-tassa-')?2:0,steps=others?[0,1,2]:[0,2];let current=0;
const controls=document.createElement('div');controls.className='web-azioni';
const partial=form.querySelector('button[name=save_mode][value=web]'),complete=form.querySelector('button[name=save_mode][value=web_complete]');
if(!partial||!complete)return;
controls.innerHTML='<button type="button" class="btn btn-light" id="web-indietro">Indietro</button><button type="button" class="btn btn-primary" id="web-continua">Continua</button>';
for(const button of [partial,complete])button.setAttribute('data-confirm-ignore','');
partial.textContent='Salva e continua più tardi';complete.textContent='Conferma dati';complete.id='web-conferma';controls.insertBefore(partial,controls.lastElementChild);controls.append(complete);form.append(controls);
// Riutilizza i pulsanti canonici già inizializzati, compresa la gestione dell'intento.
// La riga nascosta serve soltanto da modello UI: i suoi valori predefiniti non sono un ospite.
form.addEventListener('submit',()=>{form.querySelectorAll('.componente-empty-row.d-none input,.componente-empty-row.d-none select,.componente-empty-row.d-none textarea').forEach(field=>{field.disabled=true;});},true);
form.addEventListener('click',event=>{if(!event.target.closest('.remove-componente'))return;const rows=[...form.querySelectorAll('.componente-row')];if(rows.length!==1)return;const row=rows[0];if(['id','name','surname'].some(name=>row.querySelector(`[name$="[${name}]"]`)?.value))return;row.classList.add('d-none','componente-empty-row');row.querySelector('.componente-details')?.classList.add('d-none');document.getElementById('add-componente-row')?.classList.remove('d-none');});
const show=(step)=>{current=step;for(const p of panes){p.hidden=p.hasAttribute('data-web-tassa-riservata')||group(p.id)!==step;p.classList.toggle('show',!p.hidden);p.classList.toggle('active',!p.hidden);}root.querySelectorAll('[data-web-step]').forEach(b=>{b.setAttribute('aria-current',Number(b.dataset.webStep)===step?'step':'false');b.classList.toggle('btn-primary',Number(b.dataset.webStep)===step);});document.getElementById('web-riepilogo').hidden=step!==2;document.getElementById('web-indietro').hidden=step===0;document.getElementById('web-continua').hidden=step===2;document.getElementById('web-conferma').hidden=step!==2;const name=form.querySelector('[name=name]')?.value??'',surname=form.querySelector('[name=surname]')?.value??'';document.getElementById('web-riepilogo-testo').textContent='Ospite titolare: '+[name,surname].filter(Boolean).join(' ')+' · Verifica i dati e gli accompagnatori prima di confermare.';};
root.querySelectorAll('[data-web-step]').forEach(b=>b.addEventListener('click',()=>show(Number(b.dataset.webStep))));document.getElementById('web-continua').addEventListener('click',()=>show(steps[steps.indexOf(current)+1]??2));document.getElementById('web-indietro').addEventListener('click',()=>show(steps[steps.indexOf(current)-1]??0));
// Mantiene gli identificatori e i gestori canonici di anagrafica, GEO e componenti.
form.querySelectorAll('.step-arrow-nav [data-bs-toggle]').forEach(b=>b.addEventListener('shown.bs.tab',()=>show(group(b.dataset.bsTarget??''))));
for(const name of ['room','beds']){const field=form.querySelector(`[name="${name}"]`);if(field){field.removeAttribute('required');const col=field.closest('.col-lg-3');if(col)col.hidden=true;}}
const quantity=form.querySelector('[name=cant_people]');if(quantity){quantity.readOnly=true;quantity.value=@json((int)$webCheckinRichiesta->quantita_persone);}
const add=document.getElementById('add-componente-row');if(add)add.textContent='Aggiungi ospite';
let firstError=null;for(const[key,errors]of Object.entries(messages)){const name=key.replace(/\.(\w+)/g,'[$1]'),field=[...form.elements].find(e=>e.name===name);if(!field)continue;field.classList.add('is-invalid');field.setAttribute('aria-invalid','true');const feedback=document.createElement('div');feedback.className='invalid-feedback d-block';feedback.textContent=errors.join(' ');field.insertAdjacentElement('afterend',feedback);firstError??=field;}
document.getElementById('web-passaggi').hidden=false;root.classList.add('web-guidato');show(firstError?group(firstError.closest('.tab-pane')?.id??''):0);
});
</script>
@endsection
