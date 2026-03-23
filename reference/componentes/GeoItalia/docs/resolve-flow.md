# Resolve flow

## Selección por CAP
1) Usuario busca/selecciona CAP en Select2 (o lo teclea si se permite).
2) Se llama `/geo/resolve?cap={cap}`.
3) Si el CAP existe: se hidrata Comune → Provincia → Regione → Nazione y se listan todos los CAPs asociados (`caps`, `cap_default`).
4) Si el CAP no existe: se conserva el valor manual (fail-safe), no se limpia la jerarquía existente, se emite el evento igualmente.

## Selección por Comune
1) Usuario selecciona Comune en Select2.
2) Se llama `/geo/resolve?geo_comune_id={id}`.
3) Respuesta hidrata Comune/Provincia/Regione/Nazione y lista de CAPs del Comune.
4) El CAP actual se mantiene si coincide; si no, se preselecciona `cap_default` o el primer CAP.

## Selección por Provincia (sigla)
1) Usuario selecciona Provincia (que tiene sigla).
2) Se llama `/geo/resolve?sigla_provincia={sigla}`.
3) Respuesta hidrata Provincia/Regione/Nazione; Comune y CAP quedan pendientes.

## Hidratación de jerarquía
- Siempre se respeta el último resolve secuencial (se descartan respuestas obsoletas).
- Al hidratar se usan `ensureOption` + `trigger select2` para que el UI muestre las opciones correctas.
- La jerarquía inferior se limpia solo cuando corresponde (no en fail-safe CAP manual).
