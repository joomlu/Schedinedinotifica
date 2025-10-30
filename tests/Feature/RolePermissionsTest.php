<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed permissions and roles
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    /** @test */
    public function superadmin_can_create_structures_and_clients()
    {
        $user = User::factory()->create([
            'email' => 'superadmin-test@example.com',
            'avatar' => 'avatar.jpg',
            'role' => 'superadmin',
        ]);
        $user->assignRole('superadmin');

        $this->assertTrue($user->can('create structures'));
        $this->assertTrue($user->can('create clients'));
        $this->assertTrue($user->can('manage users'));
        $this->assertTrue($user->can('manage staff'));
    }

    /** @test */
    public function admin_can_create_structures_and_clients()
    {
        $user = User::factory()->create([
            'email' => 'admin-test@example.com',
            'avatar' => 'avatar.jpg',
            'role' => 'admin',
        ]);
        $user->assignRole('admin');

        $this->assertTrue($user->can('create structures'));
        $this->assertTrue($user->can('create clients'));
        $this->assertTrue($user->can('manage users'));
        $this->assertFalse($user->can('manage staff')); // admin no tiene manage staff
    }

    /** @test */
    public function cliente_can_manage_staff_but_not_create_structures()
    {
        $user = User::factory()->create([
            'email' => 'cliente-test@example.com',
            'avatar' => 'avatar.jpg',
            'role' => 'hotel_staff',
        ]);
        $user->assignRole('cliente');

        $this->assertTrue($user->can('manage staff'));
        $this->assertFalse($user->can('create structures'));
        $this->assertFalse($user->can('create clients'));
        $this->assertFalse($user->can('manage users'));
    }

    /** @test */
    public function struttura_cannot_create_anything()
    {
        $user = User::factory()->create([
            'email' => 'struttura-test@example.com',
            'avatar' => 'avatar.jpg',
            'role' => 'hotel_owner',
        ]);
        $user->assignRole('struttura');

        $this->assertFalse($user->can('create structures'));
        $this->assertFalse($user->can('create clients'));
        $this->assertFalse($user->can('manage users'));
        $this->assertFalse($user->can('manage staff'));
        $this->assertTrue($user->can('view reports')); // solo puede ver reportes
    }
}
