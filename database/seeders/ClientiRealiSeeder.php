<?php

namespace Database\Seeders;

use App\Models\Struttura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientiRealiSeeder extends Seeder
{
    public function run(): void
    {
        $strutturaId = Struttura::query()->orderBy('id')->value('id');
        if (! $strutturaId) {
            $this->command?->warn('Nessuna struttura trovata: seed clienti reali non eseguito.');
            return;
        }

        DB::table('clienti')
            ->where('name', 'QA')
            ->where('surname', 'Smoke')
            ->delete();

        $now = now();

        $records = [
            [
                'struttura_id' => $strutturaId,
                'group' => 'Turismo',
                'subgroup' => 'Weekend',
                'subgroup1' => 'Weekend lungo',
                'sex' => 'M',
                'type_housed' => 'Ospite',
                'type' => 'Sig.',
                'name' => 'Marco',
                'surname' => 'Bianchi',
                'country' => 'ITALIA',
                'city' => 'Rimini',
                'region' => 'Emilia-Romagna',
                'province' => 'RN',
                'cap' => '47921',
                'typeaway' => 'Via',
                'address' => 'Via Tripoli',
                'number' => '85',
                'email' => 'marco.bianchi@example.it',
                'phone' => '+39 0541 100001',
                'cellphone' => '+39 333 1000001',
                'observation' => 'Cliente business ricorrente.',
                'country_reg' => 'ITALIA',
                'city_reg' => 'Rimini',
                'prov_reg' => 'RN',
                'ciudadania_reg' => 'ITALIANA',
                'nac_reg' => '15/04/1984',
                'type_doc_reg' => 'Carta d identita',
                'num_doc_reg' => 'AZ1234567',
                'date_pub_reg' => 'Comune di Rimini',
                'expire_reg' => '01/06/2021',
                'rilasciato_reg' => '01/06/2031',
                'observation_reg' => 'Documento verificato al check-in.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'struttura_id' => $strutturaId,
                'group' => 'Business',
                'subgroup' => 'Fiere',
                'subgroup1' => 'Fiere internazionali',
                'sex' => 'F',
                'type_housed' => 'Ospite',
                'type' => 'Sig.ra',
                'name' => 'Giulia',
                'surname' => 'Rossi',
                'country' => 'ITALIA',
                'city' => 'Milano',
                'region' => 'Lombardia',
                'province' => 'MI',
                'cap' => '20121',
                'typeaway' => 'Corso',
                'address' => 'Corso Buenos Aires',
                'number' => '14',
                'email' => 'giulia.rossi@example.it',
                'phone' => '+39 02 2000001',
                'cellphone' => '+39 334 2000001',
                'observation' => 'Arriva per evento in fiera.',
                'country_reg' => 'ITALIA',
                'city_reg' => 'Milano',
                'prov_reg' => 'MI',
                'ciudadania_reg' => 'ITALIANA',
                'nac_reg' => '28/09/1990',
                'type_doc_reg' => 'Passaporto',
                'num_doc_reg' => 'YA7788990',
                'date_pub_reg' => 'Questura di Milano',
                'expire_reg' => '12/02/2022',
                'rilasciato_reg' => '12/02/2032',
                'observation_reg' => 'Preferisce camera silenziosa.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'struttura_id' => $strutturaId,
                'group' => 'Famiglie',
                'subgroup' => 'Villaggi family',
                'subgroup1' => 'Baby club',
                'sex' => 'M',
                'type_housed' => 'Componente',
                'type' => 'Sig.',
                'name' => 'Luca',
                'surname' => 'Verdi',
                'country' => 'ITALIA',
                'city' => 'Bologna',
                'region' => 'Emilia-Romagna',
                'province' => 'BO',
                'cap' => '40121',
                'typeaway' => 'Via',
                'address' => 'Via Ugo Bassi',
                'number' => '22',
                'email' => 'luca.verdi@example.it',
                'phone' => '+39 051 300001',
                'cellphone' => '+39 335 3000001',
                'observation' => 'Componente nucleo famigliare.',
                'country_reg' => 'ITALIA',
                'city_reg' => 'Bologna',
                'prov_reg' => 'BO',
                'ciudadania_reg' => 'ITALIANA',
                'nac_reg' => '05/01/2012',
                'type_doc_reg' => 'Carta d identita',
                'num_doc_reg' => 'CA5566778',
                'date_pub_reg' => 'Comune di Bologna',
                'expire_reg' => '10/08/2024',
                'rilasciato_reg' => '10/08/2034',
                'observation_reg' => 'Minore accompagnato.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'struttura_id' => $strutturaId,
                'group' => 'Eventi',
                'subgroup' => 'Concerti',
                'subgroup1' => 'Tour musicali',
                'sex' => 'F',
                'type_housed' => 'Richiesta',
                'type' => 'Sig.ra',
                'name' => 'Elena',
                'surname' => 'Marin',
                'country' => 'SLOVENIA',
                'city' => 'Lubiana',
                'region' => 'Osrednjeslovenska',
                'province' => 'SI',
                'cap' => '1000',
                'typeaway' => 'Via',
                'address' => 'Trubarjeva cesta',
                'number' => '6',
                'email' => 'elena.marin@example.si',
                'phone' => '+386 1 5551000',
                'cellphone' => '+386 40 100001',
                'observation' => 'Richiesta preventivo per gruppo evento.',
                'country_reg' => 'SLOVENIA',
                'city_reg' => 'Lubiana',
                'prov_reg' => 'SI',
                'ciudadania_reg' => 'SLOVENA',
                'nac_reg' => '19/07/1988',
                'type_doc_reg' => 'Passaporto',
                'num_doc_reg' => 'SI4433221',
                'date_pub_reg' => 'Ministero Interno Slovenia',
                'expire_reg' => '20/03/2020',
                'rilasciato_reg' => '20/03/2030',
                'observation_reg' => 'In attesa conferma prenotazione.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($records as $record) {
            DB::table('clienti')->updateOrInsert(
                [
                    'struttura_id' => $record['struttura_id'],
                    'email' => $record['email'],
                ],
                $record
            );
        }

        $this->command?->info('Clienti reali caricati (4 record) e clienti QA rimossi.');
    }
}
