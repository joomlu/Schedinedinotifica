<?php

namespace App\Console\Commands;

use App\Services\TassaConfig\GestioneProfiliTassa;
use Illuminate\Console\Command;

class VerificaProfiliTassa extends Command
{
    protected $signature = 'tassa:verifica-profili {--periodica : Controlla subito anche le fonti annuali note}';

    protected $description = 'Controlla le fonti istituzionali note per l’anno corrente e successivo, senza alterare lo storico';

    public function handle(GestioneProfiliTassa $gestione, \App\Services\TassaConfig\RichiesteVerificaTassa $richieste): int
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->makeDirectory(\App\Services\TassaConfig\ProfiloTassaRepository::ROOT);
        $lock = fopen($disk->path('tassa-profili/verifica.lock'), 'c');
        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            return self::SUCCESS;
        }
        try {
            if ($this->laravel->isDownForMaintenance()) {
                return self::SUCCESS;
            }
            $ultima = $disk->exists('tassa-profili/ultima-periodica.txt') ? (int) $disk->get('tassa-profili/ultima-periodica.txt') : 0;
            if ($this->option('periodica') || now()->timestamp - $ultima >= 7 * 86400) {
                foreach (array_keys(config('tassa_fonti')) as $comune) {
                    foreach ([now()->year, now()->year + 1] as $anno) {
                        $gestione->verifica($comune, $anno);
                        $this->info('Verifica registrata: '.$comune.' — '.$anno.'. Consultare gli esiti nella scheda Superadmin.');
                    }
                }
                $disk->put('tassa-profili/ultima-periodica.txt', (string) now()->timestamp);
            }
            $richieste->processa();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return self::SUCCESS;
    }
}
