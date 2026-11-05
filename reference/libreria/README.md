# Libreria componenti (GeoSelect, AddressFields)

Due componenti front-end riusabili con classi JavaScript, pronti per essere importati/esportati e integrati in altre app.

## Contenuto

- geo/GeoSelect.js — Componente Select2 con cascata Nazione → Regione → Provincia → Città → CAP
- address/AddressFields.js — Componente di campi indirizzo (Tipo di Via, Strada, Num, Int) con eventi
- index.js — Namespace boot (browser)

## Dipendenze

- jQuery (>=3.6)
- Select2 (>=4.1) e la lingua it
- Axios (opzionale) o `window.http` compatibile (GET)
- Endpoints disponibili in backend (stessi path usati nell'app):
  - GET /nations
  - GET /regions
  - GET /provinces-all
  - GET /cities-by-province
  - GET /cap-by-province

## Uso in browser (UMD)

1. Includi le dipendenze (jQuery, Select2, it.js, axios) e i file:

<script src="/path/to/jquery.min.js"></script>
<script src="/path/to/select2.full.min.js"></script>
<script src="/path/to/i18n/it.js"></script>
<script src="/path/to/axios.min.js"></script>
<script src="/reference/libreria/geo/GeoSelect.js"></script>
<script src="/reference/libreria/address/AddressFields.js"></script>

2. Inizializza:

<script>
  // Geo
  var geo = new Libreria.GeoSelect({
    nation: '#country', region: '#region', province: '#prov', city: '#city', cap: '#cap',
    preselectItaly: true, manualForNonItaly: true, filterCapByCity: true, autoSelectUniqueCap: true,
    container: document.getElementById('geo_wrap'),
    onChange: function(v){ console.log('geo', v); }
  });

  // Address
  var addr = new Libreria.AddressFields({ root: '#addr_wrap', prefix: 'addr', inlineId: 'addr_inline' });
  document.getElementById('addr_wrap').addEventListener('address:change', function(e){ console.log('addr', e.detail); });
</script>

## Uso con bundler (CommonJS/ESM)

- CommonJS:

const GeoSelect = require('./geo/GeoSelect');
const AddressFields = require('./address/AddressFields');

- ESM:

import GeoSelect from './geo/GeoSelect.js';
import AddressFields from './address/AddressFields.js';

## Eventi

- GeoSelect: `geoselect:change` dispatch su `container` con `detail = { nation, region, province, city, cap, manualMode }`
- AddressFields: `address:change` dispatch su `root` con `detail = { typeaway, address, num, internal }`

## Note

- GeoSelect si affida agli endpoint esistenti; per renderlo portabile, replica le route nel progetto di destinazione.
- Per un packaging NPM futuro, si può estrarre questi file in un repo dedicato e pubblicare la libreria.
