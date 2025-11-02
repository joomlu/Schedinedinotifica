<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeStreetSeeder extends Seeder
{
    public function run(): void
    {
        // Se la tabella non esiste, non fare nulla
        if (! \Schema::hasTable('typestreet')) {
            return;
        }

        $defaults = [
            // Generici
            'Via', 'Viale', 'Vicolo', 'Piazza', 'Piazzale', 'Largo', 'Corso', 'Strada', 'Stradone', 'Rotonda', 'Rondò',
            // Toponimi diffusi
            'Borgo', 'Borgata', 'Contrada', 'Rione', 'Quartiere', 'Località', 'Frazione', 'Case Sparse',
            // Percorsi/parti
            'Traversa', 'Trav', 'Passaggio', 'Passerella', 'Galleria', 'Sottopasso', 'Sovrappasso', 'Scalinata', 'Rampa', 'Salita', 'Discesa', 'Belvedere', 'Bastioni',
            // Lungo fiumi/mari
            'Lungomare', 'Lungarno', 'Lungotevere', 'Lungolago', 'Lungo Po', 'Riva', 'Riviera', 'Banchina', 'Calata', 'Darsena', 'Molo',
            // Venezia e simili
            'Calle', 'Corte', 'Campo', 'Campiello', 'Fondamenta', 'Rio Terà', 'Ruga', 'Lista',
            // Strade di classe
            'Tangenziale', 'Circonvallazione', 'Superstrada', 'Autostrada', 'Statale', 'Regionale', 'Provinciale', 'Comunale', 'Vicinale',
            // Altri
            'Argine', 'Costa', 'Colle', 'Parco', 'Giardino', 'Spalto', 'Alzaia',
        ];
        $existing = DB::table('typestreet')->pluck('name')->map(function ($v) {
            return mb_strtolower($v);
        })->toArray();
        $toInsert = [];
        foreach ($defaults as $name) {
            if (! in_array(mb_strtolower($name), $existing, true)) {
                $toInsert[] = ['name' => $name, 'created_at' => now(), 'updated_at' => now()];
            }
        }
        if (! empty($toInsert)) {
            DB::table('typestreet')->insert($toInsert);
        }
    }
}
