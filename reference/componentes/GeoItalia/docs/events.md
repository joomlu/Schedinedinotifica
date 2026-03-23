# Eventos

## geoselect:change
- Cuándo se emite: después de cada selección/clear de Nazione, Regione, Provincia, Comune, CAP; después de toggles Italia/Estero; después de `resolve`.
- Dónde: en el contenedor `[data-ui="geo-italia"]`.
- Payload: ver `docs/data-contract.md` (campo `manualMode` + objetos de cada nivel).
- Uso típico: escuchar en el formulario para sincronizar otros campos o validar estados.

### Ejemplo de escucha
```js
document.querySelector('[data-ui="geo-italia"]').addEventListener('geoselect:change', (e) => {
    const { manualMode, nazione, comune, cap } = e.detail;
    console.log(manualMode ? 'Estero' : 'Italia', nazione, comune, cap);
});
```
