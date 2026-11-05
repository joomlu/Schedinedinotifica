@extends('layouts.master-without-nav')
@section('title', 'Web Check-in · '.$identitaHotel['nome'])
@php
$palette = \App\Services\WebCheckinIdentita::palette($identitaHotel['colore']);
@endphp
@section('body')<body class="web-checkin-public" style="--hotel-colore:{{ $identitaHotel['colore'] }};--hotel-testo:{{ $identitaHotel['testo_colore'] }};--hotel-sfondo:{{ $palette['sfondo'] }};--hotel-tenue:{{ $palette['tenue'] }};--hotel-bordo:{{ $palette['bordo'] }};--hotel-inchiostro:{{ $palette['inchiostro'] }}">@endsection
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
@if($webPreview ?? false)<div class="alert alert-info">Anteprima interattiva: le modifiche non vengono salvate <a href="{{ route('web_checkin.edit', $webCheckinRichiesta->id) }}">Torna alla richiesta</a></div>@endif
@if(($webPreview ?? false) && request()->boolean('dimostrazione'))<div class="alert alert-info" role="status">Modalità dimostrazione. Nessun dato è stato salvato e nessun check-in è stato eseguito.</div>@endif
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
<fieldset class="border-0 p-0 m-0">
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
body{background:var(--hotel-sfondo)}#web-ospite .componente-row[hidden]{display:none!important}#web-ospite.web-guidato .tab-content .tab-pane[data-web-tassa-riservata]{display:none!important}#web-ospite .hotel-logo{max-height:90px;max-width:220px;object-fit:contain}#web-ospite .hotel-nome{color:#263238;border-left:4px solid var(--hotel-colore);padding-left:12px;font-weight:700}#web-ospite .hotel-riepilogo{display:flex;flex-wrap:wrap;gap:20px;background:var(--hotel-tenue);padding:20px;border:1px solid var(--hotel-bordo);border-radius:10px}#web-ospite .hotel-riepilogo>span{flex:1;min-width:110px}#web-ospite .btn-primary,#web-ospite .btn-success{background:var(--hotel-colore);border-color:var(--hotel-colore);color:var(--hotel-testo)}#web-ospite.web-guidato .step-arrow-nav,#web-ospite.web-guidato .tab-content .nexttab,#web-ospite.web-guidato .tab-content .prevtab,#web-ospite.web-guidato .tab-content button[name=save_mode]{display:none!important}#web-ospite.web-guidato .tab-pane[hidden]{display:none!important}#web-ospite.web-guidato .tab-pane:not([hidden]){display:block!important;opacity:1}#web-ospite .web-azioni{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:18px 0 0;border-top:1px solid var(--hotel-bordo);margin-top:24px}@media(max-width:575px){#web-ospite .web-azioni .btn{flex:1 1 100%}#web-ospite .hotel-riepilogo{gap:12px;padding:14px}}

