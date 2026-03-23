# Export checklist

- Copiar Blade: `resources/views/components/geo/italia.blade.php`.
- Copiar JS: `resources/js/ui/geo-italia.js` + helper `core/once` (ajustar ruta si difiere).
- Copiar controlador: `app/Http/Controllers/GeoController.php`.
- Registrar rutas `/geo/*` en `routes/web.php` (y opcional `/api/geo/*`).
- Copiar migración: `database/migrations/2026_03_01_150000_create_geo_tables.php`.
- Cargar datasets completos en tablas `geo_nazioni`, `geo_regioni`, `geo_province`, `geo_comuni`, `geo_cap`, `geo_comuni_cap`.
- Instalar dependencias front: jQuery, Select2 (tema bootstrap-5), axios/http global disponible.
- Compilar bundle que incluya `geo-italia.js` y ejecute `initGeoItalia()`.
- Verificar render `<x-geo.italia />` sin errores en consola.
- Probar QA rápido: búsqueda de CAP conocido, toggle Italia/Estero, CAP manual inexistente.
