<?php

namespace Database\Seeders;

use App\Models\Customers;
use Illuminate\Database\Seeder;

class BackfillNumeroClienteSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Customers::query()
            ->withoutGlobalScopes()
            ->select('struttura_id')
            ->distinct()
            ->pluck('struttura_id');

        foreach ($groups as $strutturaId) {
            $counters = [];

            $customers = Customers::query()
                ->withoutGlobalScopes()
                ->where('struttura_id', $strutturaId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            foreach ($customers as $customer) {
                if (!empty($customer->numero_cliente)) {
                    continue;
                }

                $prefix = $this->prefixByTipo((string) $customer->type_housed);
                $yy = optional($customer->created_at)->format('y') ?: now()->format('y');
                $key = $prefix . '-' . $yy;

                $counters[$key] = ($counters[$key] ?? 0) + 1;
                $customer->numero_cliente = sprintf('%s-%s-%04d', $prefix, $yy, $counters[$key]);
                $customer->save();
            }
        }

        $this->command?->info('Backfill numero_cliente completato.');
    }

    private function prefixByTipo(string $tipo): string
    {
        $t = mb_strtolower(trim($tipo));
        return match ($t) {
            'richiesta' => 'R',
            'componente' => 'C',
            default => 'O',
        };
    }
}
