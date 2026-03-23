# @tanggo/js-geo-address

Componenti JavaScript riutilizzabili per relazioni geografiche (GeoSelect) e campi indirizzo (AddressFields).

- GeoSelect: cascata Nazione → Regione → Provincia → Città → CAP (Select2 + jQuery)
- AddressFields: campi indirizzo con evento `address:change`

## Installazione (copiata/local)

Finché non viene pubblicato su NPM, puoi copiare la cartella `reference/packages/js-geo-address` in un repo separato o usare una dependency di tipo `file:` nel tuo progetto.

## Dipendenze

- jQuery >= 3.6
- Select2 >= 4.1 e locale `it`
- Axios (opzionale) o `window.http` compatibile (GET)

## Browser (UMD)

Include le dipendenze e poi i file:

```html
<script src="/path/to/jquery.min.js"></script>
<script src="/path/to/select2.full.min.js"></script>
<script src="/path/to/i18n/it.js"></script>
<script src="/path/to/axios.min.js"></script>
<script src="/dist/browser.js"></script>
```

Inizializza:

```html
<script>
  var geo = new Libreria.GeoSelect({
    nation: '#country', region: '#region', province: '#prov', city: '#city', cap: '#cap',
    preselectItaly: true, filterCapByCity: true, autoSelectUniqueCap: true,
    container: document.getElementById('geo_wrap')
  });

  var addr = new Libreria.AddressFields({ root: '#addr_wrap', prefix: 'addr', inlineId: 'addr_inline' });
</script>
```

## Bundler (CJS/ESM)

```js
import GeoSelect from '@tanggo/js-geo-address/src/geo/GeoSelect.js';
import AddressFields from '@tanggo/js-geo-address/src/address/AddressFields.js';

new GeoSelect({ /* cfg */ });
new AddressFields({ /* cfg */ });
```

## Contratti API richiesti da GeoSelect

- GET /nations → [{ denominazione_nazione, denominazione_cittadinanza, sigla_nazione }]
- GET /regions → [{ codice_regione, denominazione_regione }]
- GET /provinces-all?codice_regione=XX → [{ sigla_provincia, denominazione_provincia, codice_regione }]
- GET /cities-by-province?sigla_provincia=XX → [{ denominazione_ita, sigla_provincia, codice_regione, codice_istat }]
- GET /cap-by-province?sigla_provincia=XX&comune=...&codice_istat=... → [{ cap, sigla_provincia, denominazione_ita, codice_istat }]

Configura i path secondo il tuo backend oppure proxy con il tuo router.

Puoi anche passare gli endpoint esplicitamente al costruttore:

```js
new GeoSelect({
  nation: '#country', region: '#region', province: '#prov', city: '#city', cap: '#cap',
  endpoints: {
    nations: '/nations',
    regions: '/regions',
    provincesAll: '/provinces-all',
    citiesByProvince: '/cities-by-province',
    capByProvince: '/cap-by-province',
  }
});
```
