# Indirizzo (Tipo di Via, Strada, Numero, Interno)

Componente front‑end leggero per gestire i campi indirizzo e produrre una stringa riassuntiva.
La classe è `AddressFields` e si trova in `../address/AddressFields.js`.

## Dipendenze

- Nessuna (vanilla JS). Facoltativo integrazione con Select2/jQuery se vuoi abbellire il select di Tipo di Via.

## Uso rapido (Browser UMD)

```html
<script src="/reference/libreria/address/AddressFields.js"></script>

<div id="addr_wrap">
  <select id="addr_typeaway">
    <option value="">Seleziona...</option>
    <option>Via</option><option>Viale</option><option>Piazza</option>
  </select>
  <input id="addr_address" />
  <input id="addr_num" />
  <input id="addr_internal" />
  <small id="addr_inline"></small>
</div>
<script>
  var addr = new (window.AddressFields || (window.Libreria && Libreria.AddressFields))({
    root: '#addr_wrap',
    prefix: 'addr',
    inlineId: 'addr_inline'
  });
  document.getElementById('addr_wrap').addEventListener('address:change', function(e){
    console.log('address', e.detail);
  });
</script>
```

## Uso con Blade (Laravel)

Se usi il pacchetto Laravel, puoi inserire direttamente il componente:

```blade
<x-geo-address-address-fields prefix="addr" />
```

## Evento

- Dispatch su `root`: `address:change` con `detail = { typeaway, address, num, internal }`.
