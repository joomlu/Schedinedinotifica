<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeoImportJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *  php artisan geo:import                 # usa i JSON già presenti in storage/app
     *  php artisan geo:import /percorso/dati  # copia i JSON da una cartella e poi importa
     */
    protected $signature = 'geo:import {path? : Cartella contenente i file gi_*.json} {--no-copy : Non copiare i file, usa quelli già in storage}';

    /**
     * The console command description.
     */
    protected $description = 'Importa i dataset geografici (gi_nazioni/regioni/province/comuni/cap) da JSON in storage e popola le tabelle geo_* e stati';

    /**
     * File attesi.
     */
    private array $expected = [
        'gi_nazioni.json',
        'gi_regioni.json',
        'gi_province.json',
        'gi_comuni.json',
        'gi_comuni_cap.json',
    ];

    public function handle(): int
    {
        $sourcePath = $this->argument('path');
        $destDir = storage_path('app');

        if (! is_dir($destDir)) {
            File::ensureDirectoryExists($destDir);
        }

        if ($sourcePath && ! $this->option('no-copy')) {
            $src = realpath($sourcePath);
            if (! $src || ! is_dir($src)) {
                $this->error("Percorso sorgente non valido: {$sourcePath}");
                return self::FAILURE;
            }
            $this->info("Copio i file JSON da: {$src}");
            foreach ($this->expected as $fname) {
                $from = $src . DIRECTORY_SEPARATOR . $fname;
                $to = $destDir . DIRECTORY_SEPARATOR . $fname;
                if (! file_exists($from)) {
                    $this->warn("(skip) Non trovato nel sorgente: {$fname}");
                    continue;
                }
                File::copy($from, $to);
                $this->line("✔ Copiato {$fname}");
            }
        } else {
            $this->line('Uso i file JSON già presenti in storage/app (opzione --no-copy o nessun percorso fornito).');
        }

        // Verifica presenza in storage/app
        foreach ($this->expected as $fname) {
            $p = $destDir . DIRECTORY_SEPARATOR . $fname;
            if (! file_exists($p)) {
                $this->error("File mancante in storage/app: {$fname}");
                return self::FAILURE;
            }
        }

        // Esegui il seeder che gestisce pulizia e inserimenti a chunk
        $this->info('Eseguo il seeder Database\\Seeders\\GeoDataSeeder…');
        $exit = $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\GeoDataSeeder',
            '--no-interaction' => true,
        ]);

        if ($exit !== self::SUCCESS) {
            $this->error('Seeder fallito. Controlla i file JSON e il log.');
            return $exit;
        }

        // Piccolo riepilogo dimensioni
        $summary = [];
        foreach ($this->expected as $fname) {
            $p = $destDir . DIRECTORY_SEPARATOR . $fname;
            $size = @filesize($p) ?: 0;
            $summary[] = sprintf('%s: %s', $fname, $this->humanBytes($size));
        }
        $this->info('Importazione completata. File in uso:');
        foreach ($summary as $line) {
            $this->line(' - ' . $line);
        }

        return self::SUCCESS;
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B','KB','MB','GB','TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return number_format($bytes, $i === 0 ? 0 : 2) . ' ' . $units[$i];
    }
}
