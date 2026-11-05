<?php

namespace Database\Seeders;

use App\Models\Componenti;
use App\Models\Customers;
use App\Models\Schedina;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompleteDemoSchedineSeeder extends Seeder
{
    public function run(): void
    {
        $structures = Struttura::query()
            ->where(function ($query) {
                $query->where('email', 'like', '%@schedinedinotifica.test')
                    ->orWhere('nome_struttura', 'like', '%Demo%')
                    ->orWhereIn('nome_struttura', ['Hotel K2', 'Hotel Aurora', 'Residence Mare', 'B&B Pinzon', 'Hotel Tibidabo', 'Hotel Alexandra']);
            })
            ->orderBy('id')
            ->get();

        if ($structures->isEmpty()) {
            $this->command?->warn('Nessuna struttura demo trovata: seed schedine complete saltato.');
            return;
        }

        $profiles = $this->profiles();

        foreach ($structures as $structure) {
            foreach ($profiles as $index => $profile) {
                $this->seedProfileForStructure($structure, $profile, $index + 1);
            }
        }

        $this->command?->info('Schedine demo complete create/aggiornate con dati plausibili e componenti.');
    }

    private function seedProfileForStructure(Struttura $structure, array $profile, int $position): void
    {
        $customer = Customers::query()->updateOrCreate(
            [
                'struttura_id' => $structure->id,
                'email' => $profile['customer']['email'],
            ],
            $profile['customer'] + [
                'struttura_id' => $structure->id,
            ]
        );

        $schedaCode = sprintf('DEMO-%02d-%02d', $structure->id, $position);
        $payload = $profile['schedina'] + [
            'struttura_id' => $structure->id,
            'customer_id' => $customer->id,
            'scheda' => $schedaCode,
            'circuito' => 'schedina',
            'is_arrive' => 0,
        ];

        $schedina = Schedina::query()->updateOrCreate(
            [
                'struttura_id' => $structure->id,
                'scheda' => $schedaCode,
            ],
            $payload
        );

        Componenti::query()->where('schedina_id', $schedina->id)->delete();
        foreach ($profile['componenti'] as $component) {
            Componenti::query()->create($component + [
                'schedina_id' => $schedina->id,
                'struttura_id' => $structure->id,
                'customer_id' => $customer->id,
            ]);
        }
    }

    private function profiles(): array
    {
        return [
            [
                'customer' => [
                    'name' => 'Marco',
                    'surname' => 'Bellariva',
                    'sex' => 'M',
                    'type_housed' => 'Ospite',
                    'type' => 'Sig.',
                    'country' => 'ITALIA',
                    'city' => 'Bellaria-Igea Marina',
                    'region' => 'Emilia-Romagna',
                    'province' => 'RN',
                    'cap' => '47814',
                    'typeaway' => 'Via',
                    'address' => 'Via Alfonso Pinzon',
                    'number' => '145',
                    'email' => 'marco.bellariva.demo@schedinedinotifica.test',
                    'phone' => '+39 0541 100101',
                    'cellphone' => '+39 333 1001001',
                    'country_reg' => 'ITALIA',
                    'region_reg' => 'Emilia-Romagna',
                    'city_reg' => 'Rimini',
                    'prov_reg' => 'RN',
                    'cap_reg' => '47921',
                    'ciudadania_reg' => 'ITALIANA',
                    'nac_reg' => '1986-03-14',
                    'type_doc_reg' => 'CARTA DI IDENTITA\'',
                    'num_doc_reg' => 'CA1234567',
                    'date_pub_reg' => '2021-02-11',
                    'expire_reg' => '2031-02-10',
                    'rilasciato_reg' => 'Comune di Rimini',
                    'country_doc_reg' => 'ITALIA',
                    'city_doc_reg' => 'Rimini',
                ],
                'schedina' => [
                    'type' => 'Sig.',
                    'name' => 'Marco',
                    'surname' => 'Bellariva',
                    'sex' => 'M',
                    'relationship' => 'CAPO FAMIGLIA',
                    'exent' => 'NO',
                    'arrive' => '2026-07-11',
                    'departure' => '2026-07-18',
                    'cant_people' => 3,
                    'room' => 2,
                    'beds' => 3,
                    'observation' => 'Famiglia demo completa per validazione report.',
                    'customer_type_housed' => 'Ospite',
                    'oa_country' => 'ITALIA',
                    'oa_city' => 'Rimini',
                    'oa_region' => 'Emilia-Romagna',
                    'oa_prov' => 'RN',
                    'oa_cap' => '47921',
                    'oa_city_nac' => 'ITALIANA',
                    'oa_date_nac' => '1986-03-14',
                    'or_country' => 'ITALIA',
                    'or_city' => 'Bellaria-Igea Marina',
                    'or_region' => 'Emilia-Romagna',
                    'or_prov' => 'RN',
                    'or_cap' => '47814',
                    'or_typeaway' => 'Via',
                    'or_address' => 'Via Alfonso Pinzon',
                    'or_num' => '145',
                    'or_doc' => 'CA1234567',
                    'or_doctype' => 'CARTA DI IDENTITA\'',
                    'or_published_date' => '2021-02-11',
                    'or_expire' => '2031-02-10',
                    'or_published' => 'Comune di Rimini',
                    'or_published_country' => 'ITALIA',
                    'or_published_city' => 'Rimini',
                    'customer_email' => 'marco.bellariva.demo@schedinedinotifica.test',
                    'customer_phone' => '+39 0541 100101',
                    'customer_cellphone' => '+39 333 1001001',
                    'id_prenotazione_esterna' => 'BK-260711-001',
                    'istat_tipo_turismo' => 'LEISURE',
                    'istat_mezzo_trasporto' => 'AUTO',
                    'istat_canale_prenotazione' => 'DIRECT',
                    'istat_titolo_studio' => 'LAUREA',
                    'istat_professione' => 'Impiegato',
                    'istat_non_turista' => false,
                ],
                'componenti' => [
                    [
                        'name' => 'Giulia',
                        'surname' => 'Bellariva',
                        'sex' => 'F',
                        'relationship' => 'Coniuge',
                        'exent' => 'NO',
                        'country_nac' => 'ITALIA',
                        'regione_nac' => 'Emilia-Romagna',
                        'province_nac' => 'RN',
                        'city_nac' => 'Rimini',
                        'date_nac' => '1988-05-03',
                        'country' => 'ITALIA',
                        'regione' => 'Emilia-Romagna',
                        'province' => 'RN',
                        'city' => 'Bellaria-Igea Marina',
                        'cap' => '47814',
                        'typeaway' => 'Via',
                        'address' => 'Via Alfonso Pinzon',
                        'number' => '145',
                    ],
                    [
                        'name' => 'Luca',
                        'surname' => 'Bellariva',
                        'sex' => 'M',
                        'relationship' => 'Figlio',
                        'exent' => 'NO',
                        'country_nac' => 'ITALIA',
                        'regione_nac' => 'Emilia-Romagna',
                        'province_nac' => 'RN',
                        'city_nac' => 'Rimini',
                        'date_nac' => '2014-09-18',
                        'country' => 'ITALIA',
                        'regione' => 'Emilia-Romagna',
                        'province' => 'RN',
                        'city' => 'Bellaria-Igea Marina',
                        'cap' => '47814',
                        'typeaway' => 'Via',
                        'address' => 'Via Alfonso Pinzon',
                        'number' => '145',
                    ],
                ],
            ],
            [
                'customer' => [
                    'name' => 'Sophie',
                    'surname' => 'Dubois',
                    'sex' => 'F',
                    'type_housed' => 'Ospite',
                    'type' => 'Sig.ra',
                    'country' => 'FRANCIA',
                    'city' => 'Lione',
                    'region' => 'Auvergne-Rhone-Alpes',
                    'province' => 'LY',
                    'cap' => '69000',
                    'typeaway' => 'Rue',
                    'address' => 'Rue Victor Hugo',
                    'number' => '18',
                    'email' => 'sophie.dubois.demo@schedinedinotifica.test',
                    'phone' => '+33 4 72001010',
                    'cellphone' => '+33 6 12001010',
                    'country_reg' => 'FRANCIA',
                    'region_reg' => 'Auvergne-Rhone-Alpes',
                    'city_reg' => 'Lyon',
                    'prov_reg' => 'LY',
                    'cap_reg' => '69000',
                    'ciudadania_reg' => 'FRANCESE',
                    'nac_reg' => '1991-11-08',
                    'type_doc_reg' => 'PASSAPORTO',
                    'num_doc_reg' => 'FR9988776',
                    'date_pub_reg' => '2020-06-21',
                    'expire_reg' => '2030-06-20',
                    'rilasciato_reg' => 'Prefecture de Lyon',
                    'country_doc_reg' => 'FRANCIA',
                    'city_doc_reg' => 'Lyon',
                ],
                'schedina' => [
                    'type' => 'Sig.ra',
                    'name' => 'Sophie',
                    'surname' => 'Dubois',
                    'sex' => 'F',
                    'relationship' => 'CAPO GRUPPO',
                    'exent' => 'NO',
                    'arrive' => '2026-08-02',
                    'departure' => '2026-08-09',
                    'cant_people' => 2,
                    'room' => 1,
                    'beds' => 2,
                    'observation' => 'Coppia straniera demo per test provenienze estere.',
                    'customer_type_housed' => 'Ospite',
                    'oa_country' => 'FRANCIA',
                    'oa_city' => 'Lyon',
                    'oa_region' => 'Auvergne-Rhone-Alpes',
                    'oa_prov' => 'LY',
                    'oa_cap' => '69000',
                    'oa_city_nac' => 'FRANCESE',
                    'oa_date_nac' => '1991-11-08',
                    'or_country' => 'FRANCIA',
                    'or_city' => 'Lyon',
                    'or_region' => 'Auvergne-Rhone-Alpes',
                    'or_prov' => 'LY',
                    'or_cap' => '69000',
                    'or_typeaway' => 'Rue',
                    'or_address' => 'Rue Victor Hugo',
                    'or_num' => '18',
                    'or_doc' => 'FR9988776',
                    'or_doctype' => 'PASSAPORTO',
                    'or_published_date' => '2020-06-21',
                    'or_expire' => '2030-06-20',
                    'or_published' => 'Prefecture de Lyon',
                    'or_published_country' => 'FRANCIA',
                    'or_published_city' => 'Lyon',
                    'customer_email' => 'sophie.dubois.demo@schedinedinotifica.test',
                    'customer_phone' => '+33 4 72001010',
                    'customer_cellphone' => '+33 6 12001010',
                    'id_prenotazione_esterna' => 'OTA-260802-014',
                    'istat_tipo_turismo' => 'LEISURE',
                    'istat_mezzo_trasporto' => 'AEREO',
                    'istat_canale_prenotazione' => 'OTA',
                    'istat_titolo_studio' => 'LAUREA',
                    'istat_professione' => 'Architetto',
                    'istat_non_turista' => false,
                ],
                'componenti' => [
                    [
                        'name' => 'Thomas',
                        'surname' => 'Dubois',
                        'sex' => 'M',
                        'relationship' => 'Partner',
                        'exent' => 'NO',
                        'country_nac' => 'FRANCIA',
                        'regione_nac' => 'Auvergne-Rhone-Alpes',
                        'province_nac' => 'LY',
                        'city_nac' => 'Lyon',
                        'date_nac' => '1989-01-22',
                        'country' => 'FRANCIA',
                        'regione' => 'Auvergne-Rhone-Alpes',
                        'province' => 'LY',
                        'city' => 'Lyon',
                        'cap' => '69000',
                        'typeaway' => 'Rue',
                        'address' => 'Rue Victor Hugo',
                        'number' => '18',
                    ],
                ],
            ],
            [
                'customer' => [
                    'name' => 'Andrei',
                    'surname' => 'Popescu',
                    'sex' => 'M',
                    'type_housed' => 'Ospite',
                    'type' => 'Sig.',
                    'country' => 'ROMANIA',
                    'city' => 'Bucarest',
                    'region' => 'Bucuresti',
                    'province' => 'B',
                    'cap' => '010101',
                    'typeaway' => 'Strada',
                    'address' => 'Strada Viitorului',
                    'number' => '9',
                    'email' => 'andrei.popescu.demo@schedinedinotifica.test',
                    'phone' => '+40 21 3300101',
                    'cellphone' => '+40 722 010101',
                    'country_reg' => 'ROMANIA',
                    'region_reg' => 'Bucuresti',
                    'city_reg' => 'Bucarest',
                    'prov_reg' => 'B',
                    'cap_reg' => '010101',
                    'ciudadania_reg' => 'RUMENA',
                    'nac_reg' => '1979-07-19',
                    'type_doc_reg' => 'PASSAPORTO',
                    'num_doc_reg' => 'RO5566778',
                    'date_pub_reg' => '2019-03-07',
                    'expire_reg' => '2029-03-06',
                    'rilasciato_reg' => 'Bucuresti',
                    'country_doc_reg' => 'ROMANIA',
                    'city_doc_reg' => 'Bucarest',
                ],
                'schedina' => [
                    'type' => 'Sig.',
                    'name' => 'Andrei',
                    'surname' => 'Popescu',
                    'sex' => 'M',
                    'relationship' => 'OSPITE SINGOLO',
                    'exent' => 'NO',
                    'arrive' => '2026-09-15',
                    'departure' => '2026-09-22',
                    'cant_people' => 1,
                    'room' => 1,
                    'beds' => 1,
                    'observation' => 'Ospite business demo per controlli XML e statistica.',
                    'customer_type_housed' => 'Ospite',
                    'oa_country' => 'ROMANIA',
                    'oa_city' => 'Bucarest',
                    'oa_region' => 'Bucuresti',
                    'oa_prov' => 'B',
                    'oa_cap' => '010101',
                    'oa_city_nac' => 'RUMENA',
                    'oa_date_nac' => '1979-07-19',
                    'or_country' => 'ROMANIA',
                    'or_city' => 'Bucarest',
                    'or_region' => 'Bucuresti',
                    'or_prov' => 'B',
                    'or_cap' => '010101',
                    'or_typeaway' => 'Strada',
                    'or_address' => 'Strada Viitorului',
                    'or_num' => '9',
                    'or_doc' => 'RO5566778',
                    'or_doctype' => 'PASSAPORTO',
                    'or_published_date' => '2019-03-07',
                    'or_expire' => '2029-03-06',
                    'or_published' => 'Bucuresti',
                    'or_published_country' => 'ROMANIA',
                    'or_published_city' => 'Bucarest',
                    'customer_email' => 'andrei.popescu.demo@schedinedinotifica.test',
                    'customer_phone' => '+40 21 3300101',
                    'customer_cellphone' => '+40 722 010101',
                    'id_prenotazione_esterna' => 'CORP-260915-221',
                    'istat_tipo_turismo' => 'BUSINESS',
                    'istat_mezzo_trasporto' => 'AUTO',
                    'istat_canale_prenotazione' => 'AGENCY',
                    'istat_titolo_studio' => 'DIPLOMA',
                    'istat_professione' => 'Consulente',
                    'istat_non_turista' => false,
                ],
                'componenti' => [],
            ],
        ];
    }
}
