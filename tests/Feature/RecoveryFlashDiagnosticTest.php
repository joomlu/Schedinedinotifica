<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class RecoveryFlashDiagnosticTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_errore_flash_presente_al_reset_e_scaduto_dopo_richiesta_successiva(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $token = Password::broker()->createToken($u);
        $before = $u->password;
        $u->update(['attivo' => false]);
        $r = $this->post('/password/reset', ['email' => $u->email, 'token' => $token, 'password' => 'Password-nuova-audit-123!', 'password_confirmation' => 'Password-nuova-audit-123!']);
        $r->assertRedirect()->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame($before, $u->fresh()->password);
        $this->get('/gestione-operativa')->assertRedirect('/login');
        $this->assertFalse($this->app['session']->has('errors'), 'Il flash originario è scaduto dopo la seconda richiesta');
        $this->assertGuest();
        $this->assertFalse((bool) $u->fresh()->attivo);
        $this->assertSame($before, $u->fresh()->password);
    }
}
