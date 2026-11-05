<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

/** Diagnostico esplicito: aspettative di sicurezza, anche quando il candidato fallisce. */
class BaselineFinalBoundaryAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_reception_non_puo_resettare_password_tramite_percorso_legacy(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => 'reception']);
        $target = $this->actor('struttura_user', null, $s->id);
        $target->update(['ruolo_operativo' => 'proprietario']);
        $before = $target->password;
        $this->assertFalse($u->canManageGestioneOperativa($s->id));
        $this->actingAs($u)->post('/gestione-operativa/utenti/'.$target->id.'/password', ['password' => 'Password-audit-123!'])->assertForbidden();
        $r = $this->post('/strutture/utenti/'.$target->id.'/reset', ['password' => 'Password-audit-123!']);
        $this->assertSame($before, $target->fresh()->password, 'La reception ha cambiato la password del proprietario tramite la route legacy; HTTP '.$r->status());
        $r->assertForbidden();
    }

    public function test_reset_cross_tenant_non_modifica_password(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $a->id);
        $u->update(['ruolo_operativo' => 'proprietario']);
        $target = $this->actor('struttura_user', null, $b->id);
        $before = $target->password;
        $this->actingAs($u)->post('/strutture/utenti/'.$target->id.'/reset', ['password' => 'Password-audit-123!'])->assertNotFound();
        $this->assertSame($before, $target->fresh()->password);
    }

    public function test_cambio_password_errata_e_escalation_profilo_rifiutati(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => 'reception']);
        $before = $u->password;
        $this->actingAs($u)->post('/gestione-operativa/profilo/password', ['current_password' => 'ERRATA', 'password' => 'Password-audit-123!', 'password_confirmation' => 'Password-audit-123!'])->assertSessionHasErrors('current_password');
        $this->assertSame($before, $u->fresh()->password);
        $this->put('/gestione-operativa/profilo', ['name' => 'Audit sintetico', 'email' => $u->email, 'ruolo' => 'super_admin', 'ruolo_operativo' => 'proprietario']);
        $this->assertSame('struttura_user', $u->fresh()->ruolo);
        $this->assertSame('reception', $u->fresh()->ruolo_operativo);
    }

    private function gruppo(): array
    {
        $s = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'Principale sintetico', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 2]);
        $c = Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'name' => 'ACCOMPAGNATORE-SINTETICO', 'surname' => 'Audit', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'GRUPPO', 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 2, 'token' => str_repeat('Q', 64), 'stato' => 'da_inviare']);

        return [$s, $p, $c, $r];
    }

    public function test_modulo_anonimo_mostra_accompagnatore_autorizzato(): void
    {
        [$s, $p, $c, $r] = $this->gruppo();
        $this->assertSame(1, DB::table('componenti')->where('schedina_id', $p->id)->count());
        $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('componenti', fn ($items) => $items->contains('id', $c->id));
    }

    public function test_rifiuto_componente_invalido_non_salva_parzialmente_principale(): void
    {
        [$s, $p, $c, $r] = $this->gruppo();
        $before = DB::table('schedina')->where('id', $p->id)->first();
        $response = $this->postJson('/checkin/'.$r->token, ['name' => 'NON-SALVARE', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 2, 'componenti' => [['id' => 999999, 'name' => 'Estraneo', 'surname' => 'Audit', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']]]);
        $response->assertUnprocessable();
        $this->assertEquals($before, DB::table('schedina')->where('id', $p->id)->first(), 'Rifiuto 422 dopo scrittura parziale del principale');
        $this->assertSame('ACCOMPAGNATORE-SINTETICO', DB::table('componenti')->where('id', $c->id)->value('name'));
    }
}
