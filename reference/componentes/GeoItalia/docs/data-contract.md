# Data contract

## Shapes base
- Nazione: `{ id, text, nome, codice_iso2, is_italia, cittadinanza }`
- Regione: `{ id, text, nome, codice_regione, geo_nazione_id }`
- Provincia: `{ id, text, nome, sigla, geo_regione_id }` (text incluye sigla cuando existe).
- Comune: `{ id, text, nome, codice_istat, geo_provincia_id }`
- CAP (lista en `/cap` y `caps` de resolve): `{ cap, geo_comune_id, principale, priorita, localita }` + `geo_provincia_id` cuando viene de `/cap`.

## /resolve payload
```
{
  nazione: Nazione|null,
  regione: Regione|null,
  provincia: Provincia|null,
  comune: Comune|null,
  caps: Array<CAP>,
  cap_default: string|null
}
```

## Evento `geoselect:change`
Se emite sobre el contenedor `[data-ui="geo-italia"]` después de cada cambio relevante.

Payload:
```
{
  manualMode: boolean,
  nazione: { id?, value, label, codice_iso2?, sigla?, codice_istat? },
  regione: { id?, value, label, ... },
  provincia: { id?, value, label, ... },
  comune: { id?, value, label, ... },
  cap: { value, label }
}
```
Notas:
- En modo Italia los valores vienen de Select2; en modo Estero los campos son libres y solo llevan `label/value` de los inputs manuales.
- `manualMode=true` implica que la jerarquía guiada está oculta/deshabilitada.
