<?php

namespace Tests\Feature;

use App\Models\Classificazione;
use App\Models\Struttura;
use App\Models\User;
use App\Services\TassaDiSoggiornoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class AnagraficheUnificateTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_titolare_non_puo_modificare_dati_maestri_dopo_alta(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $before = $s->getRawOriginal();
        $this->actingAs($this->actor('proprietario', $owner->id))
            ->put('/struttura?sid='.$s->id, $this->validStructurePayload($s))->assertForbidden();
        $this->assertSame($before, $s->fresh()->getRawOriginal());
    }

    public function test_reception_non_puo_modificare_dati_maestri(): void
    {
        $s = $this->structureFor($this->ownerFor($this->actor('admin')));
        $u = $this->actor('struttura_user', $s->proprietario_id, $s->id);
        $u->update(['ruolo_operativo' => 'reception']);
        $this->actingAs($u)->put('/struttura', $this->validStructurePayload($s))->assertForbidden();
    }

    public function test_admin_puo_salvare_classificazione_e_contatti_sulla_stessa_ficha(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $c = Classificazione::create(['nome' => '3 stelle']);
        $c->tipologieStruttura()->attach($s->tipologia_struttura_id);
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, [
            'nome_struttura' => $s->nome_struttura, 'proprietario_id' => $s->proprietario_id,
            'tipologia_generale_id' => $s->tipologia_generale_id, 'tipologia_struttura_id' => $s->tipologia_struttura_id,
            'classificazione_id' => $c->id, 'telefono' => '061112223', 'email' => 'commerciale@example.invalid',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($c->id, $s->fresh()->classificazione_id);
        $this->assertSame('3 stelle', $s->fresh()->classificazione);
        $this->assertSame('commerciale@example.invalid', $s->fresh()->email);
    }

    public function test_superadmin_form_include_classificazione_capienza_fiscali(): void
    {
        $s = $this->structureFor($this->ownerFor($this->actor('admin')));
        $response = $this->actingAs($this->actor('super_admin'))->get('/superadmin/strutture/'.$s->id.'/edit')->assertOk();
        foreach (['classificazione_id', 'tipologia_generale_id', 'camere_disponibili', 'cin', 'partita_iva', 'telefono'] as $name) {
            $response->assertSee('name="'.$name.'"', false);
        }
    }

    public function test_admin_senza_assegnazione_non_puo_modificare_legacy(): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('admin'))->put('/struttura?sid='.$s->id, $this->validStructurePayload($s))->assertForbidden();
    }

    public function test_cambio_proprietario_revoca_tutti_accessi_precedenti(): void
    {
        $admin = $this->actor('admin');
        $a = $this->ownerFor($admin);
        $b = $this->ownerFor($admin);
        $s = $this->structureFor($a);
        $u = $this->actor('struttura_user', $a->id, $s->id);
        $u->update(['ruolo_operativo' => 'reception']);
        $this->actingAs($this->actor('super_admin'))->put('/superadmin/strutture/'.$s->id, [
            'nome_struttura' => $s->nome_struttura, 'proprietario_id' => $b->id,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse($u->fresh()->attivo);
        $this->actingAs($u->fresh())->get('/struttura?sid='.$s->id)->assertRedirect('/login');
    }

    public function test_salvataggio_parziale_conserva_apertura_e_capienza(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $s->update(['camere_reali_enabled' => true, 'tipo_apertura' => 'Annuale', 'cin' => 'ITTEST']);
        $this->actingAs($admin)->put('/struttura?sid='.$s->id, ['telefono' => '061112223'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($s->fresh()->camere_reali_enabled);
        $this->assertSame('Annuale', $s->fresh()->tipo_apertura);
        $this->assertSame('ITTEST', $s->fresh()->cin);
    }

    public function test_conflitto_classificazione_non_produce_tariffa_implicita(): void
    {
        $s = $this->structureFor(null);
        $c = Classificazione::create(['nome' => '4 stelle']);
        $c->tipologieStruttura()->attach($s->tipologia_struttura_id);
        $s->update(['tipologia_struttura' => 'Hotel', 'classificazione_id' => $c->id, 'classificazione' => '3 stelle']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(TassaDiSoggiornoService::class)->datiStrutturaFiscali($s->fresh());
    }

    public function test_alta_titolare_crea_una_sola_struttura_senza_account_o_licenze_duplicate(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $base = $this->structureFor($owner);
        $u = $this->actor('proprietario', $owner->id);
        $before = Struttura::count();
        $users = User::count();
        $payload = $this->validStructurePayload($base);
        $payload['nome_struttura'] = 'Nuova struttura sintetica';
        $this->actingAs($u)->post('/strutture', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $s = Struttura::where('nome_struttura', $payload['nome_struttura'])->sole();
        $this->assertSame($before + 1, Struttura::count());
        $this->assertSame($users, User::count());
        $this->assertSame($owner->id, $s->proprietario_id);
        $this->assertFalse($s->attiva);
        $this->get('/struttura?sid='.$s->id)->assertOk()->assertSee('Anagrafica unica #'.$s->id);
        $this->put('/struttura?sid='.$s->id, ['nome_struttura' => 'Tentativo'])->assertForbidden();
    }

    public function test_alta_titolare_non_accetta_collegamenti_o_privilegi_forgiati(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $this->actingAs($this->actor('proprietario', $owner->id));
        $count = Struttura::count();
        foreach (['proprietario_id' => 999, 'admin_id' => 999, 'attiva' => 1, 'ruolo' => 'super_admin'] as $k => $v) {
            $this->post('/strutture', $this->validStructurePayload($s) + [$k => $v])->assertForbidden();
            $this->assertSame($count, Struttura::count());
        }
    }

    public function test_operativita_titolare_preserva_anagrafica_e_segreti_omessi(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $s->update(['questura_password' => 'segreto-sintetico', 'questura_wskey' => 'chiave-sintetica']);
        $before = $s->fresh()->getRawOriginal();
        $this->actingAs($this->actor('proprietario', $owner->id))->put('/struttura?sid='.$s->id,
            ['camere_reali_enabled' => 1, 'questura_password' => '', 'questura_wskey' => ''])->assertSessionHasNoErrors()->assertRedirect();
        $after = $s->fresh();
        $this->assertTrue($after->camere_reali_enabled);
        $this->assertSame('segreto-sintetico', $after->questura_password);
        $this->assertSame($before['nome_struttura'], $after->getRawOriginal('nome_struttura'));
        $html = $this->get('/struttura?sid='.$s->id)->assertOk()->getContent();
        $this->assertStringNotContainsString('segreto-sintetico', $html);
        $this->assertStringNotContainsString('name="nome_struttura"', $html);
    }

    public function test_classificazione_incompatibile_e_rollback_senza_scritture_parziali(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $c = Classificazione::create(['nome' => 'Non compatibile']);
        $before = $s->getRawOriginal();
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, [
            'nome_struttura' => 'Non salvare', 'proprietario_id' => $s->proprietario_id, 'classificazione_id' => $c->id,
        ])->assertSessionHasErrors('classificazione_id');
        $this->assertSame($before, $s->fresh()->getRawOriginal());
    }

    public function test_account_proprietario_non_recuperato_per_email_o_nome(): void
    {
        $a = $this->ownerFor($this->actor('admin'));
        $b = $this->ownerFor($this->actor('admin'));
        $a->update(['nome' => 'Nome condiviso', 'email' => 'comune@example.invalid']);
        $b->update(['nome' => 'Nome condiviso', 'email' => 'comune@example.invalid']);
        $u = $this->actor('proprietario', $b->id);
        $u->update(['name' => $a->nome, 'email' => $a->email]);
        $before = $u->fresh()->getRawOriginal();
        $this->actingAs($this->actor('super_admin'))->put('/superadmin/proprietari/'.$a->id, ['nome' => 'Aggiornato'])->assertSessionHasNoErrors();
        $this->assertSame($before, $u->fresh()->getRawOriginal());
        $this->assertSame(0, User::where('proprietario_id', $a->id)->count());
    }

    public function test_admin_estraneo_non_puo_leggere_o_modificare_entita(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $this->actingAs($this->actor('admin'));
        foreach (['strutture' => $s->id, 'proprietari' => $owner->id] as $path => $id) {
            $this->get('/admin/'.$path.'/'.$id.'/edit')->assertNotFound();
            $this->put('/admin/'.$path.'/'.$id, ['nome' => 'Tentativo', 'nome_struttura' => 'Tentativo'])->assertNotFound();
        }
    }

    public function test_legacy_univoco_si_visualizza_senza_scritture(): void
    {
        $s = $this->structureFor(null);
        $c = Classificazione::create(['nome' => '3 stelle']);
        $c->tipologieStruttura()->attach($s->tipologia_struttura_id);
        $s->update(['classificazione' => '3 stelle', 'classificazione_id' => null]);
        $before = $s->fresh()->getRawOriginal();
        $data = app(\App\Services\AnagraficaStruttura::class)->formData($s->fresh());
        $this->assertSame($c->id, $data['selezioniTipologia']['classificazione_id']);
        $this->assertSame($before, $s->fresh()->getRawOriginal());
    }
    public function test_errore_accesso_annulla_anche_modifica_struttura(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $before = $s->fresh()->getRawOriginal();
        \Illuminate\Support\Facades\Event::listen('eloquent.saving: '.User::class, function (User $user) {
            if ($user->username === 'errore-sintetico') throw new \RuntimeException('Errore sintetico di persistenza account');
        });
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, [
            'nome_struttura' => 'Non deve restare salvata', 'proprietario_id' => $s->proprietario_id,
            'accesso_username' => 'errore-sintetico', 'accesso_password' => 'Password-sintetica-123!',
        ])->assertStatus(500);
        $this->assertSame($before, $s->fresh()->getRawOriginal());
        $this->assertSame(0, User::where('username', 'errore-sintetico')->count());
    }

    public function test_aggiornamento_admin_visibile_a_tutti_i_ruoli_e_identita_web(): void
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin); $s = $this->structureFor($owner);
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, ['nome_struttura' => 'Nome canonico', 'proprietario_id' => $owner->id,
            'email' => 'commerciale-canonica@example.invalid', 'telefono' => '061112224'])->assertSessionHasNoErrors();
        foreach ([$this->actor('super_admin'), $this->actor('proprietario', $owner->id), $this->actor('struttura_user', $owner->id, $s->id)] as $u) {
            $this->actingAs($u)->get('/struttura?sid='.$s->id)->assertOk()->assertSee('Nome canonico')->assertSee('commerciale-canonica@example.invalid');
        }
        $identity = app(\App\Services\WebCheckinIdentita::class)->of($s->fresh());
        $this->assertSame('Nome canonico', $identity['nome']);
        $this->assertSame('commerciale-canonica@example.invalid', $identity['email']);
        $this->assertSame('061112224', $identity['telefono']);
    }

    public function test_null_esplicito_cancella_solo_campo_facoltativo(): void
    {
        $admin = $this->actor('admin'); $s = $this->structureFor($this->ownerFor($admin));
        $s->update(['telefono_secondario' => '06999', 'cin' => 'ITTEST']);
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, ['nome_struttura' => $s->nome_struttura,
            'proprietario_id' => $s->proprietario_id, 'telefono_secondario' => null])->assertSessionHasNoErrors();
        $this->assertNull($s->fresh()->telefono_secondario);
        $this->assertSame('ITTEST', $s->fresh()->cin);
    }
    public function test_creazione_operativa_usa_stessa_struttura_e_proprietario(): void
    {
        $owner = $this->ownerFor($this->actor('admin')); $s = $this->structureFor($owner);
        $u = $this->actor('struttura_user', $owner->id, $s->id); $u->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($u)->post('/gestione-operativa/utenti?sid='.$s->id, ['name' => 'Persona nuova', 'display_name' => 'Persona',
            'shared_username' => 'accesso-condiviso', 'email' => 'persona-nuova@example.invalid', 'password' => 'Password-sintetica-123!', 'ruolo_operativo' => 'reception'])->assertSessionHasNoErrors()->assertRedirect();
        $new = User::where('email', 'persona-nuova@example.invalid')->sole();
        $this->assertSame($s->id, $new->struttura_id);
        $this->assertSame($owner->id, $new->proprietario_id);
        $this->assertSame('struttura_user', $new->ruolo);
        $this->assertSame('reception', $new->ruolo_operativo);
        $this->post('/gestione-operativa/utenti?sid='.$s->id, ['ruolo' => 'super_admin'])->assertForbidden();
    }

    public function test_errore_nuovo_utente_operativo_non_cambia_username_condiviso(): void
    {
        $owner = $this->ownerFor($this->actor('admin')); $s = $this->structureFor($owner);
        $u = $this->actor('struttura_user', $owner->id, $s->id); $u->update(['ruolo_operativo' => 'proprietario', 'username' => 'precedente']);
        \Illuminate\Support\Facades\Event::listen('eloquent.creating: '.User::class, function () { throw new \RuntimeException('Errore sintetico account operativo'); });
        $this->actingAs($u)->post('/gestione-operativa/utenti?sid='.$s->id, ['name' => 'Persona nuova', 'display_name' => 'Persona',
            'shared_username' => 'non-deve-restare', 'email' => 'errore-operativo@example.invalid', 'password' => 'Password-sintetica-123!', 'ruolo_operativo' => 'reception'])->assertStatus(500);
        $this->assertSame('precedente', $u->fresh()->username);
        $this->assertSame(0, User::where('email', 'errore-operativo@example.invalid')->count());
    }

    public function test_profilo_personale_conserva_contatti_omessi_e_non_cambia_gerarchia(): void
    {
        $owner = $this->ownerFor($this->actor('admin')); $s = $this->structureFor($owner);
        $u = $this->actor('struttura_user', $owner->id, $s->id); $u->update(['telefono' => '06123456']);
        $this->actingAs($u)->put('/gestione-operativa/profilo', ['name' => 'Persona aggiornata', 'display_name' => 'Persona'])->assertSessionHasNoErrors();
        $this->assertSame('06123456', $u->fresh()->telefono);
        $this->put('/gestione-operativa/profilo', ['name' => 'Tentativo', 'display_name' => 'Persona', 'ruolo_operativo' => 'proprietario'])->assertForbidden();
        $this->assertSame('Persona aggiornata', $u->fresh()->name);
    }

    public function test_api_profilo_non_espone_note_amministrative_o_segreti(): void
    {
        $u = $this->actor('struttura_user');
        $u->forceFill(['note_amministrative' => 'RISERVATO-SINTETICO', 'compenso_servizio' => 123])->save();
        \Laravel\Sanctum\Sanctum::actingAs($u);
        $this->getJson('/api/user')->assertOk()->assertJsonPath('id', $u->id)->assertJsonMissingPath('password')
            ->assertJsonMissingPath('remember_token')->assertJsonMissingPath('note_amministrative')->assertJsonMissingPath('compenso_servizio');
    }
    public function test_conflitto_legacy_richiede_conferma_esplicita_prima_di_riconciliare(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $c = Classificazione::create(['nome' => '3 stelle']);
        $c->tipologieStruttura()->attach($s->tipologia_struttura_id);
        $s->update(['classificazione_id' => $c->id, 'classificazione' => '4 stelle']);
        $payload = ['classificazione_id' => $c->id, 'telefono' => '061234567'];
        $before = $s->fresh()->getAttributes();
        $this->actingAs($admin)->put('/admin/strutture/'.$s->id, $payload)
            ->assertSessionHasErrors('conferma_risoluzione_anagrafica');
        $this->assertSame($before, $s->fresh()->getAttributes());
        $this->put('/admin/strutture/'.$s->id, $payload + ['conferma_risoluzione_anagrafica' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame($c->id, $s->fresh()->classificazione_id);
        $this->assertSame('3 stelle', $s->fresh()->classificazione);
        $this->assertSame('061234567', $s->fresh()->telefono);
    }

}
