# Arquitectura

## Frontend
- Componente JS `geo-italia.js` con Select2 sobre los selects de Nazione, Regione, Provincia, Comune y CAP.
- Toggle Italia/Estero que activa `manualMode` y oculta/inhabilita la jerarquía italiana mostrando campos libres.
- Fallback de endpoint (`/geo` → `/api/geo`) si la respuesta no trae resultados con forma Select2.
- Evento `geoselect:change` emitido en el contenedor para notificar el payload actual.
- Fail-safe CAP: conserva el CAP digitado aunque no esté en dataset, sin limpiar jerarquía ni bloquear.

## Backend
- Controlador `GeoController` con endpoints GET `/geo/*` (o `/api/geo/*` como backup) que devuelven formato Select2.
- Lógica de resolución en `/geo/resolve` para hidratar jerarquía a partir de CAP, Comune, codice ISTAT o sigla de Provincia.

## Tablas GEO
- `geo_nazioni`: id, codice_iso2 (unique), nome, cittadinanza, is_italia.
- `geo_regioni`: id, geo_nazione_id, codice_regione, nome.
- `geo_province`: id, geo_regione_id, sigla (unique), nome, codice_provincia.
- `geo_comuni`: id, geo_provincia_id, codice_istat (unique), nome, lat/lng opcional.
- `geo_cap`: id, cap (unique), lat/lng opcional.
- `geo_comuni_cap`: pivot Comune↔CAP con flags `principale`, `priorita`, `localita`.

## Flujo resolve
1) Si se recibe `cap`, busca CAP, obtiene Comune principal (orden: principale desc, priorita asc) y lista de CAPs vinculados.
2) Si no hay Comune por CAP, se intenta por `geo_comune_id` o `codice_istat`.
3) Provincia se resuelve desde Comune o por `sigla_provincia`.
4) Regione y Nazione se hidratan a partir de Provincia/Regione.
5) Devuelve jerarquía + lista de CAPs (`caps`) y `cap_default`.

## Italia / Estero
- Italia: modo guiado con Select2 y jerarquía completa; selects habilitados.
- Estero: `manualMode=true`, se ocultan bloques Italia, se muestran inputs libres, se preserva nación extranjera si viene de un resolve.

## Fail-safe CAP
- Seleccionar o escribir CAP dispara `resolve`.
- Si el CAP no existe en dataset: se conserva el valor, no se limpian campos manuales, no se rompe el flujo.
