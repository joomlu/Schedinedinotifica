<?php

namespace GeoItalia\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallGeoItalia extends Command
{
    protected $signature = 'geoitalia:install {--force : Sobrescribe archivos existentes}';

    protected $description = 'Publica GeoItalia (migraciones, seeders, datasets, rutas, vistas y JS).';

    public function handle(Filesystem $files): int
    {
        $base = realpath(__DIR__ . '/..');
        $force = (bool) $this->option('force');

        $this->info('Publicando GeoItalia...');

        $this->publishMigrations($files, $base, $force);
        $this->copyFile($files, "$base/database/seeders/GeoItaliaSeeder.php", database_path('seeders/GeoItaliaSeeder.php'), $force);
        $this->copyDirectory($files, "$base/database/data", database_path('data'), $force);
        $this->copyFile($files, "$base/routes/geo.php", base_path('routes/geo.php'), $force);
        $this->copyFile($files, "$base/resources/views/components/geo/italia.blade.php", resource_path('views/components/geo/italia.blade.php'), $force);
        $this->copyFile($files, "$base/resources/js/geo-italia.js", resource_path('js/geo-italia.js'), $force);

        $this->line('');
        $this->info('GeoItalia instalado. Pasos siguientes:');
        $this->line('1) php artisan migrate');
        $this->line('2) php artisan db:seed --class=GeoItaliaSeeder');
        $this->line('3) Importa resources/js/geo-italia.js en tu bundle y ejecuta initGeoItalia()');
        $this->line('4) Usa el componente Blade: <x-geo.italia />');

        return self::SUCCESS;
    }

    protected function publishMigrations(Filesystem $files, string $base, bool $force): void
    {
        $migrations = [
            'create_geo_nazioni_table.php',
            'create_geo_regioni_table.php',
            'create_geo_province_table.php',
            'create_geo_comuni_table.php',
            'create_geo_cap_table.php',
            'create_geo_comuni_cap_table.php',
        ];

        $targetDir = database_path('migrations');
        $timestamp = Carbon::now();

        foreach ($migrations as $index => $file) {
            $name = $timestamp->copy()->addSeconds($index)->format('Y_m_d_His') . '_' . $file;
            $this->copyFile(
                $files,
                "$base/database/migrations/$file",
                $targetDir . '/' . $name,
                $force
            );
        }
    }

    protected function copyFile(Filesystem $files, string $from, string $to, bool $force): void
    {
        $dir = dirname($to);
        if (! $files->isDirectory($dir)) {
            $files->makeDirectory($dir, 0755, true);
        }

        if ($files->exists($to) && ! $force) {
            $this->warn("Omitido (existe): $to");
            return;
        }

        $files->copy($from, $to);
        $this->info("Copiado: $to");
    }

    protected function copyDirectory(Filesystem $files, string $from, string $to, bool $force): void
    {
        if (! $files->isDirectory($from)) {
            $this->warn("No encontrado: $from");
            return;
        }

        $files->ensureDirectoryExists($to);
        foreach ($files->allFiles($from) as $file) {
            $relative = $file->getRelativePathname();
            $target = $to . '/' . $relative;
            $this->copyFile($files, $file->getPathname(), $target, $force);
        }
    }
}
