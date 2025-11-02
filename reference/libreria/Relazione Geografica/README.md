# Relazione Geografica (Nazione → Regione → Provincia → Città → CAP)

Componente front‑end basato su jQuery + Select2 per gestire la cascata geografica.
La classe è `GeoSelect` e si trova in `../geo/GeoSelect.js`.

## Dipendenze

- jQuery >= 3.6
- Select2 >= 4.1 + lingua italiana (it)
- Axios (opzionale) o `window.http` compatibile (metodo GET)

## Endpoints richiesti (default)

- GET /nations
- GET /regions
- GET /provinces-all?codice_regione=XX
- GET /cities-by-province?sigla_provincia=XX
- GET /cap-by-province?sigla_provincia=XX&comune=...&codice_istat=...

Puoi sovrascriverli nel costruttore con `endpoints: { ... }`.

## Uso rapido (Browser UMD)

1) Includi dipendenze e la classe:

```html
<script src="/path/to/jquery.min.js"></script>
<script src="/path/to/select2.full.min.js"></script>
<script src="/path/to/i18n/it.js"></script>
<script src="/path/to/axios.min.js"></script>
<script src="/reference/libreria/geo/GeoSelect.js"></script>
```

2) Inizializza:

```html
<script>
  var geo = new (window.GeoSelect || (window.Libreria && Libreria.GeoSelect))({
    nation: '#country',
    region: '#region',
    province: '#prov',
    city: '#city',
    cap: '#cap',
    preselectItaly: true,
    filterCapByCity: true,
    autoSelectUniqueCap: true,
    endpoints: {
      nations: '/nations',
      regions: '/regions',
      provincesAll: '/provinces-all',
      citiesByProvince: '/cities-by-province',
      capByProvince: '/cap-by-province'
    },
    container: document.getElementById('geo_wrap'),
    onChange: function(v){ console.log('geo', v); }
  });
</script>
```

## Uso con Blade (Laravel)

- Pubblica/fornisci gli asset UMD (o punta a `public/vendor/geo-address/*.js` se usi il pacchetto Laravel).
- Inserisci il componente Blade equivalente (se usi il pacchetto Laravel) oppure usa HTML + snippet UMD.

Esempio con component Blade del pacchetto Laravel:

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
```

## Eventi

- Dispatch su `container`: `geoselect:change` con `detail = { nation, region, province, city, cap, manualMode }`.

## Note pratiche

- Selezione CAP può autocompletare la città e forzare la UI di Select2 a mostrare la label.
- `filterCapByCity`: filtra i CAP in base alla città selezionata.
- `autoSelectUniqueCap`: se c'è un solo CAP, lo seleziona automaticamente.
- `backfillRegionFromProvince`: selezionando una provincia, risale automaticamente la regione.
