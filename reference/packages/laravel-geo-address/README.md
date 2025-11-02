# tanggo/laravel-geo-address

Componenti Blade e asset JS per gestire:
- Relazione geografica a cascata (Nazione → Regione → Provincia → Città → CAP)
- Campi Indirizzo (Tipo di via, Strada, Numero, Interno)

Funziona con jQuery + Select2 lato front-end ed è backend-agnostico: puoi collegarlo ai tuoi endpoint.

## Installazione (locale o path repository)

1) Copia la cartella `reference/packages/laravel-geo-address` in un repo dedicato o aggiungila come path repository nel tuo progetto Laravel:

```json
// composer.json del progetto host
{
  "repositories": [
    { "type": "path", "url": "../packages/laravel-geo-address", "options": { "symlink": true } }
  ],
  "require": {
    "tanggo/laravel-geo-address": "*@dev"
  }
}
```

2) Installa:

```bash
composer update tanggo/laravel-geo-address
php artisan vendor:publish --tag=geo-address-assets
php artisan vendor:publish --tag=geo-address-views
php artisan vendor:publish --tag=geo-address-config
```

3) Includi gli asset nella tua layout (dopo jQuery, Select2, it.js, axios opzionale):

```html
<script src="/vendor/geo-address/GeoSelect.js"></script>
<script src="/vendor/geo-address/AddressFields.js"></script>
<script src="/vendor/geo-address/bridge.js"></script>
```

Ora `window.Libreria.GeoSelect` è disponibile e anche `window.GeoSelect` (alias), idem per AddressFields.

## Blade components

- <x-geo-address-geo-select ... />
- <x-geo-address-address-fields ... />

Esempio minimo:

```blade
<x-geo-address-geo-select
  prefix="geo"
  preselectItaly="true"
  :endpoints="[
    'nations' => '/nations',
    'regions' => '/regions',
    'provincesAll' => '/provinces-all',
    'citiesByProvince' => '/cities-by-province',
    'capByProvince' => '/cap-by-province',
  ]"
/>
<x-geo-address-address-fields prefix="addr" />
```

Assicurati di avere le route lato backend (vedi Contratti API).

## Rotte di esempio (demo)

Puoi abilitare delle rotte di esempio per test locale:

```php
// config/geo_address.php
'enable_sample_routes' => true,
```

Le troverai su `/geo-sample/*` (nations, regions, provinces-all, cities-by-province, cap-by-province) con dati minimi.
Per provarle al volo, passa gli endpoints così:

```blade
<x-geo-address-geo-select :endpoints="[
  'nations' => '/geo-sample/nations',
  'regions' => '/geo-sample/regions',
  'provincesAll' => '/geo-sample/provinces-all',
  'citiesByProvince' => '/geo-sample/cities-by-province',
  'capByProvince' => '/geo-sample/cap-by-province',
]" />
```

## Contratti API attesi

- GET /nations → [{ denominazione_nazione, denominazione_cittadinanza, sigla_nazione }]
- GET /regions → [{ codice_regione, denominazione_regione }]
- GET /provinces-all?codice_regione=XX → [{ sigla_provincia, denominazione_provincia, codice_regione }]
- GET /cities-by-province?sigla_provincia=XX → [{ denominazione_ita, sigla_provincia, codice_regione, codice_istat }]
- GET /cap-by-province?sigla_provincia=XX&comune=...&codice_istat=... → [{ cap, sigla_provincia, denominazione_ita, codice_istat }]

Se i tuoi endpoint hanno path diversi, puoi mantenere gli stessi contratti e sovrascrivere lato proxy; il JS legge direttamente da questi path.

## Note

- Il pacchetto non fornisce le migrazioni con il dataset geografico completo (per dimensioni). Se ti serve DB, crea repository/servizi nel tuo progetto e implementa le stesse API.
- Il JS usa `window.http.get` se presente, altrimenti `window.axios.get`.
- Richiede jQuery + Select2 correttamente caricati prima dell’inizializzazione.