#web-ospite .componente-row > .card-header{flex-wrap:wrap;gap:8px}
#web-ospite .componente-row > .card-header > .d-flex{min-width:0;max-width:100%;flex-wrap:wrap}
#web-ospite .componente-summary-line{overflow-wrap:anywhere}
#web-ospite [data-ui=geo-italia] > .card-header{flex-wrap:wrap;gap:8px}
#web-ospite [data-ui=geo-italia] > .card-header > .d-flex{min-width:0;max-width:100%;flex-wrap:wrap}
#web-ospite [data-ui=geo-italia] > .card-header .btn{min-height:44px}
#web-ospite .web-sezioni{display:flex;flex-wrap:wrap;gap:8px;padding:8px;background:var(--hotel-tenue);border:1px solid var(--hotel-bordo);border-radius:12px}
#web-ospite .web-sezioni .btn{flex:1 1 145px;white-space:normal;min-height:46px;border-radius:8px}
#web-ospite [hidden],#web-passaggi[hidden]{display:none!important}
#web-ospite .web-sezioni .btn:focus-visible{outline:3px solid #263238;outline-offset:2px}
#web-ospite .web-azioni #web-conferma{margin-left:auto}
@media(max-width:575px){#web-ospite .web-sezioni .btn{flex:1 1 calc(50% - 8px)}#web-ospite .web-azioni #web-conferma{margin-left:0}}
/* La palette resta confinata al modulo ospite, senza cambiare i colori di errore/avviso. */
#web-ospite{--vz-primary:var(--hotel-colore);--vz-success:var(--hotel-colore);--vz-link-color:var(--hotel-inchiostro)}
#web-ospite a,#web-ospite .text-primary,#web-ospite .text-success{color:var(--hotel-inchiostro)!important}
#web-ospite .btn-light{background:var(--hotel-tenue);border-color:var(--hotel-bordo);color:var(--hotel-inchiostro)}
#web-ospite .btn-primary,#web-ospite .btn-success,#web-ospite .btn-primary:focus,#web-ospite .btn-success:focus{box-shadow:0 4px 12px var(--hotel-bordo)}
#web-ospite .btn-primary:hover,#web-ospite .btn-success:hover{background:var(--hotel-colore);border-color:var(--hotel-colore);color:var(--hotel-testo);filter:brightness(.95)}
#web-ospite .border-start{border-color:var(--hotel-bordo)!important}
#web-ospite .card-header,#web-ospite .bg-light-subtle{background:var(--hotel-tenue)!important;border-color:var(--hotel-bordo)!important}
#web-ospite .card{border-color:var(--hotel-bordo)}
#web-ospite .form-control,#web-ospite .form-select,#web-ospite .select2-selection{border-color:var(--hotel-inchiostro)}
#web-ospite .btn-soft-primary,#web-ospite .btn-soft-success{background:var(--hotel-tenue);border-color:var(--hotel-bordo);color:var(--hotel-inchiostro)}
#web-ospite .alert-info{background:var(--hotel-tenue);border-color:var(--hotel-bordo);color:var(--hotel-inchiostro)}
#web-ospite .form-control:focus,#web-ospite .form-select:focus,#web-ospite .select2-container--focus .select2-selection{border-color:var(--hotel-colore);box-shadow:0 0 0 3px var(--hotel-tenue)}
#web-ospite .form-check-input:checked{background-color:var(--hotel-colore);border-color:var(--hotel-colore)}
#web-ospite :focus-visible{outline:2px solid var(--hotel-inchiostro);outline-offset:2px}
body.web-checkin-public .flatpickr-day.selected,body.web-checkin-public .flatpickr-day.startRange,body.web-checkin-public .flatpickr-day.endRange{background:var(--hotel-colore);border-color:var(--hotel-colore);color:var(--hotel-testo)}
body.web-checkin-public .flatpickr-day.today{border-color:var(--hotel-inchiostro);background:var(--hotel-tenue);color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-day.selected{border-color:var(--hotel-inchiostro)}
#web-ospite .btn-outline-primary,#web-ospite .btn-outline-secondary{--vz-btn-color:var(--hotel-inchiostro);--vz-btn-border-color:var(--hotel-bordo);--vz-btn-hover-color:var(--hotel-testo);--vz-btn-hover-bg:var(--hotel-colore);--vz-btn-hover-border-color:var(--hotel-colore);--vz-btn-active-color:var(--hotel-testo);--vz-btn-active-bg:var(--hotel-colore);--vz-btn-active-border-color:var(--hotel-colore)}
#web-ospite .form-control.is-invalid,#web-ospite .form-select.is-invalid,#web-ospite .select2-selection.is-invalid{border-color:var(--vz-danger)}
body.web-checkin-public .select2-container--default .select2-results__option--highlighted[aria-selected],body.web-checkin-public .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{background:var(--hotel-colore);color:var(--hotel-testo)}
body.web-checkin-public .select2-dropdown{border-color:var(--hotel-bordo)}
body.web-checkin-public .flatpickr-current-month .flatpickr-monthDropdown-months,body.web-checkin-public .flatpickr-months .flatpickr-month,body.web-checkin-public .flatpickr-weekdays,body.web-checkin-public span.flatpickr-weekday{background:var(--hotel-tenue);color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-months .flatpickr-prev-month,body.web-checkin-public .flatpickr-months .flatpickr-next-month{color:var(--hotel-inchiostro);fill:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-day:not(.flatpickr-disabled):hover,body.web-checkin-public .flatpickr-day:not(.flatpickr-disabled):focus{background:var(--hotel-tenue);border-color:var(--hotel-bordo);color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-day.selected:hover,body.web-checkin-public .flatpickr-day.selected:focus{background:var(--hotel-colore);border-color:var(--hotel-inchiostro);color:var(--hotel-testo)}
body.web-checkin-public .flatpickr-day:focus-visible{outline:2px solid var(--hotel-inchiostro);outline-offset:-2px}
body.web-checkin-public .flatpickr-months{background:var(--hotel-tenue)}
body.web-checkin-public .flatpickr-current-month input.cur-year,body.web-checkin-public .flatpickr-current-month span.cur-month,body.web-checkin-public .flatpickr-current-month .flatpickr-monthDropdown-months{color:var(--hotel-inchiostro);-webkit-text-fill-color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-current-month .arrowUp::after{border-bottom-color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-current-month .arrowDown::after{border-top-color:var(--hotel-inchiostro)}
body.web-checkin-public .flatpickr-calendar.arrowTop::before,body.web-checkin-public .flatpickr-calendar.arrowTop::after{border-bottom-color:var(--hotel-tenue)}
body.web-checkin-public .flatpickr-calendar.arrowBottom::before,body.web-checkin-public .flatpickr-calendar.arrowBottom::after{border-top-color:var(--hotel-bordo)}
body.web-checkin-public .flatpickr-day.inRange{background:var(--hotel-tenue);border-color:var(--hotel-tenue);box-shadow:-5px 0 0 var(--hotel-tenue),5px 0 0 var(--hotel-tenue)}
body.web-checkin-public .select2-container--default .select2-results__option[aria-selected=true]:hover,body.web-checkin-public .select2-selection__choice{background:var(--hotel-colore);color:var(--hotel-testo)}


</style>
@endsection
@section('script')
@include('schedina.partials.scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('web-ospite');
    const form = document.getElementById('schedina-form');
    if (!form) return;
    const preview = @json($webPreview ?? false);
    if (preview) {
        const demo = document.createElement('div');
        demo.id = 'web-demo-esito'; demo.className = 'alert alert-info mt-3';
        demo.setAttribute('role', 'status'); demo.setAttribute('tabindex', '-1'); demo.hidden = true;
        form.after(demo);
        const explain = event => {
            event.preventDefault(); event.stopImmediatePropagation();
            demo.textContent = 'Modalità dimostrazione. Nessun dato è stato salvato e nessun check-in è stato eseguito. Nel modulo ospite questi pulsanti salvano i dati parziali o li confermano per la reception.';
            demo.hidden = false; demo.focus();
        };
        // Capture precede i gestori canonici; anche Invio da tastiera resta dimostrativo.
        form.addEventListener('submit', explain, true);
        form.addEventListener('click', event => {
            if (event.target.closest('button[type=submit], input[type=submit]')) explain(event);
        }, true);
    }
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
        const matches = [...form.elements].filter(element => element.name === name);
        const field = matches.find(element => element.dataset.role?.startsWith('manual-') && element.closest('[data-ui=geo-italia]')?.querySelector('[data-role=manual-flag]')?.value === '1') ?? matches.find(element => !element.disabled) ?? matches[0];
        if (!field) continue;
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        const selectContainer = field.nextElementSibling?.classList.contains('select2') ? field.nextElementSibling : null;
        const visible = selectContainer?.querySelector('.select2-selection') ?? field;
        visible.classList.add('is-invalid'); visible.setAttribute('aria-invalid', 'true');
        requestAnimationFrame(() => { if (field.tagName === 'SELECT') { const selection = field.parentElement.querySelector('.select2-selection'); selection?.classList.add('is-invalid'); selection?.setAttribute('aria-invalid', 'true'); } });
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = errors.join(' ');
        (selectContainer ?? field).insertAdjacentElement('afterend', feedback);
        const details = field.closest('details');
        if (details) details.open = true;
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
