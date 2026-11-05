<?php

namespace App\Console\Commands;

use App\Services\QuesturaRetentionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QuesturaExpiredReceipts extends Command
{
    protected $signature = 'questura:ricevute-scadute {struttura_id} {--at= : Data ISO di riferimento}';
    protected $description = 'Elenca le ricevute scadute in sola lettura, senza cancellazioni';

    public function handle(QuesturaRetentionService $service): int
    {
        $id = filter_var($this->argument('struttura_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $date = $this->option('at');
        if (!$id || ($date && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !Carbon::hasFormatWithModifiers($date, 'Y-m-d') || !Carbon::canBeCreatedFromFormat($date, 'Y-m-d')))) {
            $this->error('Struttura o data non valida.'); return self::FAILURE;
        }
        $rows = $service->expiredReceipts($id, $date ? Carbon::createFromFormat('!Y-m-d', $date) : now());
        $this->table(['ID ricevuta', 'Data comunicazione', 'Acquisita il', 'Conservare fino al'], $rows->map(fn ($r) => (array) $r)->all());
        $this->info('Sola lettura: nessuna ricevuta cancellata.');
        return self::SUCCESS;
    }
}
