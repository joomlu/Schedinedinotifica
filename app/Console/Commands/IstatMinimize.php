<?php

namespace App\Console\Commands;

use App\Services\IstatRetention;
use Illuminate\Console\Command;

final class IstatMinimize extends Command
{
    protected $signature = 'istat:minimize {--apply : Elimina le sole copie di lavoro scadute e riconciliabili}';

    protected $description = 'Verifica o minimizza le copie XML ISTAT scadute; preserva esiti incerti e storico tecnico';

    public function handle(IstatRetention $retention): int
    {
        $count = $retention->minimize((bool) $this->option('apply'));
        $this->info(($this->option('apply') ? 'Copie minimizzate: ' : 'Copie candidate, nessuna modifica: ').$count);

        return self::SUCCESS;
    }
}
