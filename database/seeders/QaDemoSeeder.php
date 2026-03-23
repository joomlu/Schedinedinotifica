<?php

namespace Database\Seeders;

use App\Models\Customers;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('QA Demo: crea strutture, utenti e dati base');

        $struttura1 = Struttura::firstOrCreate(
            ['id' => 1],
            [
                'nome_struttura' => 'Hotel K2 Demo',
                'citta' => 'Demo City',
                'provincia' => 'DC',
                'attiva' => true,
                'scadenza_servizio' => now()->addMonths(6)->toDateString(),
                'piano' => 'demo',
                'stato_pagamento' => 'ok',
            ]
        );

        $struttura2 = Struttura::firstOrCreate(
            ['id' => 2],
            [
                'nome_struttura' => 'Hotel Beta Demo',
                'citta' => 'Beta City',
                'provincia' => 'BC',
                'attiva' => true,
                'scadenza_servizio' => now()->addMonths(6)->toDateString(),
                'piano' => 'demo',
                'stato_pagamento' => 'ok',
            ]
        );

        $this->seedUsers($struttura1);
        $this->seedRecords($struttura1, $struttura2);
    }

    protected function seedUsers(Struttura $struttura1): void
    {
        $users = [
            ['email' => 'tanggo@schedinedinotifica.test', 'name' => 'Super Admin', 'ruolo' => 'super_admin', 'struttura_id' => null, 'proprietario_id' => null],
            ['email' => 'admin@schedinedinotifica.test', 'name' => 'Admin Demo', 'ruolo' => 'admin', 'struttura_id' => null, 'proprietario_id' => null],
            ['email' => 'proprietario@schedinedinotifica.test', 'name' => 'Proprietario Demo', 'ruolo' => 'proprietario', 'struttura_id' => null, 'proprietario_id' => 1],
            ['email' => 'hotelK2@schedinedinotifica.test', 'name' => 'Struttura User Demo', 'ruolo' => 'struttura_user', 'struttura_id' => $struttura1->id, 'proprietario_id' => null],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('Passw0rd!'),
                    'avatar' => '',
                    'ruolo' => $u['ruolo'],
                    'struttura_id' => $u['struttura_id'],
                    'proprietario_id' => $u['proprietario_id'],
                ]
            );
        }
    }

    protected function seedRecords(Struttura $struttura1, Struttura $struttura2): void
    {
        if (Schema::hasTable('clienti')) {
            $this->seedClientePerStruttura($struttura1->id, 'Mario', 'Rossi');
            $this->seedClientePerStruttura($struttura2->id, 'Anna', 'Bianchi');
        }

        if (Schema::hasTable('schedina')) {
            $this->seedSchedinaPerStruttura($struttura1->id, 'Mario', 'Rossi');
            $this->seedSchedinaPerStruttura($struttura2->id, 'Anna', 'Bianchi');
        }
    }

    protected function seedClientePerStruttura(int $strutturaId, string $name, string $surname): void
    {
        try {
            Customers::firstOrCreate(
                ['email' => Str::lower($name) . '.' . Str::lower($surname) . '@qa.test', 'struttura_id' => $strutturaId],
                [
                    'name' => $name,
                    'surname' => $surname,
                    'sex' => 'M',
                    'type' => 'MR',
                    'group' => 'QA',
                    'city' => 'Demo City',
                    'region' => 'Demo',
                    'province' => 'DC',
                    'country' => 'Italia',
                ]
            );
        } catch (\Throwable $e) {
            $this->command?->warn('Skip cliente per struttura ' . $strutturaId . ': ' . $e->getMessage());
        }
    }

    protected function seedSchedinaPerStruttura(int $strutturaId, string $name, string $surname): void
    {
        try {
            Schedina::firstOrCreate(
                ['scheda' => 'QA-' . $strutturaId],
                [
                    'type' => 'MR',
                    'name' => $name,
                    'surname' => $surname,
                    'sex' => 'M',
                    'relationship' => 'OSPITE SINGOLO',
                    'exent' => 'NO',
                    'arrive' => '01/01/2026',
                    'departure' => '02/01/2026',
                    'cant_people' => 1,
                    'room' => '1',
                    'beds' => 1,
                    'observation' => 'QA demo',
                    'oa_country' => 'Italia',
                    'oa_city' => 'Demo City',
                    'oa_region' => 'Demo',
                    'oa_prov' => 'DC',
                    'or_country' => 'Italia',
                    'or_city' => 'Demo City',
                    'or_region' => 'Demo',
                    'or_prov' => 'DC',
                    'or_cap' => '00000',
                    'or_typeaway' => 'Via',
                    'or_address' => 'QA',
                    'or_num' => '1',
                    'or_doc' => 'DOC123',
                    'or_doctype' => 'PASSAPORTO',
                    'scheda' => 'QA-' . $strutturaId,
                    'is_arrive' => 0,
                    'struttura_id' => $strutturaId,
                ]
            );
        } catch (\Throwable $e) {
            $this->command?->warn('Skip schedina per struttura ' . $strutturaId . ': ' . $e->getMessage());
        }
    }
}
