<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Superadmin (tu - vede tutto)
        User::updateOrCreate(
            ['email' => 'superadmin@test.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('1234'),
                'role' => 'superadmin',
                'structure_id' => null, // No structure - sees everything
                'avatar' => '',
            ]
        );

        // 2. Admin (también superadmin)
        User::updateOrCreate(
            ['email' => 'admin@test.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('1234'),
                'role' => 'superadmin',
                'structure_id' => null,
                'avatar' => '',
            ]
        );

        // 3. Hotel Owner (proprietario hotel - gestisce staff)
        User::updateOrCreate(
            ['email' => 'hotel@test.test'],
            [
                'name' => 'Hotel Owner',
                'password' => Hash::make('1234'),
                'role' => 'hotel_owner',
                'structure_id' => 1, // Assegnare alla struttura del hotel
                'avatar' => '',
            ]
        );

        // 4. Hotel Staff (secretaria - solo movimenti)
        User::updateOrCreate(
            ['email' => 'staff1@test.test'],
            [
                'name' => 'Secretaria Hotel',
                'password' => Hash::make('1234'),
                'role' => 'hotel_staff',
                'structure_id' => 1, // Stesso hotel
                'avatar' => '',
            ]
        );

        echo "\n✅ Utenti creati/aggiornati:\n";
        echo "   - superadmin@test.test / 1234 (SUPERADMIN - vede tutto)\n";
        echo "   - admin@test.test / 1234 (SUPERADMIN - vede tutto)\n";
        echo "   - hotel@test.test / 1234 (HOTEL OWNER - gestisce staff)\n";
        echo "   - staff1@test.test / 1234 (HOTEL STAFF - solo movimenti)\n\n";
    }
}
