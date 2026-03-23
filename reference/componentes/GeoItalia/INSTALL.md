# INSTALL

Guía para instalar el paquete GeoItalia en otro proyecto Laravel.

## Paso a paso
1) Copia la carpeta `GeoItalia/` a tu proyecto (por ejemplo en `packages/` o `reference/`).
2) Registra el Service Provider si tu autoload no lo detecta automáticamente:
   - `GeoItalia\GeoItaliaServiceProvider::class`
3) Ejecuta el comando de instalación:
   - `php artisan geoitalia:install` (usa `--force` para sobrescribir archivos existentes).
   - El comando publica migraciones (con timestamp), seeder, datasets, rutas, Blade y JS.
4) Migra y carga datos:
   - `php artisan migrate`
   - `php artisan db:seed --class=GeoItaliaSeeder`
5) Frontend:
   - Importa `resources/js/geo-italia.js` en tu entry (Vite/Mix) y llama `initGeoItalia()` tras el load de la vista.
   - Asegura jQuery + Select2 (tema bootstrap-5) en la página.
6) Uso en vistas:
   - Renderiza el componente oficial: `<x-geo.italia prefix="geo" />`

## Qué publica el comando
- Migraciones `geo_*` (timestamped en `database/migrations`).
- Seeder `GeoItaliaSeeder` en `database/seeders`.
- Datasets GEO (`database/data/*.json`).
- Rutas `routes/geo.php`.
- Blade `resources/views/components/geo/italia.blade.php`.
- JS `resources/js/geo-italia.js`.

## Nota de uso
- No inicializar manualmente ni modificar la lógica JS/Blade. El paquete está congelado; solo se aceptan fixes críticos.
