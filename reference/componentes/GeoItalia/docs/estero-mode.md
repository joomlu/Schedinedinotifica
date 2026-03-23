# Modo Estero

- Toggle: botones Italia/Estero cambian `manualMode`.
- Comportamiento:
  - Italia: Select2 habilitados (nazione/regione/provincia/comune/cap) y jerarquía guiada.
  - Estero: se ocultan/inhabilitan selects italianos; se muestran inputs libres para región/provincia/città/CAP.
- Payload en `manualMode=true`: los campos de jerarquía llevan solo `label/value` de inputs manuales (sin ids de base de datos).
- Preservación de nación extranjera: si un `resolve` devuelve una nación no italiana, se entra en Estero manteniendo esa nación en el select (no se limpia) y se enmascara visualmente el placeholder.
- Observaciones visuales: en Estero se fuerza el placeholder en Select2 para ocultar “Italia” en el render sin tocar el valor almacenado.
- Uso oficial: siempre vía `<x-geo.italia ... />`, sin inicialización manual ni overrides locales.
