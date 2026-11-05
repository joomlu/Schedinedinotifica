<?php

namespace Database\Seeders;

use App\Models\Proprietario;
use App\Models\Struttura;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoSaasDataFullSeeder extends Seeder
{
    protected array $columnCache = [];

    public function run(): void
    {
        $today = Carbon::today();
        $password = Hash::make('Passw0rd!');

        $superAdmin = $this->seedUser(
            'tanggo@schedinedinotifica.test',
            [
                'name' => 'Super Admin Demo',
                'ruolo' => 'super_admin',
                'password' => $password,
                'avatar' => '',
                'struttura_id' => null,
                'proprietario_id' => null,
            ]
        );

        $admin1 = $this->seedUser(
            'admin1@schedinedinotifica.test',
            [
                'name' => 'Admin Uno Demo',
                'ruolo' => 'admin',
                'password' => $password,
                'avatar' => '',
                'struttura_id' => null,
                'proprietario_id' => null,
            ]
        );

        $admin2 = $this->seedUser(
            'admin2@schedinedinotifica.test',
            [
                'name' => 'Admin Due Demo',
                'ruolo' => 'admin',
                'password' => $password,
                'avatar' => '',
                'struttura_id' => null,
                'proprietario_id' => null,
            ]
        );

        $ownerSpecs = [
            'owner_k2' => [
                'nome' => 'Owner Struttura 1',
                'email' => 'struttura1@schedinedinotifica.test',
                'telefono' => '+39 0541 000001',
                'admin' => $admin1,
                'note' => 'Demo proprietario multi-struttura (K2 + Aurora)',
            ],
            'owner_aurora' => [
                'nome' => 'Owner Struttura 2',
                'email' => 'struttura2@schedinedinotifica.test',
                'telefono' => '+39 0541 000002',
                'admin' => $admin1,
                'note' => 'Demo proprietario senza struttura (Aurora condivisa con Struttura 1)',
            ],
            'owner_mare' => [
                'nome' => 'Owner Struttura 3',
                'email' => 'struttura3@schedinedinotifica.test',
                'telefono' => '+39 0541 000003',
                'admin' => $admin1,
                'note' => 'Demo proprietario per Residence Mare',
            ],
            'owner_pinzon' => [
                'nome' => 'Owner Struttura 4',
                'email' => 'struttura4@schedinedinotifica.test',
                'telefono' => '+39 0541 000004',
                'admin' => $admin2,
                'note' => 'Demo proprietario per B&B Pinzon',
            ],
            'owner_tibidabo' => [
                'nome' => 'Owner Struttura 5',
                'email' => 'struttura5@schedinedinotifica.test',
                'telefono' => '+39 0544 000005',
                'admin' => $admin2,
                'note' => 'Demo proprietario per Hotel Tibidabo',
            ],
            'owner_alexandra' => [
                'nome' => 'Owner Struttura 6',
                'email' => 'struttura6@schedinedinotifica.test',
                'telefono' => '+39 0544 000006',
                'admin' => $admin2,
                'note' => 'Demo proprietario per Hotel Alexandra',
            ],
        ];

        $owners = [];
        foreach ($ownerSpecs as $key => $spec) {
            $owners[$key] = Proprietario::updateOrCreate(
                ['email' => $spec['email']],
                $this->filterColumns('proprietari', [
                    'nome' => $spec['nome'],
                    'email' => $spec['email'],
                    'telefono' => $spec['telefono'],
                    'admin_id' => $spec['admin']->id,
                    'note' => $spec['note'],
                    'attivo' => 1,
                ])
            );
        }

        $proprietarioUsersSpec = [
            [
                'email' => 'proprietario@schedinedinotifica.test',
                'name' => 'Proprietario Hotel K2',
                'owner' => $owners['owner_k2'],
            ],
            [
                'email' => 'proprietario2user@schedinedinotifica.test',
                'name' => 'Proprietario Hotel Aurora',
                'owner' => $owners['owner_aurora'],
            ],
            [
                'email' => 'proprietario3user@schedinedinotifica.test',
                'name' => 'Proprietario Residence Mare',
                'owner' => $owners['owner_mare'],
            ],
            [
                'email' => 'owner.pinzon.user@schedinedinotifica.test',
                'name' => 'Proprietario B&B Pinzon',
                'owner' => $owners['owner_pinzon'],
            ],
            [
                'email' => 'owner.tibidabo.user@schedinedinotifica.test',
                'name' => 'Proprietario Hotel Tibidabo',
                'owner' => $owners['owner_tibidabo'],
            ],
            [
                'email' => 'owner.alexandra.user@schedinedinotifica.test',
                'name' => 'Proprietario Hotel Alexandra',
                'owner' => $owners['owner_alexandra'],
            ],
        ];

        $proprietarioUsers = [];
        foreach ($proprietarioUsersSpec as $spec) {
            $proprietarioUsers[] = $this->seedUser(
                $spec['email'],
                [
                    'name' => $spec['name'],
                    'ruolo' => 'proprietario',
                    'password' => $password,
                    'avatar' => '',
                    'proprietario_id' => $spec['owner']->id,
                    'struttura_id' => null,
                ]
            );
        }

        $structures = $this->buildStructureSpecs($owners, $today);

        $createdStructures = [];
        Struttura::unguard();
        foreach ($structures as $slug => $spec) {
            $match = $this->structureMatch($spec);
            $payload = $this->filterColumns('struttura', $spec);
            $createdStructures[$slug] = Struttura::updateOrCreate($match, $payload);
        }
        Struttura::reguard();

        $strutturaUsersSpec = [
            ['email' => 'hotelK2@schedinedinotifica.test', 'name' => 'Reception - Hotel K2', 'slug' => 'hotel_k2'],
            ['email' => 'aurora.user@schedinedinotifica.test', 'name' => 'Reception - Hotel Aurora', 'slug' => 'hotel_aurora'],
            ['email' => 'mare.user@schedinedinotifica.test', 'name' => 'Reception - Residence Mare', 'slug' => 'residence_mare'],
            ['email' => 'pinzon.user@schedinedinotifica.test', 'name' => 'Reception - B&B Pinzon', 'slug' => 'bb_pinzon'],
            ['email' => 'tibidabo.user@schedinedinotifica.test', 'name' => 'Reception - Hotel Tibidabo', 'slug' => 'hotel_tibidabo'],
            ['email' => 'alexandra.user@schedinedinotifica.test', 'name' => 'Reception - Hotel Alexandra', 'slug' => 'hotel_alexandra'],
        ];

        $strutturaUsers = [];
        foreach ($strutturaUsersSpec as $spec) {
            $struttura = $createdStructures[$spec['slug']] ?? null;
            if (!$struttura) {
                continue;
            }
            $strutturaUsers[] = $this->seedUser(
                $spec['email'],
                [
                    'name' => $spec['name'],
                    'ruolo' => 'struttura_user',
                    'password' => $password,
                    'avatar' => '',
                    'struttura_id' => $struttura->id,
                    'proprietario_id' => null,
                ]
            );
        }

        $warnings = $this->hardChecks($createdStructures, $owners);
        $this->renderSummary($superAdmin, [$admin1, $admin2], $owners, $createdStructures, $proprietarioUsers, $strutturaUsers, $warnings);
        $this->writeDemoMapDoc($owners, $createdStructures, $proprietarioUsers, $strutturaUsers);
    }

    protected function seedUser(string $email, array $data): User
    {
        $payload = $this->filterColumns('users', $data + ['email' => $email]);
        return User::updateOrCreate(['email' => $email], $payload);
    }

    protected function structureMatch(array $spec): array
    {
        if ($this->columnExists('struttura', 'email')) {
            return ['email' => $spec['email']];
        }

        return ['nome_struttura' => $spec['nome_struttura']];
    }

    protected function buildStructureSpecs(array $owners, Carbon $today): array
    {
        return [
            'hotel_k2' => [
                'nome_struttura' => 'Hotel K2',
                'name' => 'Hotel K2',
                'email' => 'hotelk2@schedinedinotifica.test',
                'telefono' => '+39 0541 330001',
                'phone' => '+39 0541 330001',
                'telefono_secondario' => '+39 0541 330999',
                'fax' => '+39 0541 330998',
                'indirizzo' => 'Viale Pinzon',
                'address' => 'Viale Pinzon',
                'numero_civico' => '120',
                'cap' => '47814',
                'cp' => '47814',
                'città' => 'Bellaria-Igea Marina',
                'city' => 'Bellaria-Igea Marina',
                'provincia' => 'RN',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Litorale',
                'località' => 'Igea Marina',
                'localita' => 'Igea Marina',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://hotelk2.test',
                'web' => 'https://hotelk2.test',
                'cir' => 'CIR-RN-000001',
                'cin' => 'CIN-IT-RN-K2-0001',
                'ragione_sociale' => 'Hotel K2 Srl',
                'tipologia_generale' => "Alberghiera",
                'typology' => 'Hotel',
                'tipologia_struttura' => 'Hotel',
                'classificazione' => '3 stelle',
                'clasification' => '3 stelle',
                'tipo_apertura' => 'stagionale',
                'startact' => $today->copy()->subMonths(6)->toDateString(),
                'data_apertura' => $today->copy()->subMonths(6)->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 80,
                'roomdisp' => 80,
                'letti_disponibili' => 160,
                'beddisp' => 160,
                'letti_agg' => 8,
                'updatedbed' => 8,
                'istat_username' => 'istat_k2',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_k2',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_k2',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '44.1384',
                'longitudine' => '12.4743',
                'attiva' => 1,
                'scadenza_servizio' => $today->copy()->addDays(60)->toDateString(),
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'proprietario_id' => $owners['owner_k2']->id,
            ],
            'hotel_aurora' => [
                'nome_struttura' => 'Hotel Aurora',
                'name' => 'Hotel Aurora',
                'email' => 'aurora@schedinedinotifica.test',
                'telefono' => '+39 0541 330002',
                'phone' => '+39 0541 330002',
                'telefono_secondario' => '+39 0541 330997',
                'fax' => '+39 0541 330996',
                'indirizzo' => 'Viale Regina Elena',
                'address' => 'Viale Regina Elena',
                'numero_civico' => '50',
                'cap' => '47921',
                'cp' => '47921',
                'città' => 'Rimini',
                'city' => 'Rimini',
                'provincia' => 'RN',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Marina Centro',
                'località' => 'Marina Centro',
                'localita' => 'Marina Centro',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://aurora.test',
                'web' => 'https://aurora.test',
                'cir' => 'CIR-RN-000002',
                'cin' => 'CIN-IT-RN-AUR-0002',
                'ragione_sociale' => 'Hotel Aurora Srl',
                'tipologia_generale' => "Alberghiera",
                'typology' => 'Hotel',
                'tipologia_struttura' => 'Hotel',
                'classificazione' => '3 stelle',
                'clasification' => '3 stelle',
                'tipo_apertura' => 'annuale',
                'startact' => $today->copy()->subYear()->toDateString(),
                'data_apertura' => $today->copy()->subYear()->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 60,
                'roomdisp' => 60,
                'letti_disponibili' => 120,
                'beddisp' => 120,
                'letti_agg' => 6,
                'updatedbed' => 6,
                'istat_username' => 'istat_aurora',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_aurora',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_aurora',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '44.0639',
                'longitudine' => '12.5808',
                'attiva' => 1,
                'scadenza_servizio' => $today->copy()->addDays(10)->toDateString(),
                'piano' => 'basic',
                'stato_pagamento' => 'in_scadenza',
                // Condivide il proprietario con Hotel K2 per simulare multi-struttura
                'proprietario_id' => $owners['owner_k2']->id,
            ],
            'residence_mare' => [
                'nome_struttura' => 'Residence Mare',
                'name' => 'Residence Mare',
                'email' => 'mare@schedinedinotifica.test',
                'telefono' => '+39 0541 330003',
                'phone' => '+39 0541 330003',
                'telefono_secondario' => '+39 0541 330995',
                'fax' => '+39 0541 330994',
                'indirizzo' => 'Viale Dante',
                'address' => 'Viale Dante',
                'numero_civico' => '88',
                'cap' => '47838',
                'cp' => '47838',
                'città' => 'Riccione',
                'city' => 'Riccione',
                'provincia' => 'RN',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Centro',
                'località' => 'Zona mare',
                'localita' => 'Zona mare',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://mare.test',
                'web' => 'https://mare.test',
                'cir' => 'CIR-RN-000003',
                'cin' => 'CIN-IT-RN-MARE-0003',
                'ragione_sociale' => 'Residence Mare Srl',
                'tipologia_generale' => "Alberghiera",
                'typology' => 'Residence',
                'tipologia_struttura' => 'Residence',
                'classificazione' => '4 stelle',
                'clasification' => '4 stelle',
                'tipo_apertura' => 'annuale',
                'startact' => $today->copy()->subMonths(10)->toDateString(),
                'data_apertura' => $today->copy()->subMonths(10)->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 50,
                'roomdisp' => 50,
                'letti_disponibili' => 150,
                'beddisp' => 150,
                'letti_agg' => 5,
                'updatedbed' => 5,
                'istat_username' => 'istat_mare',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_mare',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_mare',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '43.9991',
                'longitudine' => '12.6560',
                'attiva' => 1,
                'scadenza_servizio' => $today->copy()->addDays(120)->toDateString(),
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'proprietario_id' => $owners['owner_mare']->id,
            ],
            'bb_pinzon' => [
                'nome_struttura' => 'B&B Pinzon',
                'name' => 'B&B Pinzon',
                'email' => 'pinzon@schedinedinotifica.test',
                'telefono' => '+39 0547 000004',
                'phone' => '+39 0547 000004',
                'telefono_secondario' => '+39 0547 000994',
                'fax' => '+39 0547 000993',
                'indirizzo' => 'Via Armellini',
                'address' => 'Via Armellini',
                'numero_civico' => '12',
                'cap' => '47042',
                'cp' => '47042',
                'città' => 'Cesenatico',
                'city' => 'Cesenatico',
                'provincia' => 'FC',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Porto Canale',
                'località' => 'Porto Canale',
                'localita' => 'Porto Canale',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://pinzon.test',
                'web' => 'https://pinzon.test',
                'cir' => 'CIR-FC-000004',
                'cin' => 'CIN-IT-FC-PIN-0004',
                'ragione_sociale' => 'Pinzon Hospitality Srl',
                'tipologia_generale' => "Extra-alberghiera",
                'typology' => 'B&B',
                'tipologia_struttura' => 'B&B',
                'classificazione' => '3 stelle',
                'clasification' => '3 stelle',
                'tipo_apertura' => 'annuale',
                'startact' => $today->copy()->subMonths(8)->toDateString(),
                'data_apertura' => $today->copy()->subMonths(8)->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 18,
                'roomdisp' => 18,
                'letti_disponibili' => 36,
                'beddisp' => 36,
                'letti_agg' => 2,
                'updatedbed' => 2,
                'istat_username' => 'istat_pinzon',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_pinzon',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_pinzon',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '44.2014',
                'longitudine' => '12.3918',
                'attiva' => 0,
                'scadenza_servizio' => $today->copy()->subDays(5)->toDateString(),
                'piano' => 'basic',
                'stato_pagamento' => 'sospeso',
                'proprietario_id' => $owners['owner_pinzon']->id,
            ],
            'hotel_tibidabo' => [
                'nome_struttura' => 'Hotel Tibidabo',
                'name' => 'Hotel Tibidabo',
                'email' => 'tibidabo@schedinedinotifica.test',
                'telefono' => '+39 0544 000005',
                'phone' => '+39 0544 000005',
                'telefono_secondario' => '+39 0544 000993',
                'fax' => '+39 0544 000992',
                'indirizzo' => 'Viale Matteotti',
                'address' => 'Viale Matteotti',
                'numero_civico' => '15',
                'cap' => '48015',
                'cp' => '48015',
                'città' => 'Milano Marittima',
                'city' => 'Milano Marittima',
                'provincia' => 'RA',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Rotonda Primo Maggio',
                'località' => 'Rotonda Primo Maggio',
                'localita' => 'Rotonda Primo Maggio',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://tibidabo.test',
                'web' => 'https://tibidabo.test',
                'cir' => 'CIR-RA-000005',
                'cin' => 'CIN-IT-RA-TIB-0005',
                'ragione_sociale' => 'Hotel Tibidabo Spa',
                'tipologia_generale' => "Alberghiera",
                'typology' => 'Hotel',
                'tipologia_struttura' => 'Hotel',
                'classificazione' => '5 stelle',
                'clasification' => '5 stelle',
                'tipo_apertura' => 'annuale',
                'startact' => $today->copy()->subMonths(18)->toDateString(),
                'data_apertura' => $today->copy()->subMonths(18)->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 120,
                'roomdisp' => 120,
                'letti_disponibili' => 250,
                'beddisp' => 250,
                'letti_agg' => 15,
                'updatedbed' => 15,
                'istat_username' => 'istat_tibidabo',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_tibidabo',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_tibidabo',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '44.2770',
                'longitudine' => '12.3437',
                'attiva' => 1,
                'scadenza_servizio' => $today->copy()->addDays(365)->toDateString(),
                'piano' => 'enterprise',
                'stato_pagamento' => 'ok',
                'proprietario_id' => $owners['owner_tibidabo']->id,
            ],
            'hotel_alexandra' => [
                'nome_struttura' => 'Hotel Alexandra',
                'name' => 'Hotel Alexandra',
                'email' => 'alexandra@schedinedinotifica.test',
                'telefono' => '+39 0544 000006',
                'phone' => '+39 0544 000006',
                'telefono_secondario' => '+39 0544 000991',
                'fax' => '+39 0544 000990',
                'indirizzo' => 'Lungomare Grazia Deledda',
                'address' => 'Lungomare Grazia Deledda',
                'numero_civico' => '8',
                'cap' => '48015',
                'cp' => '48015',
                'città' => 'Cervia',
                'city' => 'Cervia',
                'provincia' => 'RA',
                'regione' => 'Emilia-Romagna',
                'nazione' => 'IT',
                'zona' => 'Lungomare',
                'località' => 'Lungomare',
                'localita' => 'Lungomare',
                'logo' => '',
                'logo_citta' => '',
                'logo_città' => '',
                'sito_web' => 'https://alexandra.test',
                'web' => 'https://alexandra.test',
                'cir' => 'CIR-RA-000006',
                'cin' => 'CIN-IT-RA-ALEX-0006',
                'ragione_sociale' => 'Hotel Alexandra Srl',
                'tipologia_generale' => "Alberghiera",
                'typology' => 'Hotel',
                'tipologia_struttura' => 'Hotel',
                'classificazione' => '4 stelle',
                'clasification' => '4 stelle',
                'tipo_apertura' => 'stagionale',
                'startact' => $today->copy()->subMonths(7)->toDateString(),
                'data_apertura' => $today->copy()->subMonths(7)->toDateString(),
                'closeact' => null,
                'data_chiusura' => null,
                'camere_disponibili' => 90,
                'roomdisp' => 90,
                'letti_disponibili' => 180,
                'beddisp' => 180,
                'letti_agg' => 9,
                'updatedbed' => 9,
                'istat_username' => 'istat_alexandra',
                'istat_password' => 'DemoPass!123',
                'questura_username' => 'questura_alexandra',
                'questura_password' => 'DemoPass!123',
                'ref' => 'questura_alexandra',
                'refpass' => 'DemoPass!123',
                'camere_reali_enabled' => true,
                'latitudine' => '44.2590',
                'longitudine' => '12.3492',
                'attiva' => 1,
                'scadenza_servizio' => $today->copy()->addDays(90)->toDateString(),
                'piano' => 'pro',
                'stato_pagamento' => 'ok',
                'proprietario_id' => $owners['owner_alexandra']->id,
            ],
        ];
    }

    protected function filterColumns(string $table, array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $column = $this->resolveColumn($table, $key);
            if ($column && $this->columnExists($table, $column)) {
                $clean[$column] = $value;
            }
        }

        return $clean;
    }

    protected function resolveColumn(string $table, string $key): ?string
    {
        if ($this->columnExists($table, $key)) {
            return $key;
        }

        $map = [
            'citta' => 'città',
            'logo_citta' => 'logo_città',
            'localita' => 'località',
            'phone' => 'telefono',
            'address' => 'indirizzo',
            'city' => 'città',
            'cp' => 'cap',
            'web' => 'sito_web',
            'piva' => 'partita_iva',
            'cf' => 'codice_fiscale',
            'typology' => 'tipologia_struttura',
            'clasification' => 'classificazione',
            'startact' => 'data_apertura',
            'closeact' => 'data_chiusura',
            'roomdisp' => 'camere_disponibili',
            'beddisp' => 'letti_disponibili',
            'updatedbed' => 'letti_agg',
            'ref' => 'questura_username',
            'refpass' => 'questura_password',
        ];

        return $map[$key] ?? null;
    }

    protected function columnExists(string $table, string $column): bool
    {
        if (isset($this->columnCache[$table][$column])) {
            return $this->columnCache[$table][$column];
        }

        $exists = Schema::hasColumn($table, $column);
        $this->columnCache[$table][$column] = $exists;

        return $exists;
    }

    protected function hardChecks(array $structures, array $owners): array
    {
        $warnings = [];

        if (count($structures) !== 6) {
            $warnings[] = 'Numero strutture diverso da 6: ' . count($structures);
        }

        if (count($owners) !== 6) {
            $warnings[] = 'Numero proprietari diverso da 6: ' . count($owners);
        }

        $countPerOwner = [];
        foreach ($structures as $structure) {
            if (!$structure->proprietario_id) {
                $warnings[] = 'Struttura ID ' . $structure->id . ' senza proprietario_id';
            }

            $ownerId = $structure->proprietario_id;
            if ($ownerId) {
                $countPerOwner[$ownerId] = ($countPerOwner[$ownerId] ?? 0) + 1;
            }
        }

        $ownerIdsWithStructures = array_keys($countPerOwner);
        $expectedOwnerIds = array_map(fn ($owner) => $owner->id, $owners);
        $ownersWithoutStructures = array_diff($expectedOwnerIds, $ownerIdsWithStructures);

        foreach ($ownersWithoutStructures as $ownerId) {
            $warnings[] = 'Proprietario ID ' . $ownerId . ' non ha strutture collegate.';
        }

        $multiPropertyOwners = array_filter($countPerOwner, fn ($count) => $count > 1);
        if (!$multiPropertyOwners) {
            $warnings[] = 'Nessun proprietario con più di una struttura, atteso almeno uno scenario multi-struttura.';
        }

        return $warnings;
    }

    protected function renderSummary(
        User $superAdmin,
        array $admins,
        array $owners,
        array $structures,
        array $proprietarioUsers,
        array $strutturaUsers,
        array $warnings
    ): void {
        if (!$this->command) {
            return;
        }

        $this->command->info('--- Demo SaaS Data FULL ---');
        $this->command->line('Super admin: ' . $superAdmin->email);
        $this->command->line('Admins: ' . implode(', ', array_map(fn ($a) => $a->email, $admins)));

        $this->command->info('Mappa Admin -> Proprietario -> Struttura -> Utenti');
        foreach ($owners as $key => $owner) {
            $structure = $this->findStructureByOwner($structures, $owner->id);
            $propUser = $this->findProprietarioUser($proprietarioUsers, $owner->id);
            $strUser = $this->findStrutturaUser($strutturaUsers, $structure?->id);

            $adminEmail = $this->resolveAdminEmail($admins, $owner->admin_id);
            $this->command->line(
                sprintf(
                    'Admin %s -> Proprietario %s (%s) -> Struttura %s [%s] -> PropUser %s -> StrUser %s | servizio %s/%s scad=%s',
                    $adminEmail ?? 'n/d',
                    $owner->nome,
                    $owner->email,
                    $structure?->nome_struttura ?? 'n/d',
                    $structure?->email ?? 'n/d',
                    $propUser?->email ?? 'n/d',
                    $strUser?->email ?? 'n/d',
                    $structure?->piano ?? 'n/d',
                    $structure?->stato_pagamento ?? 'n/d',
                    $structure?->scadenza_servizio ?? 'n/d'
                )
            );
        }

        if (!empty($warnings)) {
            $this->command->warn('WARNINGS:');
            foreach ($warnings as $warning) {
                $this->command->warn(' - ' . $warning);
            }
        } else {
            $this->command->info('Hard check OK: 6 strutture, 6 proprietari, include scenario multi-struttura.');
        }
    }

    protected function writeDemoMapDoc(array $owners, array $structures, array $proprietarioUsers, array $strutturaUsers): void
    {
        $rows = [];
        foreach ($owners as $owner) {
            $structure = $this->findStructureByOwner($structures, $owner->id);
            $propUser = $this->findProprietarioUser($proprietarioUsers, $owner->id);
            $strUser = $this->findStrutturaUser($strutturaUsers, $structure?->id);
            $admin = $owner->admin;

            $rows[] = [
                'admin' => $admin?->email ?? 'n/d',
                'owner' => $owner->email,
                'structure' => $structure
                    ? ($structure->nome_struttura . ' (' . ($structure->email ?? 'n/d') . ', id ' . $structure->id . ')')
                    : 'n/d',
                'prop_user' => $propUser?->email ?? 'n/d',
                'str_user' => $strUser?->email ?? 'n/d',
                'stato' => $structure
                    ? ($structure->piano . ' / ' . $structure->stato_pagamento . ' / scad. ' . $structure->scadenza_servizio)
                    : 'n/d',
            ];
        }

        $content = [];
        $content[] = '# Demo Map';
        $content[] = '';
        $content[] = 'Mappa relazioni admin -> proprietario -> struttura -> utenti (solo lettura, password non incluse).';
        $content[] = '';
        $content[] = '| Admin | Proprietario | Struttura | Proprietario user | Struttura user | Stato servizio |';
        $content[] = '| --- | --- | --- | --- | --- | --- |';
        foreach ($rows as $row) {
            $content[] = sprintf(
                '| %s | %s | %s | %s | %s | %s |',
                $row['admin'],
                $row['owner'],
                $row['structure'],
                $row['prop_user'],
                $row['str_user'],
                $row['stato']
            );
        }

        $path = base_path('docs/DEMO-MAP.md');
        file_put_contents($path, implode("\n", $content));
    }

    protected function findStructureByOwner(array $structures, int $ownerId): ?Struttura
    {
        foreach ($structures as $structure) {
            if ((int) $structure->proprietario_id === $ownerId) {
                return $structure;
            }
        }

        return null;
    }

    protected function findProprietarioUser(array $users, int $ownerId): ?User
    {
        foreach ($users as $user) {
            if ((int) $user->proprietario_id === $ownerId) {
                return $user;
            }
        }

        return null;
    }

    protected function findStrutturaUser(array $users, ?int $strutturaId): ?User
    {
        if ($strutturaId === null) {
            return null;
        }

        foreach ($users as $user) {
            if ((int) $user->struttura_id === $strutturaId) {
                return $user;
            }
        }

        return null;
    }

    protected function resolveAdminEmail(array $admins, ?int $adminId): ?string
    {
        foreach ($admins as $admin) {
            if ((int) $admin->id === (int) $adminId) {
                return $admin->email;
            }
        }

        return null;
    }
}
