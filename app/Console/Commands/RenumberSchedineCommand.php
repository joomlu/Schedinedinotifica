<?php

namespace App\Console\Commands;

use App\Models\Schedina;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RenumberSchedineCommand extends Command
{
    protected $signature = 'schedine:renumber
        {--apply : Applica la rinumerazione}
        {--struttura= : Limita la rinumerazione a una struttura specifica}
        {--circuito= : Limita la rinumerazione a un circuito specifico (schedina, arrivi, web)}';

    protected $description = 'Rinumerazione controllata di schedine, arrivi e web check-in per struttura, circuito e anno';

    public function handle(): int
    {
        $query = Schedina::query()
            ->withoutGlobalScopes()
            ->orderBy('struttura_id')
            ->orderByRaw('arrive IS NULL')
            ->orderBy('arrive')
            ->orderBy('created_at')
            ->orderBy('id');

        if ($this->option('struttura')) {
            $query->where('struttura_id', (int) $this->option('struttura'));
        }

        /** @var Collection<int, Schedina> $rows */
        $rows = $query->get([
            'id',
            'struttura_id',
            'scheda',
            'circuito',
            'is_arrive',
            'arrive',
            'created_at',
        ]);

        if ($rows->isEmpty()) {
            $this->warn('Nessuna schedina trovata per la rinumerazione.');
            return self::SUCCESS;
        }

        $requestedCircuit = $this->option('circuito')
            ? $this->normalizeCircuitName((string) $this->option('circuito'))
            : null;

        $serials = [];
        $changes = [];

        foreach ($rows as $row) {
            $circuito = $this->normalizeSchedaCircuit($row);
            if (!in_array($circuito, ['schedina', 'arrivi', 'web'], true)) {
                continue;
            }

            if ($requestedCircuit && $circuito !== $requestedCircuit) {
                continue;
            }

            $yy = $this->codeYearForRecord($row);
            $prefix = $this->circuitCodePrefix($circuito);
            $groupKey = implode('|', [
                (string) ($row->struttura_id ?? 0),
                $circuito,
                $yy,
            ]);

            $serials[$groupKey] = ($serials[$groupKey] ?? 0) + 1;
            $newCode = sprintf('%s-%s%03d', $prefix, $yy, $serials[$groupKey]);
            $oldCode = (string) ($row->scheda ?? '');

            if ($oldCode !== $newCode) {
                $changes[] = [
                    'id' => $row->id,
                    'struttura_id' => $row->struttura_id,
                    'circuito' => $circuito,
                    'old' => $oldCode,
                    'new' => $newCode,
                ];
            }
        }

        if (empty($changes)) {
            $this->info('La numerazione schedine è già coerente. Nessuna modifica necessaria.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Record da rinumerare: %d', count($changes)));
        $this->table(
            ['ID', 'Struttura', 'Circuito', 'Codice attuale', 'Nuovo codice'],
            array_slice($changes, 0, 25)
        );

        if (! $this->option('apply')) {
            $this->comment('Anteprima completata. Esegui con --apply per salvare le modifiche.');
            return self::SUCCESS;
        }

        $updated = 0;
        foreach ($changes as $change) {
            Schedina::query()
                ->withoutGlobalScopes()
                ->whereKey($change['id'])
                ->update(['scheda' => $change['new']]);
            $updated++;
        }

        $this->info(sprintf('Rinumerazione completata. Record aggiornati: %d', $updated));

        return self::SUCCESS;
    }

    private function normalizeSchedaCircuit(Schedina $schedina): string
    {
        $circuito = trim((string) ($schedina->circuito ?? ''));
        if ($circuito !== '') {
            return $this->normalizeCircuitName($circuito);
        }

        if ((bool) ($schedina->is_arrive ?? false)) {
            return 'arrivi';
        }

        return trim((string) ($schedina->scheda ?? '')) === '' ? 'bozza' : 'schedina';
    }

    private function normalizeCircuitName(string $circuito): string
    {
        $value = Str::of($circuito)->trim()->lower()->value();

        return match ($value) {
            'arrivo', 'arrivi', 'to_arrivi' => 'arrivi',
            'web', 'web-checkin', 'web_checkin' => 'web',
            'bozza', 'draft' => 'bozza',
            default => 'schedina',
        };
    }

    private function circuitCodePrefix(string $circuito): string
    {
        return match ($circuito) {
            'arrivi' => 'A',
            'web' => 'W',
            default => 'S',
        };
    }

    private function codeYearForRecord(Schedina $row): string
    {
        try {
            return ($row->arrive ? Carbon::parse($row->arrive) : Carbon::parse($row->created_at))->format('y');
        } catch (\Throwable $e) {
            return now()->format('y');
        }
    }
}
