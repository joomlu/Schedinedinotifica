<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ExportSchedaClienteModule extends Command
{
    protected $signature = 'scheda-cliente:export {--target= : Cartella di destinazione assoluta}';

    protected $description = 'Esporta il modulo Scheda Cliente con manifest e dipendenze.';

    public function handle(): int
    {
        $cfg = (array) config('scheda_cliente', []);
        $name = (string) ($cfg['name'] ?? 'scheda-cliente');
        $files = array_values(array_filter((array) ($cfg['files'] ?? [])));

        if (empty($files)) {
            $this->error('Configurazione scheda_cliente.files vuota.');
            return self::FAILURE;
        }

        $timestamp = Carbon::now()->format('Ymd_His');
        $baseDir = $this->option('target')
            ? rtrim((string) $this->option('target'), DIRECTORY_SEPARATOR)
            : storage_path('app/exports');

        if (!File::exists($baseDir)) {
            File::makeDirectory($baseDir, 0755, true);
        }

        $zipPath = $baseDir . DIRECTORY_SEPARATOR . $name . '-' . $timestamp . '.zip';
        $manifestPath = $baseDir . DIRECTORY_SEPARATOR . $name . '-' . $timestamp . '.json';

        $manifest = [
            'name' => $name,
            'generated_at' => Carbon::now()->toIso8601String(),
            'tables' => array_values((array) ($cfg['tables'] ?? [])),
            'routes' => array_values((array) ($cfg['routes'] ?? [])),
            'files' => [],
            'missing_files' => [],
        ];

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Impossibile creare archivio ZIP.');
            return self::FAILURE;
        }

        foreach ($files as $relativePath) {
            $abs = base_path($relativePath);
            if (!File::exists($abs) || !File::isFile($abs)) {
                $manifest['missing_files'][] = $relativePath;
                continue;
            }

            $zip->addFile($abs, $relativePath);
            $manifest['files'][] = $relativePath;
        }

        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $zip->addFromString('manifest.json', $manifestJson ?: '{}');
        $zip->close();

        File::put($manifestPath, $manifestJson ?: '{}');

        $this->info('Modulo esportato con successo.');
        $this->line('ZIP: ' . $zipPath);
        $this->line('Manifest: ' . $manifestPath);

        if (!empty($manifest['missing_files'])) {
            $this->warn('File mancanti nel pacchetto:');
            foreach ($manifest['missing_files'] as $missing) {
                $this->line('- ' . $missing);
            }
        }

        return self::SUCCESS;
    }
}

