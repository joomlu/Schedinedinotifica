<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class BaselineFinalAuthorizationAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function personale(string $role = 'proprietario'): array
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => $role]);
        $this->actingAs($u);

        return [$s, $u];
    }

    private function dati(User $u): array
    {
        return ['name' => 'Persona sintetica aggiornata', 'display_name' => 'Audit', 'shared_username' => 'audit-shared', 'email' => $u->email, 'ruolo_operativo' => 'reception', 'password' => 'Password-audit-123!', 'attivo' => false];
    }

    public function test_gestione_autorizzata_crea_modifica_disattiva_senza_escalation(): void
    {
        [$s, $u] = $this->personale();
        $data = $this->dati($u);
        $data['email'] = 'new-person@example.invalid';
        $data['attivo'] = true;
        $data['ruolo'] = 'super_admin';
        $this->post('/gestione-operativa/utenti', $data)->assertSessionHasNoErrors()->assertRedirect();
        $new = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame('struttura_user', $new->ruolo);
        $this->assertSame($s->id, (int) $new->struttura_id);
        $this->assertTrue(Hash::check($data['password'], $new->password));
        $data['attivo'] = false;
        $this->put('/gestione-operativa/utenti/'.$new->id, $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse((bool) $new->fresh()->attivo);
        $this->assertSame('struttura_user', $new->fresh()->ruolo);
    }

    public function test_reception_non_crea_modifica_disattiva_altri_utenti(): void
    {
        [$s, $u] = $this->personale('reception');
        $target = $this->actor('struttura_user', null, $s->id);
        $before = $target->fresh()->getRawOriginal();
        $count = User::count();
        $data = $this->dati($target);
        $this->post('/gestione-operativa/utenti', $data)->assertForbidden();
        $this->put('/gestione-operativa/utenti/'.$target->id, $data)->assertForbidden();
        $this->assertSame($count, User::count());
        $this->assertSame($before, $target->fresh()->getRawOriginal());
    }

    public function test_modifica_reset_e_profilo_cross_tenant_negati(): void
    {
        [$a, $u] = $this->personale();
        $b = $this->structureFor(null);
        $target = $this->actor('struttura_user', null, $b->id);
        $before = $target->fresh()->getRawOriginal();
        $this->put('/gestione-operativa/utenti/'.$target->id, $this->dati($target))->assertNotFound();
        $this->post('/gestione-operativa/utenti/'.$target->id.'/password', ['password' => 'Password-audit-123!'])->assertNotFound();
        $this->post('/update-profile/'.$target->id, ['name' => 'NON-SCRIVERE', 'email' => 'bad@example.invalid'])->assertForbidden();
        $this->post('/update-password/'.$target->id, ['current_password' => 'IGNOTO', 'password' => 'Password-audit-123!', 'password_confirmation' => 'Password-audit-123!'])->assertForbidden();
        $this->assertSame($before, $target->fresh()->getRawOriginal());
    }

    public function test_riferimenti_componenti_esterni_negati_in_lettura_scrittura_eliminazione(): void
    {
        [$a, $u] = $this->personale();
        $b = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'RISERVATO-AUDIT-B', 'surname' => 'Sintetico']);
        $c = Componenti::forceCreate(['struttura_id' => $b->id, 'schedina_id' => $p->id, 'name' => 'RISERVATO-COMPONENTE-B', 'surname' => 'Sintetico']);
        $before = DB::table('componenti')->where('id', $c->id)->first();
        $this->get('/componenti/'.$c->id.'/modifica')->assertNotFound();
        $this->put('/componenti/'.$c->id, ['name' => 'NON-SCRIVERE'])->assertNotFound();
        $this->delete('/componenti/'.$c->id)->assertNotFound();
        $this->assertEquals($before, DB::table('componenti')->where('id', $c->id)->first());
    }

    public function test_logout_distrugge_accesso_e_password_errata_non_cambia_credenziali(): void
    {
        [$s, $u] = $this->personale();
        $before = $u->password;
        $this->post('/update-password/'.$u->id, ['current_password' => 'ERRATA', 'password' => 'Password-audit-123!', 'password_confirmation' => 'Password-audit-123!'])->assertOk();
        $this->assertSame($before, $u->fresh()->password);
        $this->get('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/gestione-operativa')->assertRedirect('/login');
    }
}
