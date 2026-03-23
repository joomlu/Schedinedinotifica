<?php

namespace Database\Seeders;

use App\Models\TipoCliente;
use Illuminate\Database\Seeder;

class TipoClienteSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codice' => 'OSPITE', 'descrizione' => 'Ospite'],
            ['codice' => 'COMPONENTE', 'descrizione' => 'Componente'],
            ['codice' => 'RICHIESTA', 'descrizione' => 'Richiesta'],
        ];

        foreach ($rows as $row) {
            TipoCliente::updateOrCreate(
                ['codice' => $row['codice']],
                [
                    'descrizione' => $row['descrizione'],
                    'attivo' => true,
                ]
            );
        }
    }
}

