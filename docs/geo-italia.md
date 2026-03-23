# GeoItalia (módulo cerrado)

## Objetivo
Componente único para selección jerárquica Nazione → Regione → Provincia → Comune → CAP, con modo Italia/Estero y CAP manual. Uso oficial y único: `<x-geo.italia ... />`.

## Alcance
- Sin cambio de comportamiento funcional actual.
- Sin scripts inline ni inicializaciones manuales externas.
- Dependencia obligatoria de tablas GEO pobladas.
- Jerarquía, reseteos descendentes y validación interna residen sólo en el módulo.

## Archivos del módulo
- Blade oficial: `resources/views/components/geo/italia.blade.php`
- JS oficial: `resources/js/ui/geo-italia.js` (reexport en `resources/js/components/geo/geo-italia.js`)
- Controlador/endpoints: `app/Http/Controllers/GeoController.php`
- Rutas: grupo `/geo` en `routes/web.php`
- Config mínima: `config/geo.php` (tablas requeridas, flags de dependencia)

## Tablas obligatorias (pobladas)
- `geo_nazioni`
- `geo_regioni`
- `geo_province`
- `geo_comuni`
- `geo_cap`
- `geo_comuni_cap`
No hay fallback inventado: si faltan datos, el comportamiento es indefinido y debe resolverse poblando las tablas.

## Endpoints oficiales
Prefijo: `/geo` (o el definido en config, mismo contrato).
- `GET /geo/nazioni`: params `q`, `page`; devuelve `{results:[{id,text,nome,codice_iso2,is_italia,cittadinanza}], pagination:{more}}`
- `GET /geo/regioni`: params `q`, `page`, `geo_nazione_id`; devuelve `{results:[{id,text,nome,codice_regione,geo_nazione_id}], pagination:{more}}`
- `GET /geo/province`: params `q`, `page`, `geo_regione_id`; devuelve `{results:[{id,text,nome,sigla,geo_regione_id}], pagination:{more}}`
- `GET /geo/comuni`: params `q`, `page`, `geo_provincia_id`; devuelve `{results:[{id,text,nome,codice_istat,geo_provincia_id}], pagination:{more}}`
- `GET /geo/cap`: params `q`, `page`, `geo_comune_id`, `geo_provincia_id`; devuelve `{results:[{id,text,cap,geo_comune_id,geo_provincia_id,principale,priorita,localita}], pagination:{more}}`
- `GET /geo/resolve`: params combinables `cap`, `geo_comune_id`, `codice_istat`, `sigla_provincia`; devuelve `{nazione,regione,provincia,comune,caps:[{cap,geo_comune_id,principale,priorita,localita}],cap_default}`

## Contrato de datos esperado
- Formato Select2: `{results: [...], pagination: {more: bool}}` con `text` como label.
- `resolve` hidrata jerarquía completa y lista de CAP válidos para select CAP.
- El JS normaliza respuestas que ya cumplan este shape o provengan de `data` plano.

## Uso oficial en Blade
```
<x-geo.italia
    prefix="geo"
    :value="$modelo?->toArray()"
    endpointBase="/geo"
    endpointFallback="/api/geo"  <!-- opcional, mismo contrato -->
    :showToggleItalia="true"
    :showNazione="true"
    :showRegione="true"
    :showProvincia="true"
    :showComune="true"
    :showCap="true"
    :showIndirizzo="false"
    :showCittadinanza="false"
/>
```
- Props admitidas: las que expone el Blade; no añadir lógicas propias.
- Los `name` se derivan de `prefix`; puede sobreescribirse vía `names` si se requiere compatibilidad legacy.

## Inicialización JS
- El bundle incluye `initGeoItalia` y `initOnce`; los selects internos llevan `data-geo="1"` y quedan excluidos del init genérico de Select2 (`resources/js/ui/select2.js`).
- No llamar manualmente a `select2()` sobre los selects GEO. No usar scripts inline para manipular su estado interno.
- Para montar en un fragmento dinámico, invocar `initGeoItalia(fragment)` una sola vez.

## Qué NO tocar
- No inicializar Select2 sobre selects con `data-geo="1"` ni dentro de `[data-ui="geo-italia"]`.
- No modificar endpoints ni parámetros desde vistas o formularios.
- No insertar lógica jerárquica en vistas; usar el componente.
- No agregar scripts inline para forzar valores: usar los valores iniciales del componente (`value`).

## Exportación a otro sistema (checklist)
1) BD: migraciones/tablas/dataset completos para las 6 tablas GEO.
2) Backend: copiar `app/Http/Controllers/GeoController.php` y registrar rutas `/geo` con el mismo contrato.
3) Blade: copiar `resources/views/components/geo/italia.blade.php` y registrar el componente en el service provider si aplica.
4) JS: incluir `resources/js/ui/geo-italia.js` (y su reexport) en el bundle; asegurar que jQuery + Select2 estén disponibles y que `window.http`/`axios` exista.
5) Front: asegurar estilos Select2 bootstrap-5 ya presentes en el theme.
6) Inicialización: llamar a `initGeoItalia()` en el layout principal (o fragmento) después de montar el DOM.
7) Uso: emplear únicamente `<x-geo.italia ... />` en las vistas. No exponer endpoints alternativos ni configuraciones custom.

## Dependencias externas
- jQuery y Select2 (tema bootstrap-5) ya presentes en el bundle.
- Cliente HTTP global (`window.http` o `window.axios`).

## Nota de cierre
GeoItalia es la única vía soportada para capturar ubicación Italia/Estero. Cualquier implementación alternativa debe alinearse a este contrato o se considerará no soportada.
