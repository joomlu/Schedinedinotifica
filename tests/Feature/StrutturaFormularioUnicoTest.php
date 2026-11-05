<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class StrutturaFormularioUnicoTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_tutti_i_ruoli_usano_il_formulario_completo_e_salvano_la_propria_anagrafica(): void
    {
        Http::fake();
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $structure = $this->structureFor($owner);
        $actors = [$this->actor('super_admin'), $admin, $this->actor('proprietario', $owner->id), $this->actor('struttura_user', $owner->id, $structure->id)];

        foreach ($actors as $index => $actor) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $structure->id]);
            $this->get('/struttura')->assertOk()->assertViewIs('struttura.edit')
                ->assertSee('name="nome_struttura"', false)->assertSee('name="indirizzo"', false)
                ->assertSee('name="email"', false)->assertSee('Profilo Alloggiati Web')
                ->assertSee('Configurazione Ross1000')->assertSee('Salva')
                ->assertDontSee('Per modificare i dati maestri rivolgersi');
            $payload = $this->validStructurePayload($structure);
            $payload['nome_struttura'] = 'Hotel ruolo '.$index;
            $payload['indirizzo'] = 'Via Prova '.$index;
            $payload['questura_username'] = 'UTENTE-SINTETICO-'.$index;
            $this->put('/struttura', $payload)->assertSessionHasNoErrors()->assertRedirect();
            $fresh = $structure->fresh();
            $this->assertSame($payload['nome_struttura'], $fresh->nome_struttura);
            $this->assertSame($payload['indirizzo'], $fresh->indirizzo);
            $this->assertSame($payload['questura_username'], $fresh->questura_username);
            $this->assertSame($owner->id, $fresh->proprietario_id);
            $this->post('/istat-tabella-a/configurazione?struttura_id='.$structure->id, [
                'codice' => 'CODICE-SINTETICO-'.$index,
                'username' => 'ISTAT-SINTETICO', 'password' => 'SEGRETO-SINTETICO',
            ])->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame('CODICE-SINTETICO-'.$index, $structure->fresh()->istat_codice_struttura);
        }
        Http::assertNothingSent();
    }

    public function test_hotel_e_proprietario_non_cambiano_relazioni_ruoli_o_stato_del_servizio(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $structure = $this->structureFor($owner);
        foreach ([$this->actor('proprietario', $owner->id), $this->actor('struttura_user', $owner->id, $structure->id)] as $actor) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $structure->id]);
            $before = $structure->fresh()->getRawOriginal();
            foreach (['id', 'struttura_id', 'proprietario_id', 'admin_id', 'ruolo', 'attiva', 'piano', 'scadenza_servizio', 'stato_pagamento'] as $field) {
                $this->put('/struttura', $this->validStructurePayload($structure) + [$field => '999'])
                    ->assertForbidden();
                $this->assertSame($before, $structure->fresh()->getRawOriginal());
            }
        }
    }

    public function test_formulario_completo_non_allarga_accesso_a_strutture_estranee(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $mine = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $before = $foreign->getRawOriginal();
        foreach ([$admin, $this->actor('proprietario', $owner->id), $this->actor('struttura_user', $owner->id, $mine->id)] as $actor) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $mine->id]);
            foreach (['sid', 'struttura_id'] as $parameter) {
                $this->get('/struttura?'.$parameter.'='.$foreign->id)->assertForbidden();
                $this->put('/struttura?'.$parameter.'='.$foreign->id, $this->validStructurePayload($foreign))->assertForbidden();
            }
        }
        $this->assertSame($before, $foreign->fresh()->getRawOriginal());
    }

    public function test_hotel_riceve_gli_errori_di_validazione_senza_salvataggi_parziali(): void
    {
        $structure = $this->structureFor($this->ownerFor($this->actor('admin')));
        $before = $structure->getRawOriginal();
        $this->actingAs($this->actor('struttura_user', $structure->proprietario_id, $structure->id))
            ->withSession(['struttura_corrente_id' => $structure->id]);
        $payload = $this->validStructurePayload($structure);
        $payload['email'] = 'indirizzo non valido';
        $payload['nome_struttura'] = '';
        $this->put('/struttura', $payload)->assertSessionHasErrors(['email', 'nome_struttura']);
        $this->assertSame($before, $structure->fresh()->getRawOriginal());
    }
    public function test_logo_citta_personale_non_modifica_il_catalogo_o_gli_altri_hotel(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $comune = \Tests\Support\GeoLogoFixtures::comune();
        $globalPath = \Tests\Support\GeoLogoFixtures::existingLogo($comune);
        $owner = $this->ownerFor($this->actor('admin'));
        $structure = $this->structureFor($owner);
        $other = $this->structureFor($owner);
        $other->update(['logo_citta' => 'storage/'.$globalPath]);
        $masterBefore = $comune->fresh()->getRawOriginal();
        $otherBefore = $other->fresh()->getRawOriginal();
        try {
            foreach ([$this->actor('proprietario', $owner->id), $this->actor('struttura_user', $owner->id, $structure->id)] as $actor) {
                $this->actingAs($actor)->withSession(['struttura_corrente_id' => $structure->id]);
                $this->put('/struttura', ['citta' => $comune->nome, 'logo_citta' => \Tests\Support\GeoLogoFixtures::upload('personale.png')])
                    ->assertSessionHasNoErrors()->assertRedirect();
                $this->assertSame($masterBefore, $comune->fresh()->getRawOriginal());
                $this->assertSame($otherBefore, $other->fresh()->getRawOriginal());
                $logo = $structure->fresh()->logo_citta;
                $this->assertStringStartsWith('storage/uploads/loghi_citta/strutture/'.$structure->id.'/', $logo);
                \Illuminate\Support\Facades\Storage::disk('public')->assertExists(substr($logo, strlen('storage/')));
                \Illuminate\Support\Facades\Storage::disk('public')->assertExists($globalPath);
            }
        } finally {
            \Tests\Support\GeoLogoFixtures::releaseUploads();
        }
    }

}
