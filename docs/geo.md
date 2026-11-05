# Prodotto GEO

## Principi
- Unica fonte dati: tabelle `geo_*` (nazioni, regioni, province, comuni, cap, pivot comuni_cap).
- I JSON ISTAT/Questura (`gi_*.json`) servono solo per import/seed (mai runtime).
- Relazioni gerarchiche: `geo_nazioni -> geo_regioni -> geo_province -> geo_comuni` con FK.
- CAP molti-a-molti tramite `geo_cap` + pivot `geo_comuni_cap`.
- CAP default deterministico: `principale` desc, `priorita` asc, `cap` asc.
- UI unica: componente Blade `<x-geo.italia />` con JS `data-ui="geo-italia"` inizializzato da `window.UI.init`.
- Nessun CDN; tutto via asset locali e API `/api/geo/*`.

## Import
- Comando: `php artisan geo:import` (`--fresh` per truncate+reimport con conferma).
- Service: `App\Services\GeoImportService` legge i file `gi_*.json` da `storage/app` o `reference/libreria/geo`.
- Upsert su tutte le tabelle; `is_italia` settato per ISO2 IT/Italia.

## API
- Endpoints REST `/api/geo/*` (nazioni, regioni, province, comuni, cap, resolve) alimentano Select2 e reverse autocomplete.
- `/geo/resolve` completa la catena da `cap`, `codice_istat`, `geo_comune_id` o `sigla_provincia` e ordina i CAP con le regole di default.

## UI
- Blade: `resources/views/components/geo/italia.blade.php`, wrapper con `data-ui="geo-italia"` e campi Nazione/Regione/Provincia/Comune/CAP (Select2) + modalità estero manuale.
- JS: `resources/js/components/geo/geo-italia.js`, registrato via `initGeoSelect` (modulo deprecato "geo-select" solo per warning).
- In DEV l'uso di componenti legacy `geo-italia` o `data-ui="geo-select"` genera errore/console error.

## Migrazione viste
- Sostituire ogni componente precedente con `<x-geo.italia ...>`.
- In DEV il vecchio Blade `resources/views/components/geo-italia.blade.php` lancia un'eccezione per impedire l'uso.

## Note
- `CAP` sempre modificabile (Select2 con `tags`), ma si propone il `cap_default` ordinato.
- Se nazione != Italia, la UI passa in modalità manuale (input testo) disabilitando i select guidati.
- Inizializzazione esclusiva con `window.UI.init(root)`; nessun init manuale nelle viste.
