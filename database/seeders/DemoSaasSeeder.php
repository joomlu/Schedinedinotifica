<?php

namespace Database\Seeders;

use App\Models\Proprietario;
use App\Models\Struttura;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSaasSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Passw0rd!');

        $superAdmin = User::updateOrCreate(
            ['email' => 'tanggo@schedinedinotifica.test'],
            [
                'name' => 'Super Admin Demo',
                'ruolo' => 'super_admin',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => null,
                'struttura_id' => null,
            ]
        );

        $admin1 = User::updateOrCreate(
            ['email' => 'admin1@schedinedinotifica.test'],
            [
                'name' => 'Admin Uno Demo',
                'ruolo' => 'admin',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => null,
                'struttura_id' => null,
            ]
        );

        $admin2 = User::updateOrCreate(
            ['email' => 'admin2@schedinedinotifica.test'],
            [
                'name' => 'Admin Due Demo',
                'ruolo' => 'admin',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => null,
                'struttura_id' => null,
            ]
        );

        $proprietari = [];
        $proprietari['p1'] = Proprietario::updateOrCreate(
            ['email' => 'proprietario1@schedinedinotifica.test'],
            [
                'nome' => 'Proprietario Uno',
                'admin_id' => $admin1->id,
                'attivo' => 1,
            ]
        );
        $proprietari['p2'] = Proprietario::updateOrCreate(
            ['email' => 'proprietario2@schedinedinotifica.test'],
            [
                'nome' => 'Proprietario Due',
                'admin_id' => $admin1->id,
                'attivo' => 1,
            ]
        );
        $proprietari['p3'] = Proprietario::updateOrCreate(
            ['email' => 'proprietario3@schedinedinotifica.test'],
            [
                'nome' => 'Proprietario Tre',
                'admin_id' => $admin2->id,
                'attivo' => 1,
            ]
        );

        $proprietariUsers = [];
        $proprietariUsers[] = User::updateOrCreate(
            ['email' => 'proprietario@schedinedinotifica.test'],
            [
                'name' => 'Proprietario Uno User',
                'ruolo' => 'proprietario',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => $proprietari['p1']->id,
                'struttura_id' => null,
            ]
        );
        $proprietariUsers[] = User::updateOrCreate(
            ['email' => 'proprietario2user@schedinedinotifica.test'],
            [
                'name' => 'Proprietario Due User',
                'ruolo' => 'proprietario',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => $proprietari['p2']->id,
                'struttura_id' => null,
            ]
        );
        $proprietariUsers[] = User::updateOrCreate(
            ['email' => 'proprietario3user@schedinedinotifica.test'],
            [
                'name' => 'Proprietario Tre User',
                'ruolo' => 'proprietario',
                'password' => $password,
                'avatar' => '',
                'proprietario_id' => $proprietari['p3']->id,
                'struttura_id' => null,
            ]
        );

        $oggi = Carbon::today();
        $struttureData = [
            'hotel_k2' => [
                'nome_struttura' => 'Hotel K2',
                'email' => 'hotelk2@schedinedinotifica.test',
                'telefono' => '000000001',
                'indirizzo' => 'Via Demo 1',
                'citta' => 'Rimini',
                'cap' => '47921',
                'regione' => 'Emilia-Romagna',
                'provincia' => 'RN',
                'attiva' => 1,
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'scadenza_servizio' => $oggi->copy()->addDays(60)->toDateString(),
                'proprietario' => $proprietari['p1'],
            ],
            'hotel_aurora' => [
                'nome_struttura' => 'Hotel Aurora',
                'email' => 'aurora@schedinedinotifica.test',
                'telefono' => '000000002',
                'indirizzo' => 'Via Demo 2',
                'citta' => 'Rimini',
                'cap' => '47921',
                'regione' => 'Emilia-Romagna',
                'provincia' => 'RN',
                'attiva' => 1,
                'piano' => 'basic',
                'stato_pagamento' => 'in_scadenza',
                'scadenza_servizio' => $oggi->copy()->addDays(10)->toDateString(),
                'proprietario' => $proprietari['p1'],
            ],
            'residence_mare' => [
                'nome_struttura' => 'Residence Mare',
                'email' => 'mare@schedinedinotifica.test',
                'telefono' => '000000003',
                'indirizzo' => 'Via Demo 3',
                'citta' => 'Ancona',
                'cap' => '60121',
                'regione' => 'Marche',
                'provincia' => 'AN',
                'attiva' => 1,
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'scadenza_servizio' => $oggi->copy()->addDays(120)->toDateString(),
                'proprietario' => $proprietari['p2'],
            ],
            'bb_pinzon' => [
                'nome_struttura' => 'B&B Pinzon',
                'email' => 'pinzon@schedinedinotifica.test',
                'telefono' => '000000004',
                'indirizzo' => 'Via Demo 4',
                'citta' => 'Ancona',
                'cap' => '60121',
                'regione' => 'Marche',
                'provincia' => 'AN',
                'attiva' => 0,
                'piano' => 'basic',
                'stato_pagamento' => 'sospeso',
                'scadenza_servizio' => $oggi->copy()->subDays(5)->toDateString(),
                'proprietario' => $proprietari['p2'],
            ],
            'hotel_tibidabo' => [
                'nome_struttura' => 'Hotel Tibidabo',
                'email' => 'tibidabo@schedinedinotifica.test',
                'telefono' => '000000005',
                'indirizzo' => 'Via Demo 5',
                'citta' => 'Bologna',
                'cap' => '40121',
                'regione' => 'Emilia-Romagna',
                'provincia' => 'BO',
                'attiva' => 1,
                'piano' => 'enterprise',
                'stato_pagamento' => 'ok',
                'scadenza_servizio' => $oggi->copy()->addDays(365)->toDateString(),
                'proprietario' => $proprietari['p3'],
            ],
            'hotel_alexandra' => [
                'nome_struttura' => 'Hotel Alexandra',
                'email' => 'alexandra@schedinedinotifica.test',
                'telefono' => '000000006',
                'indirizzo' => 'Via Demo 6',
                'citta' => 'Bologna',
                'cap' => '40121',
                'regione' => 'Emilia-Romagna',
                'provincia' => 'BO',
                'attiva' => 1,
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'scadenza_servizio' => $oggi->copy()->addDays(90)->toDateString(),
                'proprietario' => $proprietari['p3'],
            ],
        ];

        $strutture = [];
        Struttura::unguard();
        foreach ($struttureData as $key => $data) {
            $strutture[$key] = Struttura::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nome_struttura' => $data['nome_struttura'],
                    'proprietario_id' => $data['proprietario']->id,
                    'telefono' => $data['telefono'],
                    'indirizzo' => $data['indirizzo'],
                    'città' => $data['citta'],
                    'cap' => $data['cap'],
                    'nazione' => 'IT',
                    'regione' => $data['regione'],
                    'provincia' => $data['provincia'],
                    'tipologia_generale' => "Alberghiera",
                    'tipologia_struttura' => 'Hotel',
                    'classificazione' => '3 stelle',
                    'attiva' => $data['attiva'],
                    'piano' => $data['piano'],
                    'stato_pagamento' => $data['stato_pagamento'],
                    'scadenza_servizio' => $data['scadenza_servizio'],
                ]
            );
        }
        Struttura::reguard();

        $strutturaUsers = [
            ['email' => 'hotelk2@schedinedinotifica.test', 'name' => 'User Hotel K2', 'struttura' => $strutture['hotel_k2']],
            ['email' => 'aurora.user@schedinedinotifica.test', 'name' => 'User Hotel Aurora', 'struttura' => $strutture['hotel_aurora']],
            ['email' => 'mare.user@schedinedinotifica.test', 'name' => 'User Residence Mare', 'struttura' => $strutture['residence_mare']],
            ['email' => 'pinzon.user@schedinedinotifica.test', 'name' => 'User B&B Pinzon', 'struttura' => $strutture['bb_pinzon']],
            ['email' => 'tibidabo.user@schedinedinotifica.test', 'name' => 'User Hotel Tibidabo', 'struttura' => $strutture['hotel_tibidabo']],
            ['email' => 'alexandra.user@schedinedinotifica.test', 'name' => 'User Hotel Alexandra', 'struttura' => $strutture['hotel_alexandra']],
        ];

        $strutturaUsersCreated = [];
        foreach ($strutturaUsers as $userData) {
            $strutturaUsersCreated[] = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'ruolo' => 'struttura_user',
                    'password' => $password,
                    'avatar' => '',
                    'struttura_id' => $userData['struttura']->id,
                    'proprietario_id' => null,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('--- Demo SaaS seed completato ---');
            $this->command->info('Super admin: '.$superAdmin->email);
            $this->command->info('Admin: '.$admin1->email.' | '.$admin2->email);

            $this->command->info('Proprietari:');
            foreach ($proprietari as $prop) {
                $this->command->line(' - '.$prop->nome.' ('.$prop->email.') admin_id='.$prop->admin_id);
            }

            $this->command->info('Utenti proprietari:');
            foreach ($proprietariUsers as $user) {
                $this->command->line(' - '.$user->email.' proprietario_id='.$user->proprietario_id);
            }

            $this->command->info('Strutture:');
            foreach ($strutture as $struttura) {
                $this->command->line(' - '.$struttura->nome_struttura.' | '.$struttura->email.' | proprietario_id='.$struttura->proprietario_id.' | piano='.$struttura->piano.' | stato_pagamento='.$struttura->stato_pagamento.' | scadenza='.$struttura->scadenza_servizio.' | attiva='.$struttura->attiva);
            }

            $this->command->info('Utenti struttura_user:');
            foreach ($strutturaUsersCreated as $user) {
                $this->command->line(' - '.$user->email.' struttura_id='.$user->struttura_id);
            }
        }
    }
}
