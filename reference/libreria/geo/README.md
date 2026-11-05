# @tanggo/geo (pacchetto unico)

Questo pacchetto contiene due classi UMD pronte per il browser:
- GeoSelect: gestisce la cascata Nazione → Regione → Provincia → Città → CAP (jQuery + Select2)
- AddressFields: gestisce i campi indirizzo e un riepilogo inline (vanilla JS)

Include:
- src/GeoSelect.js, src/AddressFields.js
- dist/browser.js (bundle semplice con entrambe le classi)
- docs/Relazione Geografica.md, docs/Indirizzo.md (istruzioni specifiche)
- examples/demo.html

## Browser (UMD)

```html
<script src="/path/to/jquery.min.js"></script>
<script src="/path/to/select2.full.min.js"></script>
<script src="/path/to/i18n/it.js"></script>
<script src="/path/to/axios.min.js"></script>
<script src="/path/to/dist/browser.js"></script>

<script>
  var geo = new Libreria.GeoSelect({
    nation: '#country', region: '#region', province: '#prov', city: '#city', cap: '#cap',
    preselectItaly: true, filterCapByCity: true, autoSelectUniqueCap: true,
    endpoints: {
      nations: '/nations', regions: '/regions', provincesAll: '/provinces-all',
      citiesByProvince: '/cities-by-province', capByProvince: '/cap-by-province'
    },
    container: document.getElementById('geo_wrap')
  });

  var addr = new Libreria.AddressFields({ root: '#addr_wrap', prefix: 'addr', inlineId: 'addr_inline' });
</script>
```

## Endpoints richiesti da GeoSelect

- GET /nations
- GET /regions
- GET /provinces-all?codice_regione=XX
- GET /cities-by-province?sigla_provincia=XX
- GET /cap-by-province?sigla_provincia=XX&comune=...&codice_istat=...

Puoi sovrascrivere i path con `endpoints` nel costruttore come sopra.

## Demo

Apri `examples/demo.html` e modifica gli endpoints in cima allo script se necessario.

## Note

- Richiede jQuery + Select2 per GeoSelect, e axios (opzionale) o window.http.
- AddressFields non ha dipendenze.
