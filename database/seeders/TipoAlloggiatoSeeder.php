<?php

namespace Database\Seeders;

use App\Models\TipoAlloggiato;
use Illuminate\Database\Seeder;

class TipoAlloggiatoSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('reference/libreria/tipo_alloggiato.csv');
        if (!file_exists($path)) {
            $this->command?->error('File CSV non trovato: ' . $path);
            return;
        }

        $delimiter = $this->detectDelimiter($path);
        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->command?->error('Impossibile aprire il file CSV: ' . $path);
            return;
        }

        $header = $this->readCsvRow($handle, $delimiter);
        if (!$header) {
            $this->command?->error('Intestazione CSV non valida in: ' . $path);
            fclose($handle);
            return;
        }

        $header = array_map('trim', $header);

        while (($row = $this->readCsvRow($handle, $delimiter)) !== false) {
            if ($row === null) {
                continue;
            }

            $data = array_combine($header, array_map('trim', $row));
            if ($data === false) {
                continue;
            }

            $codice = $data['codice'] ?? $data['Codice'] ?? null;
            $descrizione = $data['descrizione'] ?? $data['Descrizione'] ?? null;

            if ($codice && $descrizione) {
                TipoAlloggiato::updateOrCreate(
                    ['codice' => $codice],
                    [
                        'descrizione' => $descrizione,
                        'locked' => true,
                    ]
                );
            }
        }

        fclose($handle);
    }

    private function detectDelimiter(string $path): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $line = '';

        $handle = fopen($path, 'r');
        if ($handle) {
            $line = fgets($handle) ?: '';
            fclose($handle);
        }

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($counts);
        $best = array_key_first($counts);

        return $best ?: ',';
    }

    private function readCsvRow($handle, string $delimiter)
    {
        $row = fgetcsv($handle, 0, $delimiter);

        if ($row === false) {
            return false;
        }

        if ($row === [null] || $row === []) {
            return null;
        }

        return $row;
    }
}

