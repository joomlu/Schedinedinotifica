<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoRilasciatoSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'CI'   => 'Comune',
            'CARTAID' => 'Comune',
            'PASS' => 'Questura',
            'PAT'  => 'Motorizzazione Civile',
            'PERM' => 'Questura',
        ];

        $tutti = TipoDocumento::all();

        foreach ($tutti as $tipo) {
            $codice = strtoupper($tipo->codice);
            if (isset($map[$codice])) {
                $tipo->rilasciato_da = $map[$codice];
            } else {
                $desc = strtolower($tipo->descrizione);
                if (str_contains($desc, 'carta')) {
                    $tipo->rilasciato_da = 'Comune';
                } elseif (str_contains($desc, 'passaporto')) {
                    $tipo->rilasciato_da = 'Questura';
                } elseif (str_contains($desc, 'patente')) {
                    $tipo->rilasciato_da = 'Motorizzazione Civile';
                } elseif (str_contains($desc, 'permesso')) {
                    $tipo->rilasciato_da = 'Questura';
                } else {
                    $tipo->rilasciato_da = 'Comune';
                }
            }
            $tipo->save();
        }
    }
}
