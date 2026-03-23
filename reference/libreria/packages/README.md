# Pacchetti riutilizzabili: GeoSelect + AddressFields

Questo repository contiene due pacchetti separati che puoi includere in futuri progetti:

- JS Standalone: `reference/packages/js-geo-address/`
  - Per qualsiasi progetto web (anche non Laravel)
  - Esporta due classi UMD/ESM: `GeoSelect` e `AddressFields`
  - `dist/browser.js` include entrambe le classi per uso diretto in pagina

- Laravel Package: `reference/packages/laravel-geo-address/`
  - Fornisce due Blade components e gli asset JS pubblicabili
  - Include rotte di esempio facoltative (`/geo-sample/*`) per test locali

## Quando usare cosa

- Hai Laravel? Usa il pacchetto Laravel: integrazione rapida con Blade components, publish di asset e config.
- Non hai Laravel? Usa il pacchetto JS: include le classi UMD e un esempio `examples/demo.html`.
- Vuoi entrambi? Nessun problema: il pacchetto Laravel usa gli stessi asset UMD.

## Passi rapidi

- JS
  1. Copia `reference/packages/js-geo-address` nel tuo progetto
  2. `npm run build` (facoltativo: crea `dist/browser.js`)
  3. Includi jQuery + Select2 + it.js + axios (opzionale) + `dist/browser.js`
  4. Inizializza `new Libreria.GeoSelect(...)` e `new Libreria.AddressFields(...)`

- Laravel
  1. Aggiungi il pacchetto come path repository in composer
  2. `composer update tanggo/laravel-geo-address`
  3. `php artisan vendor:publish` (asset, views, config)
  4. Includi `GeoSelect.js`, `AddressFields.js`, `bridge.js` dal path `/vendor/geo-address`
  5. Usa `<x-geo-address-geo-select ... />` e `<x-geo-address-address-fields ... />`

## Endpoints richiesti (GeoSelect)

- GET /nations
- GET /regions
- GET /provinces-all?codice_regione=XX
- GET /cities-by-province?sigla_provincia=XX
- GET /cap-by-province?sigla_provincia=XX&comune=...&codice_istat=...

Nel costruttore puoi passare `endpoints` per puntare a path diversi.

## Supporto

Se vuoi pubblicare i pacchetti su NPM/Packagist o creare uno Starter Project minimale, possiamo farlo in un follow-up.
