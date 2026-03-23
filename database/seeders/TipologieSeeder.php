<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TipologieSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanDuplicates();
        $now = now();

        $tipologieGenerali = [
            "Alberghiera",
            "Extra-alberghiera",
            "All'aperto",
        ];

        foreach ($tipologieGenerali as $nome) {
            DB::table('tipologie_generali')->updateOrInsert(
                ['nome' => $nome],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $tipologieStruttura = [
            'Alberghiera' => [
                'Hotel',
                'Residenza turistico-alberghiera (RTA)',
                'Albergo diffuso',
                'Villaggio-albergo',
                'Condohotel',
                'Motel',
                'Meuble / Garni',
                'Resort',
            ],
            'Extra-alberghiera' => [
                'Agriturismo',
                'Affittacamere',
                'Bed & Breakfast',
                'Case e appartamenti per vacanze (CAV)',
                'Locazione turistica imprenditoriale',
                'Ostello',
                'Casa per ferie',
                'Rifugio alpino',
                'Rifugio escursionistico',
            ],
            "All'aperto" => [
                'Campeggio',
                'Villaggio turistico',
                'Camping village',
                'Area di sosta camper',
                'Marina resort',
            ],
        ];

        $classificazioni = [];
        // Catalogo base nazionale/regionale (armonizzato): la normativa di dettaglio resta regionale.
        $stelle = ['1 stella', '2 stelle', '3 stelle', '3 stelle superior', '4 stelle', '4 stelle superior', '5 stelle', '5 stelle lusso'];
        foreach (['Hotel', 'Albergo diffuso', 'Villaggio-albergo', 'Meuble / Garni', 'Resort', 'Condohotel', 'Motel'] as $nome) {
            $classificazioni[$nome] = $stelle;
        }
        $classificazioni['Residenza turistico-alberghiera (RTA)'] = ['2 stelle', '3 stelle', '4 stelle'];

        // Agriturismo: standard nazionale "girasoli" + compatibilita legacy "spighe"
        $classificazioni['Agriturismo'] = [
            '1 girasole', '2 girasoli', '3 girasoli', '4 girasoli', '5 girasoli',
            '1 spiga', '2 spighe', '3 spighe', '4 spighe', '5 spighe',
        ];
        $classificazioni['Campeggio'] = ['1 stella', '2 stelle', '3 stelle', '4 stelle', '5 stelle'];
        $classificazioni['Villaggio turistico'] = ['1 stella', '2 stelle', '3 stelle', '4 stelle', '5 stelle'];
        $classificazioni['Camping village'] = ['1 stella', '2 stelle', '3 stelle', '4 stelle', '5 stelle'];
        $classificazioni['Marina resort'] = ['1 stella', '2 stelle', '3 stelle', '4 stelle', '5 stelle'];
        $classificazioni['Area di sosta camper'] = ['Nessuna'];

        foreach ([
            'Affittacamere',
            'Bed & Breakfast',
            'Case e appartamenti per vacanze (CAV)',
            'Locazione turistica imprenditoriale',
            'Ostello',
            'Casa per ferie',
            'Rifugio alpino',
            'Rifugio escursionistico',
        ] as $nome) {
            $classificazioni[$nome] = ['Nessuna'];
        }

        $classNomi = [];
        foreach ($classificazioni as $nomi) {
            foreach ($nomi as $cNome) {
                $classNomi[] = $cNome;
            }
        }
        $classNomi = array_values(array_unique($classNomi));

        $classIdByName = [];
        foreach ($classNomi as $cNome) {
            DB::table('classificazioni')->updateOrInsert(
                ['nome' => $cNome],
                ['updated_at' => $now, 'created_at' => $now]
            );
            $classIdByName[$cNome] = DB::table('classificazioni')->where('nome', $cNome)->value('id');
        }

        // Inserimento tipologie struttura + pivot classificazioni
        foreach ($tipologieStruttura as $genName => $items) {
            $genId = DB::table('tipologie_generali')->where('nome', $genName)->value('id');
            if (!$genId) {
                $genId = DB::table('tipologie_generali')->insertGetId([
                    'nome' => $genName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($items as $nome) {
                $tipId = DB::table('tipologie_struttura')->updateOrInsert(
                    ['tipologia_generale_id' => $genId, 'nome' => $nome],
                    ['updated_at' => $now, 'created_at' => $now]
                );
                $tipId = DB::table('tipologie_struttura')->where(['tipologia_generale_id' => $genId, 'nome' => $nome])->value('id');

                $nomiClass = $classificazioni[$nome] ?? ['Nessuna'];
                foreach ($nomiClass as $classNome) {
                    $classId = $classIdByName[$classNome] ?? null;
                    if ($classId) {
                        DB::table('classificazione_tipologia')->updateOrInsert(
                            [
                                'tipologia_struttura_id' => $tipId,
                                'classificazione_id' => $classId,
                            ],
                            [
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }
                }
            }
        }
    }

    private function cleanDuplicates(): void
    {
        // Remove duplicated rows keeping the oldest id per logical key
        DB::statement("DELETE t FROM tipologie_generali t JOIN (SELECT id, ROW_NUMBER() OVER (PARTITION BY LOWER(nome) ORDER BY id) AS rn FROM tipologie_generali) d ON d.id = t.id WHERE d.rn > 1");
        DB::statement("DELETE t FROM tipologie_struttura t JOIN (SELECT id, ROW_NUMBER() OVER (PARTITION BY tipologia_generale_id, LOWER(nome) ORDER BY id) AS rn FROM tipologie_struttura) d ON d.id = t.id WHERE d.rn > 1");
        DB::statement("DELETE c FROM classificazioni c JOIN (SELECT id, ROW_NUMBER() OVER (PARTITION BY LOWER(nome) ORDER BY id) AS rn FROM classificazioni) d ON d.id = c.id WHERE d.rn > 1");
        if (Schema::hasTable('classificazione_tipologia')) {
            DB::statement("DELETE p FROM classificazione_tipologia p JOIN (SELECT id, ROW_NUMBER() OVER (PARTITION BY tipologia_struttura_id, classificazione_id ORDER BY id) AS rn FROM classificazione_tipologia) d ON d.id = p.id WHERE d.rn > 1");
        }
    }
}
