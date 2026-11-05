<?php

namespace Database\Seeders;

use App\Models\Customers;
use App\Models\Schedina;
use App\Models\Struttura;
use Illuminate\Database\Seeder;

class QaSmokeMinSeeder extends Seeder
{
    public function run(): void
    {
        $struttura = Struttura::query()->orderBy('id')->first();
        if (! $struttura) {
            $this->command?->warn('Nessuna struttura trovata: seed QA smoke saltato.');
            return;
        }

        $customer = Customers::query()->updateOrCreate(
            [
                'struttura_id' => $struttura->id,
                'name' => 'QA',
                'surname' => 'Smoke',
            ],
            [
                'type_housed' => 'Ospite',
                'sex' => 'M',
                'email' => 'qa.smoke@schedinedinotifica.test',
                'country' => 'ITALIA',
                'city' => 'Bellaria-Igea Marina',
            ]
        );

        $scheda = '999-' . date('Y');

        Schedina::query()->updateOrCreate(
            [
                'struttura_id' => $struttura->id,
                'scheda' => $scheda,
            ],
            [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'surname' => $customer->surname,
                'type' => 'Sig.',
                'relationship' => 'OSPITE SINGOLO',
                'exent' => 0,
                'is_arrive' => 0,
            ]
        );

        $this->command?->info('QA smoke data pronta (cliente + schedina).');
    }
}
