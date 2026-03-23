# Fail-safe CAP

- Si el CAP digitado/seleccionado no existe en `geo_cap` + `geo_comuni_cap`, el valor se conserva y se emite el evento sin limpiar el input.
- No se limpia la jerarquía existente ni se bloquea el flujo; el usuario puede seguir con CAP manual.
- Si el CAP existe, `resolve` hidrata Comune/Provincia/Regione/Nazione y lista de CAPs vinculados; se prioriza mantener el CAP actual si coincide, si no se usa `cap_default`.
- Se limpia jerarquía inferior solo cuando la selección superior cambia (no por un CAP manual inválido).
