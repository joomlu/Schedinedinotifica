<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthCoverPagesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_loads_cover_layout()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Benvenuto', false);
        $response->assertSee('Accedi', false);
    }

    /** @test */
    public function forgot_password_page_loads_cover_layout()
    {
        $response = $this->get('/password/reset');
        $response->assertStatus(200);
        $response->assertSee('Password dimenticata', false);
        $response->assertSee('Invia link reset', false);
    }

    /** @test */
    public function register_page_loads_cover_layout()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Crea nuovo account', false);
        $response->assertSee('Registrati', false);
    }

    /** @test */
    public function it_sends_password_reset_notification()
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
            'avatar' => 'default.png',
            'role' => 'hotel_staff',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
