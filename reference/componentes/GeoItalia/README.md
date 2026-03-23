# GeoItalia (paquete Laravel reutilizable)

GeoItalia es el componente oficial para selección geográfica Italia/Estero con jerarquía Nazione → Regione → Provincia → Comune → CAP, modo Estero manual, fail-safe de CAP y resolve automático. Este paquete incluye frontend, backend, rutas, Blade, migraciones, seeders y datasets para que funcione al copiarlo a cualquier proyecto Laravel.

## Qué resuelve
- Búsqueda Select2 y jerarquía completa para Italia.
- Resolución por CAP y por Comune con hidratación automática.
- Modo Estero manual manteniendo el payload coherente.
- Fail-safe CAP: conserva el CAP manual aunque no exista en dataset.
- Fallback automático de endpoint (`/geo` → `/api/geo`).

## Requisitos
- Laravel 9+.
- jQuery + Select2 (tema bootstrap-5) cargados en la vista.
- Cliente HTTP global `window.http` o `window.axios`.

## Instalación rápida
1) Copia el paquete (carpeta `GeoItalia/`).
2) Registra el Service Provider `GeoItalia\GeoItaliaServiceProvider` si no usas autodiscovery.
3) Ejecuta el comando: `php artisan geoitalia:install` (opción `--force` para sobrescribir).
4) Ejecuta migraciones y seeders:
	- `php artisan migrate`
	- `php artisan db:seed --class=GeoItaliaSeeder`
5) Importa `resources/js/geo-italia.js` en tu bundle y llama `initGeoItalia()` al cargar la vista.

## Uso oficial
- Única forma soportada: `<x-geo.italia prefix="geo" />`.
- Sin inits manuales ni scripts inline.
- Las rutas `/geo/*` se registran automáticamente por el Service Provider.

## Estado
- Estado: **STABLE / FROZEN**.
- Política: solo fixes críticos; no alterar comportamientos, endpoints, eventos ni estilos.
