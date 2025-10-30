<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $manageUsers = Permission::firstOrCreate(['name' => 'manage users']);
        $manageRoles = Permission::firstOrCreate(['name' => 'manage roles']);
        $accessAdmin = Permission::firstOrCreate(['name' => 'access admin']);
        $createStructures = Permission::firstOrCreate(['name' => 'create structures']);
        $createClients = Permission::firstOrCreate(['name' => 'create clients']);
        $manageStaff = Permission::firstOrCreate(['name' => 'manage staff']);
        $viewReports = Permission::firstOrCreate(['name' => 'view reports']);

        // Create roles (Spatie roles)
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $clienteRole = Role::firstOrCreate(['name' => 'cliente']);
        $strutturaRole = Role::firstOrCreate(['name' => 'struttura']);

        // Assign permissions to roles
        // superadmin: puede crear todas las demas condiciones
        $superadminRole->givePermissionTo(Permission::all());
        
        // admin: puede crear clientes y estructuras
        $adminRole->syncPermissions([$manageUsers, $manageRoles, $accessAdmin, $createStructures, $createClients]);
        
        // cliente: puede tener varias estructuras y administrarlas, crear accesos individuales de staff
        $clienteRole->syncPermissions([$createStructures, $createClients, $manageStaff, $manageUsers, $viewReports]);
        
        // struttura: acceso individual para quien administra el software (no puede crear nada, solo usar app)
        $strutturaRole->syncPermissions([$viewReports]);

        // Seed users with requested emails and unified password
        $password = Hash::make('123456');

        // superadmin@test.test
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@test.test'],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'avatar' => 'avatar-1.jpg',
                // app-specific role column must match allowed set
                'role' => 'superadmin',
            ]
        );
        $superadmin->syncRoles(['superadmin']);

        // admin@test.test
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.test'],
            [
                'name' => 'Admin',
                'password' => $password,
                'avatar' => 'avatar-2.jpg',
                'role' => 'admin',
            ]
        );
        $admin->syncRoles(['admin']);

        // cliente@test.test -> map internal role to a non-admin app role (hotel_staff)
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@test.test'],
            [
                'name' => 'Cliente',
                'password' => $password,
                'avatar' => 'avatar-3.jpg',
                'role' => 'hotel_staff',
            ]
        );
        $cliente->syncRoles(['cliente']);

        // struttura@test.test -> map internal role to hotel_owner
        $struttura = User::firstOrCreate(
            ['email' => 'struttura@test.test'],
            [
                'name' => 'Struttura',
                'password' => $password,
                'avatar' => 'avatar-4.jpg',
                'role' => 'hotel_owner',
            ]
        );
        $struttura->syncRoles(['struttura']);
    }
}
