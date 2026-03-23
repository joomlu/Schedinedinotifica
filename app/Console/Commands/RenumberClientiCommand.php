<?php

namespace App\Console\Commands;

use App\Models\Customers;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RenumberClientiCommand extends Command
{
    protected $signature = 'clienti:renumber
        {--apply : Applica la rinumerazione}
        {--struttura= : Limita la rinumerazione a una struttura specifica}';

    protected $description = 'Rinumerazione controllata dei codici cliente per struttura, tipo e anno';

    public function handle(): int
    {
        $query = Customers::query()
            ->withoutGlobalScopes()
            ->orderBy('struttura_id')
            ->orderBy('created_at')
            ->orderBy('id');

        if ($this->option('struttura')) {
            $query->where('struttura_id', (int) $this->option('struttura'));
        }

        /** @var \Illuminate\Support\Collection<int, Customers> $customers */
        $customers = $query->get();

        if ($customers->isEmpty()) {
            $this->warn('Nessun cliente trovato per la rinumerazione.');
            return self::SUCCESS;
        }

        $serials = [];
        $changes = [];

        foreach ($customers as $customer) {
            $prefix = $this->prefixByTipoCliente($customer->type_housed);
            $yearTwoDigits = $this->yearToken($customer);
            $groupKey = implode('|', [
                (string) ($customer->struttura_id ?? 0),
                $prefix,
                $yearTwoDigits,
            ]);

            $serials[$groupKey] = ($serials[$groupKey] ?? 0) + 1;
            $newCode = sprintf('%s-%s-%04d', $prefix, $yearTwoDigits, $serials[$groupKey]);
            $oldCode = (string) ($customer->numero_cliente ?? '');

            if ($oldCode !== $newCode) {
                $changes[] = [
                    'id' => $customer->id,
                    'struttura_id' => $customer->struttura_id,
                    'tipo' => $customer->type_housed,
                    'old' => $oldCode,
                    'new' => $newCode,
                ];
            }
        }

        if (empty($changes)) {
            $this->info('La numerazione clienti è già coerente. Nessuna modifica necessaria.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Clienti da rinumerare: %d', count($changes)));
        $this->table(
            ['ID', 'Struttura', 'Tipo', 'Codice attuale', 'Nuovo codice'],
            array_slice($changes, 0, 20)
        );

        if (! $this->option('apply')) {
            $this->comment('Anteprima completata. Esegui con --apply per salvare le modifiche.');
            return self::SUCCESS;
        }

        $updated = 0;
        foreach ($changes as $change) {
            Customers::withoutGlobalScopes()
                ->whereKey($change['id'])
                ->update(['numero_cliente' => $change['new']]);
            $updated++;
        }

        $this->info(sprintf('Rinumerazione completata. Clienti aggiornati: %d', $updated));

        return self::SUCCESS;
    }

    private function prefixByTipoCliente(?string $tipoCliente): string
    {
        return match (trim((string) $tipoCliente)) {
            'Richiesta' => 'R',
            'Componente' => 'C',
            default => 'O',
        };
    }

    private function yearToken(Customers $customer): string
    {
        $date = $customer->created_at ?: $customer->updated_at ?: now();

        if ($date instanceof Carbon) {
            return $date->format('y');
        }

        return Carbon::parse($date)->format('y');
    }
}
