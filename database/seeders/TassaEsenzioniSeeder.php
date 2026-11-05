<?php

namespace Database\Seeders;

use App\Models\Struttura;
use App\Models\TassaEsenzione;
use Illuminate\Database\Seeder;

class TassaEsenzioniSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['codice' => 'E01', 'descrizione' => 'Minori fino a 14 anni', 'richiede_nota' => false, 'ordine' => 10],
            ['codice' => 'E02', 'descrizione' => 'Disabili e accompagnatore', 'richiede_nota' => true, 'ordine' => 20],
            ['codice' => 'E03', 'descrizione' => 'Forze dell’ordine in servizio', 'richiede_nota' => false, 'ordine' => 30],
            ['codice' => 'E04', 'descrizione' => 'Autista di gruppo', 'richiede_nota' => true, 'ordine' => 40],
            ['codice' => 'E05', 'descrizione' => 'Accompagnatore turistico', 'richiede_nota' => true, 'ordine' => 50],
            ['codice' => 'E06', 'descrizione' => 'Residenti nel comune', 'richiede_nota' => false, 'ordine' => 60],
        ];

        $strutture = Struttura::all(['id']);

        foreach ($strutture as $struttura) {
            foreach ($defaults as $row) {
                TassaEsenzione::updateOrCreate(
                    ['struttura_id' => $struttura->id, 'codice' => $row['codice']],
                    [
                        'descrizione' => $row['descrizione'],
                        'richiede_nota' => $row['richiede_nota'],
                        'ordine' => $row['ordine'],
                        'attivo' => true,
                    ]
                );
            }
        }
    }
}
