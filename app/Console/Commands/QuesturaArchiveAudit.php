<?php

namespace App\Console\Commands;

use App\Services\QuesturaRetentionService;
use Illuminate\Console\Command;

class QuesturaArchiveAudit extends Command
{
    protected $signature = 'questura:audit-archivi {struttura_id}';
    protected $description = 'Audit Questura in sola lettura: integrità, hash legacy e PDF orfani';

    public function handle(QuesturaRetentionService $service): int
    {
        $id = filter_var($this->argument('struttura_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) { $this->error('Identificativo struttura non valido.'); return self::FAILURE; }
        $this->line('Sola lettura: nessuna cancellazione, rigenerazione o scrittura hash.');
        foreach ($service->auditArchives($id) as $row) { $this->line(json_encode($row, JSON_THROW_ON_ERROR)); }
        return self::SUCCESS;
    }
}
