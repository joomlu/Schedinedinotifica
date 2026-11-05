<?php

namespace Tests\Feature;

use App\Models\ImpersonationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class ImpersonazioneSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public static function ruoli(): array
    {
        return [['admin'], ['proprietario'], ['struttura_user'], ['super_admin']];
    }

    public function test_route_stop_non_collide_e_start_richiede_id_numerico(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/superadmin/impersona/esci', 'POST'));
        $this->assertSame('esci', $route->getActionMethod());
        $this->actingAs($this->actor('super_admin'))->post('/superadmin/impersona/abc')->assertStatus(405);
    }

    #[DataProvider('ruoli')]
    public function test_uscita_ripristina_identita_log_e_contesto(string $ruolo): void
    {
        $super = $this->actor('super_admin');
        $owner = $this->ownerFor($super);
        $s = $this->structureFor($owner);
        $target = $this->actor($ruolo, $ruolo === 'proprietario' ? $owner->id : null, $ruolo === 'struttura_user' ? $s->id : null);
        $this->actingAs($super)->post('/superadmin/impersona/'.$target->id)->assertRedirect();
        $this->assertAuthenticatedAs($target);
        $log = ImpersonationLog::sole();
        $this->assertNull($log->ended_at);
        $this->post('/superadmin/impersona/esci')->assertRedirect()->assertSessionMissing('impersonator_id')
            ->assertSessionMissing('impersonated_id')->assertSessionMissing('impersonation_log_id');
        $this->assertAuthenticatedAs($super);
        $this->assertNotNull($log->fresh()->ended_at);
        $this->assertNull(session('struttura_corrente_id'));
        $this->post('/superadmin/impersona/esci')->assertForbidden();
    }

    public function test_uscita_con_servizio_target_scaduto_e_csrf_reale(): void
    {
        $super = $this->actor('super_admin');
        $s = $this->structureFor(null);
        $target = $this->actor('struttura_user', null, $s->id);
        $this->actingAs($super)->post('/superadmin/impersona/'.$target->id)->assertRedirect();
        $s->update(['scadenza_servizio' => '2000-01-01']);
        $this->post('/superadmin/impersona/esci', ['_token' => 'ERRATO'])->assertStatus(419);
        $this->assertAuthenticatedAs($target);
        $this->assertNull(ImpersonationLog::sole()->ended_at);
        $this->post('/superadmin/impersona/esci')->assertRedirect();
        $this->assertAuthenticatedAs($super);
    }

    public static function contestiInvalidi(): array
    {
        return array_map(fn ($v) => [$v], ['assente', 'log_assente', 'log_chiuso', 'target_errato', 'origine_errata', 'origine_inattiva', 'origine_non_super']);
    }

    #[DataProvider('contestiInvalidi')]
    public function test_uscita_senza_attestazione_valida_non_cambia_identita_o_log(string $caso): void
    {
        $super = $this->actor('super_admin');
        $s = $this->structureFor(null);
        $target = $this->actor('struttura_user', null, $s->id);
        $log = ImpersonationLog::create(['impersonator_id' => $super->id, 'impersonated_id' => $target->id, 'started_at' => now(), 'ended_at' => $caso === 'log_chiuso' ? now() : null]);
        $session = ['impersonator_id' => $super->id, 'impersonated_id' => $target->id, 'impersonation_log_id' => $log->id];
        if ($caso === 'assente') {
            $session = [];
        }
        if ($caso === 'log_assente') {
            $session['impersonation_log_id'] = 999999;
        }
        if ($caso === 'target_errato') {
            $session['impersonated_id'] = $super->id;
        }
        if ($caso === 'origine_errata') {
            $session['impersonator_id'] = $target->id;
        }
        if ($caso === 'origine_inattiva') {
            $super->update(['attivo' => false]);
        }
        if ($caso === 'origine_non_super') {
            $super->update(['ruolo' => 'admin']);
        }
        $before = $log->fresh()->getRawOriginal();
        $this->actingAs($target)->withSession($session)->post('/superadmin/impersona/esci')->assertForbidden();
        $this->assertAuthenticatedAs($target);
        $this->assertSame($before, $log->fresh()->getRawOriginal());
    }

    public function test_start_non_autorizzato_e_stop_anonimo(): void
    {
        $s = $this->structureFor(null);
        $target = $this->actor('struttura_user', null, $s->id);
        $this->post('/superadmin/impersona/esci')->assertRedirect('/login');
        $this->actingAs($target)->post('/superadmin/impersona/'.$target->id)->assertForbidden();
        $this->post('/superadmin/impersona/esci')->assertForbidden();
        $this->assertSame(0, ImpersonationLog::count());
    }
}
