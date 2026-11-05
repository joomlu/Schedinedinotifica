<?php

namespace Database\Seeders;

use App\Models\Struttura;
use App\Models\TassaEsenzione;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TassaEsenzioniBellariaSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['codice' => '400', 'descrizione' => 'Minori fino al compimento del 18° anno di età', 'richiede_nota' => false, 'ordine' => 10],
            ['codice' => '405', 'descrizione' => 'Soggetti in terapia e accompagnatori', 'richiede_nota' => true, 'ordine' => 20],
            ['codice' => '410', 'descrizione' => 'Soggetti invalidi e accompagnatore', 'richiede_nota' => true, 'ordine' => 30],
            ['codice' => '415', 'descrizione' => 'Volontari in eventi organizzati o di emergenza', 'richiede_nota' => true, 'ordine' => 40],
            ['codice' => '420', 'descrizione' => 'Soggetti coinvolti in eventi calamitosi o di emergenza', 'richiede_nota' => true, 'ordine' => 50],
            ['codice' => '425', 'descrizione' => 'Autisti di pullman e accompagnatori turistici', 'richiede_nota' => false, 'ordine' => 60],
            ['codice' => '430', 'descrizione' => 'Personale dipendente della struttura ricettiva', 'richiede_nota' => false, 'ordine' => 70],
            ['codice' => '440', 'descrizione' => 'Forze Armate e Vigili del Fuoco in servizio', 'richiede_nota' => true, 'ordine' => 80],
            ['codice' => '450', 'descrizione' => 'Familiari del gestore se anagraficamente conviventi', 'richiede_nota' => true, 'ordine' => 85],
        ];

        $cityColumn = null;
        if (Schema::hasColumn('struttura', 'citta')) {
            $cityColumn = 'citta';
        } elseif (Schema::hasColumn('struttura', 'città')) {
            $cityColumn = 'città';
        }

        if (!$cityColumn) {
            return;
        }

        $strutture = Struttura::whereRaw('LOWER(`' . $cityColumn . '`) = ?', ['bellaria-igea marina'])->get(['id']);

        foreach ($strutture as $struttura) {
            TassaEsenzione::where('struttura_id', $struttura->id)->where('codice', '777')->delete();

            foreach ($records as $row) {
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
