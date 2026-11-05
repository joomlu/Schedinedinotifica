<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSaasDataFullSeeder;
use Illuminate\Console\Command;

class DemoSaasFullCommand extends Command
{
    protected $signature = 'demo:full';

    protected $description = 'Crea/aggiorna dati demo completi (multi-ruolo/multi-struttura)';

    public function handle(): int
    {
        $this->info('Esecuzione DemoSaasDataFullSeeder...');
        $this->call('db:seed', ['--class' => DemoSaasDataFullSeeder::class]);
        $this->info('Completato.');

        return self::SUCCESS;
    }
}
