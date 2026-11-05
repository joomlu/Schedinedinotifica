# API endpoints

Todos los endpoints son GET y devuelven objetos con forma Select2: `{ results: [...], pagination: { more: bool } }`.

## /geo/nazioni
- Método: GET
- Parámetros: `q` (texto), `page` (int), `per_page` (int), `is_italia` (bool opcional para filtrar Italia).
- Respuesta (item): `{ id, text, nome, codice_iso2, is_italia, cittadinanza }`.
- Ejemplo:
```json
{
  "results": [
    {"id":1,"text":"Italia (IT)","nome":"Italia","codice_iso2":"IT","is_italia":true,"cittadinanza":"italiana"}
  ],
  "pagination":{"more":false}
}
```

## /geo/regioni
- Método: GET
- Parámetros: `q`, `page`, `per_page`, `geo_nazione_id` (opcional, filtra por nación).
- Respuesta (item): `{ id, text, nome, codice_regione, geo_nazione_id }`.
- Ejemplo:
```json
{
  "results":[{"id":10,"text":"Lazio","nome":"Lazio","codice_regione":"12","geo_nazione_id":1}],
  "pagination":{"more":false}
}
```

## /geo/province
- Método: GET
- Parámetros: `q`, `page`, `per_page`, `geo_regione_id` (opcional).
- Respuesta (item): `{ id, text, nome, sigla, geo_regione_id }` (text incluye sigla cuando existe).
- Ejemplo:
```json
{
  "results":[{"id":57,"text":"Roma (RM)","nome":"Roma","sigla":"RM","geo_regione_id":10}],
  "pagination":{"more":false}
}
```

## /geo/comuni
- Método: GET
- Parámetros: `q`, `page`, `per_page`, `geo_provincia_id` (opcional).
- Respuesta (item): `{ id, text, nome, codice_istat, geo_provincia_id }`.
- Ejemplo:
```json
{
  "results":[{"id":1234,"text":"Roma","nome":"Roma","codice_istat":"058091","geo_provincia_id":57}],
  "pagination":{"more":false}
}
```

## /geo/cap
- Método: GET
- Parámetros: `q`, `page`, `per_page`, `geo_comune_id` (opcional), `geo_provincia_id` (opcional cuando no hay comune).
- Respuesta (item): `{ id, text, cap, geo_comune_id, geo_provincia_id, principale, priorita, localita }`.
- Ejemplo:
```json
{
  "results":[{"id":"00100","text":"00100","cap":"00100","geo_comune_id":1234,"geo_provincia_id":57,"principale":true,"priorita":10,"localita":null}],
  "pagination":{"more":false}
}
```

## /geo/resolve
- Método: GET
- Parámetros (cualquiera de): `cap`, `geo_comune_id`, `codice_istat`, `sigla_provincia`.
- Respuesta:
```json
{
  "nazione": {"id":1,"nome":"Italia","text":"Italia","codice_iso2":"IT","is_italia":true,"cittadinanza":"italiana"},
  "regione": {"id":10,"nome":"Lazio","text":"Lazio","codice_regione":"12","geo_nazione_id":1},
  "provincia": {"id":57,"nome":"Roma","text":"Roma (RM)","sigla":"RM","geo_regione_id":10},
  "comune": {"id":1234,"nome":"Roma","text":"Roma","codice_istat":"058091","geo_provincia_id":57},
  "caps": [{"cap":"00100","geo_comune_id":1234,"principale":true,"priorita":10,"localita":null}],
  "cap_default": "00100"
}
```
