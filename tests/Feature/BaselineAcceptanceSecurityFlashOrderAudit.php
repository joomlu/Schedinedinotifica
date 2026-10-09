<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

// Copia diagnostica: unica differenza funzionale, asserzione flash prima della richiesta successiva.
class BaselineAcceptanceSecurityFlashOrderAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function personale(string $ruolo = 'reception'): User
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => $ruolo, 'password' => Hash::make('Password-audit-123!')]);

        return $u;
    }

    public function test_utente_disattivato_non_accede_con_sessione_esistente(): void
    {
        $u = $this->personale();
        $this->post('/login', ['login' => $u->email, 'password' => 'Password-audit-123!'])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/gestione-operativa')->assertOk();
        DB::table('users')->where('id', $u->id)->update(['attivo' => false]);
        Auth::forgetGuards();
        $response = $this->get('/gestione-operativa');
        $this->assertContains($response->status(), [302, 401, 403], 'Account disattivato: sessione esistente ancora autorizzata');
    }

    public function test_login_nuovo_di_utente_disattivato_viene_rifiutato(): void
    {
        $u = $this->personale();
        $u->update(['attivo' => false]);
        $this->post('/login', ['login' => $u->email, 'password' => 'Password-audit-123!'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_recupero_password_valido_token_monouso_e_account_estraneo_preservato(): void
    {
        $u = $this->personale();
        $other = $this->personale();
        $otherHash = $other->password;
        $token = Password::broker()->createToken($u);
        $payload = ['email' => $u->email, 'token' => $token, 'password' => 'Password-nuova-audit-123!', 'password_confirmation' => 'Password-nuova-audit-123!'];
        $this->post('/password/reset', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(Hash::check($payload['password'], $u->fresh()->password));
        $hash = $u->fresh()->password;
        $this->get('/logout');
        Auth::forgetGuards();
        $this->post('/password/reset', $payload)->assertSessionHasErrors('email');
        $this->assertSame($hash, $u->fresh()->password);
        $this->assertSame($otherHash, $other->fresh()->password);
    }

    public function test_token_recupero_invalido_non_modifica_password(): void
    {
        $u = $this->personale();
        $before = $u->password;
        $this->post('/password/reset', ['email' => $u->email, 'token' => 'TOKEN-SINTETICO-INVALIDO', 'password' => 'Password-nuova-audit-123!', 'password_confirmation' => 'Password-nuova-audit-123!'])->assertSessionHasErrors('email');
        $this->assertSame($before, $u->fresh()->password);
        $this->assertGuest();
    }

    public function test_creazione_legacy_di_utente_da_proprietario_funzionante(): void
    {
        $u = $this->personale('proprietario');
        $this->actingAs($u);
        $this->post('/strutture/utenti', ['name' => 'Utente sintetico', 'email' => 'legacy-authorized@example.invalid', 'password' => 'Password-audit-123!'])->assertSessionHasNoErrors()->assertRedirect();
        $new = User::where('email', 'legacy-authorized@example.invalid')->firstOrFail();
        $this->assertSame($u->struttura_id, $new->struttura_id);
        $this->assertSame('struttura_user', $new->ruolo);
    }

    public function test_reception_non_crea_utenti_attraverso_route_legacy(): void
    {
        $u = $this->personale();
        $this->actingAs($u);
        $before = User::count();
        $this->post('/strutture/utenti', ['name' => 'Utente sintetico', 'email' => 'legacy-forbidden@example.invalid', 'password' => 'Password-audit-123!'])->assertForbidden();
        $this->assertSame($before, User::count());
    }

    public function test_token_scaduto_di_recupero_non_modifica_password(): void
    {
        $u = $this->personale();
        $token = Password::broker()->createToken($u);
        DB::table(config('auth.passwords.users.table'))->where('email', $u->email)->update(['created_at' => now()->subDays(2)]);
        $before = $u->password;
        $this->post('/password/reset', ['email' => $u->email, 'token' => $token, 'password' => 'Password-nuova-audit-123!', 'password_confirmation' => 'Password-nuova-audit-123!'])->assertSessionHasErrors('email');
        $this->assertSame($before, $u->fresh()->password);
    }

    public function test_recupero_precedentemente_emesso_non_riabilita_account_disattivato(): void
    {
        $u = $this->personale();
        $token = Password::broker()->createToken($u);
        $u->update(['attivo' => false]);
        $response = $this->post('/password/reset', ['email' => $u->email, 'token' => $token, 'password' => 'Password-nuova-audit-123!', 'password_confirmation' => 'Password-nuova-audit-123!']);
        $response->assertSessionHasErrors('email');
        $access = $this->get('/gestione-operativa')->status();
        fwrite(STDOUT, '\nA14 '.json_encode(['attivo' => (bool) $u->fresh()->attivo, 'reset_status' => $response->status(), 'autenticato' => Auth::check(), 'access_status' => $access]).'\n');
        $this->assertGuest();
    }
}
