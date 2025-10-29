<?php

namespace Database\Seeders;

use App\Models\Estructura;
use Illuminate\Database\Seeder;

class QuickTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔄 Creando estructuras y asignando usuarios...\n\n";

        // Crear 2 Estructuras (Hotels)
        $structure1 = Estructura::create([
            'name' => 'Hotel Satellite Roma',
            'phone' => '+39 06 1234567',
            'city' => 'Roma',
            'address' => 'Via Roma 123',
            'email' => 'roma@hotelsatellite.com',
            'cp' => '00100',
            'web' => 'www.hotelsatellite-roma.com',
            'cf' => 'HTLRM12345',
            'piva' => '12345678901',
            'startact' => '2020-01-01',
            'typology' => 'Hotel',
            'clasification' => '4 stelle',
            'numshedine' => 50,
            'roomdisp' => 45,
            'ref' => 'REF-ROMA-001',
            'beddisp' => 90,
            'refpass' => 'PASS-ROMA-001',
        ]);

        $structure2 = Estructura::create([
            'name' => 'Hotel Satellite Milano',
            'phone' => '+39 02 7654321',
            'city' => 'Milano',
            'address' => 'Via Milano 456',
            'email' => 'milano@hotelsatellite.com',
            'cp' => '20100',
            'web' => 'www.hotelsatellite-milano.com',
            'cf' => 'HTLML67890',
            'piva' => '98765432109',
            'startact' => '2019-06-15',
            'typology' => 'Hotel',
            'clasification' => '5 stelle',
            'numshedine' => 80,
            'roomdisp' => 75,
            'ref' => 'REF-MILANO-001',
            'beddisp' => 150,
            'refpass' => 'PASS-MILANO-001',
        ]);

        // Actualizar users con structures
        \App\Models\User::where('email', 'hotel@test.test')->update(['structure_id' => $structure1->id]);
        \App\Models\User::where('email', 'staff1@test.test')->update(['structure_id' => $structure1->id]);

        echo "✅ Estructuras creadas:\n";
        echo "   - {$structure1->name} (ID: {$structure1->id})\n";
        echo "   - {$structure2->name} (ID: {$structure2->id})\n\n";

        echo "✅ Usuarios actualizados con structures:\n";
        echo "   - hotel@test.test (hotel_owner) → Hotel Satellite Roma\n";
        echo "   - staff1@test.test (hotel_staff) → Hotel Satellite Roma\n";
        echo "   - superadmin@test.test (superadmin) → Sin estructura (ve todo)\n";
        echo "   - admin@test.test (superadmin) → Sin estructura (ve todo)\n\n";

        echo "🎯 Sistema de roles listo para probar!\n\n";
    }
}
