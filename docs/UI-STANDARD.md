# Standard UI centralizzato (Laravel + Vite + Velzon Minimal + Bootstrap 5)

## Regole generali
- Non inizializzare plugin nelle viste o in script inline: è vietato richiamare select2/DataTable/flatpickr a mano.
- Usa solo `data-ui` e `window.UI.init(root)` per attivare i componenti.
- Una sola istanza per componente: `initOnce` impedisce doppie init; in DEV logghiamo errori/warning se usi i plugin direttamente.
- Qualsiasi partial, modal, livewire/turbo va reinizializzato con `window.UI.init(root)`.

## Attributi data-ui
- Select2: `<select data-ui="select2" data-placeholder="Seleziona..."></select>`
- DataTable: `<table data-ui="datatable">...</table>`
- Datepicker: `<input data-ui="datepicker" data-format="d/m/Y">`
- GeoSelect: `<div data-ui="geo-select" data-nation="#nazione" data-region="#regione" data-province="#provincia" data-city="#citta" data-cap="#cap"></div>`
- Filtro tipologie: `<div data-ui="tipologie-filtro" data-select-generale="#gen" data-select-struttura="#str" data-select-classificazione="#cls"></div>`

## Inizializzazione
- Punto unico: `window.UI.init(root)` definito in `resources/js/core/init.js`.
- Chiamato su DOMContentLoaded, su `shown.bs.modal`, e dopo update Livewire.

## Componenti registrati
- select2
- datepicker
- datatable
- geo-select
- tipologie-filtro (logica centralizzata)

## Unico GeoSelect
- Usa il componente in `reference/libreria/geo/GeoSelect.js` tramite il wrapper `initGeoSelect`.
- Endpoints attesi: `/geo/*` (aggiornare se diverso).

## Come aggiungere un nuovo partial/vista
1. Marca gli elementi con `data-ui` appropriato.
2. Assicurati che i select/inputs/tabella esistano nel DOM prima di chiamare `window.UI.init(root)`.
3. Su caricamenti dinamici (AJAX, Livewire, Turbo, modali) richiama `window.UI.init` passando il nodo root appena inserito.
4. Per il filtro Tipologia Generale -> Tipologia Struttura -> Classificazione, usa il componente Blade riutilizzabile `<x-xx.struttura-tipologia ...>` (compatibile anche con `<x-struttura.filtro-tipologie ...>`).

## Anti-conflitti
- In DEV, inizializzare select2 o DataTable senza `data-ui` logga un warning e stack (guard in app.js).
- `initOnce` usa `data-init-*` per impedire doppie inizializzazioni.
- Evita script inline che instanziano plugin: usa solo i wrapper registrati.

## Componenti Blade obbligatori
- Usa solo i componenti Blade ufficiali per i tre pilastri UI: `<x-ui.select>`, `<x-ui.datepicker>`, `<x-ui.datatable>`.
- Niente HTML manuale e niente init manuali: i wrapper JS leggono i `data-*` e mantengono le configurazioni globali.

### Esempi
**Select2**
```blade
<x-ui.select name="stato" :value="old('stato', $model->stato)" placeholder="Seleziona stato" allow-clear="1">
	<option value="">--</option>
	@foreach($stati as $stato)
		<option value="{{ $stato->id }}" @selected(old('stato', $model->stato) == $stato->id)>{{ $stato->nome }}</option>
	@endforeach
</x-ui.select>
```

**Datepicker (Flatpickr)**
```blade
<x-ui.datepicker
	name="data_apertura"
	:value="old('data_apertura', $struttura->data_apertura)"
	format="d/m/Y"
	enable-time="1"
	time-24hr="1"
	min-date="2024-01-01"
	class="form-control form-control-sm"
/>
```

**DataTables**
```blade
<x-ui.datatable id="utenti-table" page-length="25" order="0,asc">
	<x-slot:head>
		<tr><th>Nome</th><th>Email</th></tr>
	</x-slot:head>
	<x-slot:body>
		@foreach($users as $user)
			<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td></tr>
		@endforeach
	</x-slot:body>
</x-ui.datatable>
```

### Regole rapide
- Non inizializzare mai i plugin direttamente; i guard DEV lo segnaleranno.
- Configurazioni personalizzate vanno passate solo via `data-*` sugli elementi resi dai componenti Blade.
- Nessuna CDN: asset solo locali (build/Vite) e override SCSS dedicati.
