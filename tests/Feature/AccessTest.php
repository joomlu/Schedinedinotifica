<?php

namespace Tests\Feature;

use Database\Seeders\DemoSaasDataFullSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSaasDataFullSeeder::class);
    }

    public function test_super_admin_can_access_demo_map(): void
    {
        $user = $this->userByEmail('tanggo@schedinedinotifica.test');
        $this->actingAs($user)
            ->get('/qa/demo-map')
            ->assertStatus(200);
    }

    public function test_admin_access_matrix(): void
    {
        $admin = $this->userByEmail('admin1@schedinedinotifica.test');

        $this->actingAs($admin)
            ->get('/qa/demo-map')
            ->assertStatus(403);

        $respSuper = $this->actingAs($admin)->get('/superadmin/strutture');
        $this->assertTrue(in_array($respSuper->getStatusCode(), [302, 403]));
        $this->assertNotEquals(500, $respSuper->getStatusCode());

        $respAdmin = $this->actingAs($admin)->get('/admin/proprietari');
        $this->assertTrue(in_array($respAdmin->getStatusCode(), [200, 302]));
        $this->assertNotEquals(403, $respAdmin->getStatusCode());

        $this->actingAs($admin)
            ->get('/proprietario/strutture')
            ->assertStatus(403);
    }

    public function test_proprietario_access(): void
    {
        $ownerUser = $this->userByEmail('proprietario@schedinedinotifica.test');

        $this->actingAs($ownerUser)
            ->get('/proprietario/strutture')
            ->assertStatus(200);

        $this->actingAs($ownerUser)
            ->get('/admin/proprietari')
            ->assertStatus(403);
    }

    public function test_struttura_user_access(): void
    {
        $structUser = $this->userByEmail('hotelK2@schedinedinotifica.test');

        $resp = $this->actingAs($structUser)->get('/strutture/seleziona');
        $status = $resp->getStatusCode();
        $this->assertTrue(in_array($status, [200, 302, 403]), 'Status: '.$status);
        $this->assertNotEquals(500, $status);
    }

    protected function userByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
