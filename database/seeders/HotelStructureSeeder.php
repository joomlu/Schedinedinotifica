<?php

namespace Database\Seeders;

use App\Models\HotelStructure;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HotelStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario cliente de prueba
        $cliente = User::where('email', 'cliente@test.test')->first();
        
        if (!$cliente) {
            $this->command->error('Usuario cliente@test.test no encontrado. Ejecuta primero RolesAndPermissionsSeeder.');
            return;
        }

        // Estructuras de ejemplo para la cadena del cliente
        $structures = [
            [
                'cliente_id' => $cliente->id,
                'name' => 'Grand Hotel Milano Centro',
                'description' => 'Hotel de lujo en el centro histórico de Milán, cerca del Duomo y la Galleria Vittorio Emanuele II.',
                'fecha_registro' => '2024-01-15',
                'fecha_vencimiento' => '2025-12-31',
                'online' => true,
                'activo' => true,
                'username_hotel' => 'grandmilano',
                'password_hotel' => Hash::make('hotel123'),
                'schedina_url' => 'https://schedina.example.com/grandmilano',
                'city' => 'Milano',
                'address' => 'Via Montenapoleone, 15',
                'cp' => '20121',
                'phone' => '+39 02 7601 1234',
                'fax' => '+39 02 7601 1235',
                'email' => 'info@grandmilano.com',
                'web' => 'https://www.grandmilano.com',
                'cf' => 'GRNHTL24A01F205X',
                'piva' => '12345678901',
                'typology' => 'Hotel',
                'clasification' => '5 stelle',
                'roomdisp' => 120,
                'beddisp' => 240,
            ],
            [
                'cliente_id' => $cliente->id,
                'name' => 'Hotel Venezia Palace',
                'description' => 'Boutique hotel con vista al Canal Grande, perfecto para una estancia romántica en Venecia.',
                'fecha_registro' => '2024-02-01',
                'fecha_vencimiento' => '2025-12-31',
                'online' => true,
                'activo' => true,
                'username_hotel' => 'veneziapalace',
                'password_hotel' => Hash::make('hotel123'),
                'schedina_url' => 'https://schedina.example.com/veneziapalace',
                'city' => 'Venezia',
                'address' => 'Sestiere San Marco, 1243',
                'cp' => '30124',
                'phone' => '+39 041 520 1234',
                'fax' => '+39 041 520 1235',
                'email' => 'info@veneziapalace.com',
                'web' => 'https://www.veneziapalace.com',
                'cf' => 'VNZHTL24B01L736Y',
                'piva' => '98765432101',
                'typology' => 'Hotel',
                'clasification' => '4 stelle',
                'roomdisp' => 45,
                'beddisp' => 90,
            ],
            [
                'cliente_id' => $cliente->id,
                'name' => 'Resort Costa Smeralda',
                'description' => 'Resort exclusivo en la hermosa Costa Smeralda, Cerdeña, con playa privada y spa.',
                'fecha_registro' => '2024-03-10',
                'fecha_vencimiento' => '2025-12-31',
                'online' => true,
                'activo' => true,
                'username_hotel' => 'costasmeralda',
                'password_hotel' => Hash::make('hotel123'),
                'schedina_url' => 'https://schedina.example.com/costasmeralda',
                'city' => 'Arzachena',
                'address' => 'Località Porto Cervo',
                'cp' => '07021',
                'phone' => '+39 0789 92345',
                'fax' => '+39 0789 92346',
                'email' => 'info@costasmeralda.com',
                'web' => 'https://www.costasmeralda.com',
                'cf' => 'CSTHTL24C01A453Z',
                'piva' => '45678912301',
                'typology' => 'Resort',
                'clasification' => '5 stelle',
                'roomdisp' => 200,
                'beddisp' => 450,
            ],
            [
                'cliente_id' => $cliente->id,
                'name' => 'B&B Roma Trastevere',
                'description' => 'Bed & Breakfast acogedor en el pintoresco barrio de Trastevere, cerca del centro histórico.',
                'fecha_registro' => '2024-04-05',
                'fecha_vencimiento' => '2025-06-30',
                'online' => false,
                'activo' => true,
                'username_hotel' => 'romatrastevere',
                'password_hotel' => Hash::make('hotel123'),
                'schedina_url' => 'https://schedina.example.com/romatrastevere',
                'city' => 'Roma',
                'address' => 'Via della Lungaretta, 23',
                'cp' => '00153',
                'phone' => '+39 06 5812 3456',
                'email' => 'info@romatrastevere.com',
                'web' => 'https://www.romatrastevere.com',
                'cf' => 'RMTHTL24D01H501A',
                'piva' => '78912345601',
                'typology' => 'B&B',
                'clasification' => '3 stelle',
                'roomdisp' => 8,
                'beddisp' => 16,
            ],
            [
                'cliente_id' => $cliente->id,
                'name' => 'Agriturismo Toscana Verde',
                'description' => 'Auténtico agriturismo toscano rodeado de viñedos y olivares, ideal para desconectar.',
                'fecha_registro' => '2023-12-01',
                'fecha_vencimiento' => '2024-11-30',
                'online' => true,
                'activo' => false,
                'username_hotel' => 'toscanaverde',
                'password_hotel' => Hash::make('hotel123'),
                'schedina_url' => 'https://schedina.example.com/toscanaverde',
                'city' => 'Siena',
                'address' => 'Località Poggio alle Mura',
                'cp' => '53024',
                'phone' => '+39 0577 84 1234',
                'email' => 'info@toscanaverde.com',
                'web' => 'https://www.toscanaverde.com',
                'cf' => 'TSCHTL23L01I726B',
                'piva' => '32165498701',
                'typology' => 'Agriturismo',
                'clasification' => '3 stelle',
                'roomdisp' => 12,
                'beddisp' => 28,
            ],
        ];

        foreach ($structures as $structure) {
            HotelStructure::create($structure);
        }

        $this->command->info('✅ Creadas 5 estructuras hoteleras de prueba para cliente@test.test');
        $this->command->info('📋 Estados:');
        $this->command->info('   - 4 hoteles online, 1 offline (B&B Roma Trastevere)');
        $this->command->info('   - 4 hoteles activos, 1 desactivado (Agriturismo Toscana Verde - vencido)');
        $this->command->info('🔑 Credenciales de acceso (todas con password: hotel123):');
        $this->command->info('   - grandmilano, veneziapalace, costasmeralda, romatrastevere, toscanaverde');
    }
}
