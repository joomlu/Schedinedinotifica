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

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([$manageUsers, $manageRoles, $accessAdmin]);
        $userRole->givePermissionTo([]);

        // Ensure an admin user exists
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'avatar' => 'avatar-1.jpg',
                'role' => 'admin',
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
