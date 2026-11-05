<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSaasSeeder;
use Illuminate\Console\Command;

class DemoSaasCommand extends Command
{
    protected $signature = 'demo:saas';

    protected $description = 'Crea/aggiorna dati demo per ruoli e multi-struttura';

    public function handle(): int
    {
        $this->info('Esecuzione DemoSaasSeeder...');
        $this->call('db:seed', ['--class' => DemoSaasSeeder::class]);
        $this->info('Completato.');

        return self::SUCCESS;
    }
}
