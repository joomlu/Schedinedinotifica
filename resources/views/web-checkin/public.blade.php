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
<div class="hotel-nome">{{ $identitaHotel['nome'] }}</div><h1 class="h3 mt-2">Benvenuto, {{ $webCheckinRichiesta->nome_referente }}</h1>
<p class="text-muted">Compila la scheda una sezione alla volta. Puoi salvare anche se mancano dei dati: la reception potrà completarli con te.</p>
@if(filled($webCheckinRichiesta->messaggio_ospite))<p class="border-start border-3 ps-3" style="white-space:pre-line">{{ $webCheckinRichiesta->messaggio_ospite }}</p>@endif
<div class="hotel-riepilogo"><span><strong>Prenotazione</strong><br>{{ $webCheckinRichiesta->numero_prenotazione }}</span><span><strong>Arrivo</strong><br>{{ $webCheckinRichiesta->arrivo->format('d/m/Y') }}</span><span><strong>Partenza</strong><br>{{ $webCheckinRichiesta->partenza->format('d/m/Y') }}</span><span><strong>Ospiti</strong><br>{{ $webCheckinRichiesta->quantita_persone }}@if($webCheckinRichiesta->numero_adulti!==null)<small class="d-block">{{ $webCheckinRichiesta->numero_adulti }} adulti · {{ $webCheckinRichiesta->numero_bambini }} bambini</small>@endif</span></div>
<p class="small text-muted mt-3">Referente della prenotazione: {{ $webCheckinRichiesta->nome_referente }}. I dati sotto appartengono all’ospite titolare, che può essere una persona diversa.</p>
</header>
@if($webPreview ?? false)<div class="alert alert-info">Anteprima riservata alla reception: nessuna visita dell’ospite registrata e nessun salvataggio disponibile. <a href="{{ route('web_checkin.edit', $webCheckinRichiesta->id) }}">Torna alla richiesta</a></div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">Controlla i campi segnalati. I dati inseriti sono ancora nel modulo.</div>@endif
<p id="ospiti-completati" class="small">Ultimo salvataggio: <strong>Ospiti completati {{ $completedGuests }} di {{ $webCheckinRichiesta->quantita_persone }}</strong>. Il conteggio si aggiorna al salvataggio; la conferma rivalida tutti i dati.</p>
@if($campiPendentiOspite)<details class="mb-4"><summary>Campi da completare prima della conferma</summary><ul class="mt-2">@foreach(collect($campiPendentiOspite)->flatten()->unique() as $pending)<li>{{ $pending }}</li>@endforeach</ul></details>@endif
<nav id="web-passaggi" hidden aria-label="Sezioni della scheda ospite" class="mb-4">
<div class="web-sezioni" role="tablist" aria-label="Dati del soggiorno e degli ospiti">
@foreach(['base'=>'Soggiorno e ospite', 'anag'=>'Anagrafica', 'res'=>'Residenza e documento', 'contact'=>'Contatti e consensi', 'comp'=>'Componenti', 'tassa'=>'Tassa di soggiorno'] as $section => $label)
<button type="button" role="tab" id="web-tab-{{ $section }}" aria-controls="schedina-step-{{ $section }}-pane" aria-selected="false" tabindex="-1" class="btn btn-light" data-web-section="{{ $section }}">{{ $label }}</button>
@endforeach
</div></nav>
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
body{background:#f3f5f4}#web-ospite .componente-row[hidden]{display:none!important}#web-ospite.web-guidato .tab-content .tab-pane[data-web-tassa-riservata]{display:none!important}#web-ospite .hotel-logo{max-height:90px;max-width:220px;object-fit:contain}#web-ospite .hotel-nome{color:#263238;border-left:4px solid var(--hotel-colore);padding-left:12px;font-weight:700}#web-ospite .hotel-riepilogo{display:flex;flex-wrap:wrap;gap:20px;background:#f4f7f7;padding:20px;border:1px solid #dce5e4;border-radius:10px}#web-ospite .hotel-riepilogo>span{flex:1;min-width:110px}#web-ospite .btn-primary,#web-ospite .btn-success{background:var(--hotel-colore);border-color:var(--hotel-colore);color:var(--hotel-testo)}#web-ospite.web-guidato .step-arrow-nav,#web-ospite.web-guidato .tab-content .nexttab,#web-ospite.web-guidato .tab-content .prevtab,#web-ospite.web-guidato .tab-content button[name=save_mode]{display:none!important}#web-ospite.web-guidato .tab-pane[hidden]{display:none!important}#web-ospite.web-guidato .tab-pane:not([hidden]){display:block!important;opacity:1}#web-ospite .web-azioni{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:18px 0 0;border-top:1px solid #dce5e4;margin-top:24px}@media(max-width:575px){#web-ospite .web-azioni .btn{flex:1 1 100%}#web-ospite .hotel-riepilogo{gap:12px;padding:14px}}

#web-ospite .web-sezioni{display:flex;flex-wrap:wrap;gap:8px;padding:8px;background:#f4f7f7;border:1px solid #dce5e4;border-radius:12px}
#web-ospite .web-sezioni .btn{flex:1 1 145px;white-space:normal;min-height:46px;border-radius:8px}
#web-ospite [hidden],#web-passaggi[hidden]{display:none!important}
#web-ospite .web-sezioni .btn:focus-visible{outline:3px solid #263238;outline-offset:2px}
#web-ospite .web-azioni #web-conferma{margin-left:auto}
@media(max-width:575px){#web-ospite .web-sezioni .btn{flex:1 1 calc(50% - 8px)}#web-ospite .web-azioni #web-conferma{margin-left:0}}
</style>
@endsection
@section('script')
@include('schedina.partials.scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('web-ospite');
    const form = document.getElementById('schedina-form');
    if (!form) return;
    const messages = @json($publicMessages);
    const hasOthers = @json($hasOthers);
    const panes = [...form.querySelectorAll('.tab-content > .tab-pane')];
    const tabs = [...root.querySelectorAll('[data-web-section]')];
    const available = tabs.filter(button => {
        const section = button.dataset.webSection;
        const pane = document.getElementById(button.getAttribute('aria-controls'));
        const visible = pane && !pane.hasAttribute('data-web-tassa-riservata') && (section !== 'comp' || hasOthers);
        button.hidden = !visible;
        return visible;
    });
    const partial = form.querySelector('button[name=save_mode][value=web]');
    const complete = form.querySelector('button[name=save_mode][value=web_complete]');
    if (!partial || !complete || !available.length) return;

    const controls = document.createElement('div');
    controls.className = 'web-azioni';
    controls.innerHTML = '<button type="button" class="btn btn-light" id="web-indietro">Indietro</button><button type="button" class="btn btn-light" id="web-continua">Continua</button>';
    for (const button of [partial, complete]) button.setAttribute('data-confirm-ignore', '');
    partial.textContent = 'Salva e continua più tardi';
    complete.textContent = 'Conferma dati';
    complete.id = 'web-conferma';
    controls.append(partial, complete);
    form.append(controls);
    const back = document.getElementById('web-indietro');
    const next = document.getElementById('web-continua');
    let current = 0;
    const show = (index, focus = false) => {
        current = Math.max(0, Math.min(index, available.length - 1));
        const selected = available[current];
        for (const pane of panes) {
            pane.hidden = pane.id !== selected.getAttribute('aria-controls');
            pane.classList.toggle('show', !pane.hidden);
            pane.classList.toggle('active', !pane.hidden);
        }
        for (const button of tabs) {
            const active = button === selected;
            button.setAttribute('aria-selected', String(active));
            button.tabIndex = active ? 0 : -1;
            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-light', !active);
            const pane = document.getElementById(button.getAttribute('aria-controls'));
            if (pane) pane.setAttribute('aria-labelledby', button.id);
        }
        back.hidden = current === 0;
        next.hidden = current === available.length - 1;
        const activeTab = form.querySelector('#active-tab');
        if (activeTab) activeTab.value = 'schedina-step-' + selected.dataset.webSection;
        if (focus) selected.focus();
    };
    available.forEach((button, index) => {
        button.addEventListener('click', () => show(index));
        button.addEventListener('keydown', event => {
            const target = event.key === 'Home' ? 0 : event.key === 'End' ? available.length - 1
                : event.key === 'ArrowRight' ? (index + 1) % available.length
                : event.key === 'ArrowLeft' ? (index + available.length - 1) % available.length : null;
            if (target === null) return;
            event.preventDefault();
            show(target, true);
        });
    });
    next.addEventListener('click', () => show(current + 1, true));
    back.addEventListener('click', () => show(current - 1, true));
    // I gestori canonici di salvataggio, GEO, consensi e componenti restano invariati.
    form.querySelectorAll('.step-arrow-nav [data-bs-toggle]').forEach(button => {
        button.addEventListener('shown.bs.tab', () => {
            const index = available.findIndex(tab => tab.getAttribute('aria-controls') === (button.dataset.bsTarget || '').replace(/^#/, ''));
            if (index >= 0) show(index);
        });
    });
    for (const name of ['room', 'beds']) {
        const field = form.querySelector('[name="' + name + '"]');
        if (field) {
            field.removeAttribute('required');
            const column = field.closest('.col-lg-3');
            if (column) column.hidden = true;
        }
    }
    const quantity = form.querySelector('[name=cant_people]');
    if (quantity) {
        quantity.readOnly = true;
        quantity.value = @json((int)$webCheckinRichiesta->quantita_persone);
    }

    // La riga vuota resta un modello, mai un accompagnatore da inviare.
    const add = document.getElementById('add-componente-row');
    const container = document.getElementById('componenti-container');
    const limit = @json(max(0,(int)$webCheckinRichiesta->quantita_persone-1));
    const rows = () => [...form.querySelectorAll('.componente-row:not(.componente-empty-row)')];
    const refreshComponents = () => {
        form.querySelectorAll('.componente-empty-row').forEach(row => {
            if (row.querySelector('[name$="[id]"]')?.value) row.classList.remove('componente-empty-row', 'd-none');
        });
        if (add) {
            add.hidden = rows().length >= limit;
            // Dopo la rimozione il modello viene nascosto: riallinea anche il pulsante.
            const openDetails = rows().some(row => !row.classList.contains('d-none')
                && !row.querySelector('.componente-details')?.classList.contains('d-none'));
            add.classList.toggle('d-none', add.hidden || openDetails);
        }
    };
    if (add && container) {
        add.textContent = 'Aggiungi componente';
        add.addEventListener('click', event => {
            if (rows().length >= limit) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        add.addEventListener('click', () => queueMicrotask(() => {
            const added = [...container.querySelectorAll('.componente-row')].at(-1);
            if (added && !added.classList.contains('d-none')) added.classList.remove('componente-empty-row');
            refreshComponents();
        }));
        container.addEventListener('click', event => {
            if (!event.target.closest('.remove-componente')) return;
            queueMicrotask(() => {
                const remaining = [...container.querySelectorAll('.componente-row')];
                const row = remaining[0];
                if (remaining.length === 1 && !['id', 'name', 'surname'].some(name => row.querySelector('[name$="[' + name + ']"]')?.value)) {
                    row.classList.add('d-none', 'componente-empty-row');
                    row.querySelector('.componente-details')?.classList.add('d-none');
                }
                refreshComponents();
            });
        });
        new MutationObserver(refreshComponents).observe(container, {childList: true});
    }
    form.addEventListener('submit', () => {
        form.querySelectorAll('.componente-empty-row.d-none input,.componente-empty-row.d-none select,.componente-empty-row.d-none textarea').forEach(field => { field.disabled = true; });
    }, true);
    refreshComponents();

    let firstError = null;
    for (const [key, errors] of Object.entries(messages)) {
        const name = key.replace(/\.(\w+)/g, '[$1]');
        const field = [...form.elements].find(element => element.name === name);
        if (!field) continue;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = errors.join(' ');
        field.insertAdjacentElement('afterend', feedback);
        firstError ??= field;
    }
    if (firstError) {
        const row = firstError.closest('.componente-row');
        if (row) {
            row.classList.remove('componente-empty-row', 'd-none');
            row.querySelector('.componente-details')?.classList.remove('d-none');
        }
    }
    const errorPane = firstError?.closest('.tab-pane');
    const errorIndex = available.findIndex(button => button.getAttribute('aria-controls') === errorPane?.id);
    document.getElementById('web-passaggi').hidden = false;
    root.classList.add('web-guidato');
    show(errorIndex >= 0 ? errorIndex : 0);
});
</script>
@endsection
