<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginPresentationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_accesso_presenta_identita_link_e_contratto_del_modulo(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Accedi a SchedinaNotifica')->assertSee('Accesso struttura')
            ->assertSee('site-assets/img/logo-tanggo.png')->assertSee('site-assets/img/foto-reception.webp')
            ->assertSee('name="_token"', false)->assertSee('name="login"', false)
            ->assertSee('name="password"', false)->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('https://wa.me/393930991968')
            ->assertSee('Software gestionale per ospitalità, ricezione e adempimenti telematici.')
            ->assertSee('Realizzato con ❤️ per il lavoro quotidiano della struttura.')
            ->assertSee('© 2026 @Schedine di Notifica - Tanggo Platform | Versione 2.0')
            ->assertSee(route('password.request'))->assertSee(route('site.home'))->assertSee(route('site.contatti'));
    }

    public function test_errore_credenziali_ripristina_solo_identificativo(): void
    {
        $this->from('/login')->post('/login', ['login' => 'nessuno-login@example.invalid', 'password' => 'Password-sintetica-errata!'])
            ->assertRedirect('/login')->assertSessionHasErrors('login');
        $this->get('/login')->assertOk()->assertSee('Credenziali di accesso non riconosciute.')
            ->assertSee('id="login-error"', false)->assertSee('aria-describedby="login-error"', false)
            ->assertSee('value="nessuno-login@example.invalid"', false)->assertDontSee('Password-sintetica-errata!');
        $this->assertGuest();
    }

    public function test_campi_vuoti_mostrano_entrambi_gli_errori(): void
    {
        $this->from('/login')->post('/login', ['login' => '', 'password' => ''])->assertRedirect('/login')->assertSessionHasErrors(['login', 'password']);
        $this->get('/login')->assertSee('id="login-error"', false)->assertSee('id="password-error"', false)->assertSee('aria-describedby="password-error"', false);
    }

    public function test_csrf_errato_resta_rifiutato(): void
    {
        $this->post('/login', ['_token' => 'csrf-errato', 'login' => 'sintetico', 'password' => 'sintetica'])->assertStatus(419);
        $this->assertGuest();
    }

    public static function identificativi(): array
    {
        return [['accesso-login-sintetico'], ['login-design@example.invalid']];
    }

    #[DataProvider('identificativi')]
    public function test_accesso_reale_isolato_con_username_o_email(string $identifier): void
    {
        $user = User::factory()->create(['username' => 'accesso-login-sintetico', 'email' => 'login-design@example.invalid', 'ruolo' => 'super_admin', 'attivo' => true, 'password' => Hash::make('Password-login-fixture-123!')]);
        $this->post('/login', ['login' => $identifier, 'password' => 'Password-login-fixture-123!'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
