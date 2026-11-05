<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class ConfigurazioneStrutturaAllineataTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_impostazioni_complete_nei_dati_struttura_e_operativita_senza_credenziali(): void
    {
        $admin = $this->actor('admin');
        $struttura = $this->structureFor($this->ownerFor($admin));
        $struttura->update(['regione' => 'Emilia-Romagna', 'questura_profilo' => 'appartamenti', 'questura_appartamento_id' => 123,
            'istat_username' => 'UTENTE-SINTETICO', 'istat_password' => 'SEGRETO-SINTETICO', 'istat_codice_struttura' => 'CODICE-SINTETICO']);
        $this->actingAs($admin)->get('/struttura?sid='.$struttura->id)->assertOk()
            ->assertSee('Profilo Alloggiati Web')->assertSee('ID appartamento Alloggiati Web')->assertSee('Configurazione Ross1000')
            ->assertSee('form="ross1000-config-form"', false)->assertDontSee('UTENTE-SINTETICO')->assertDontSee('SEGRETO-SINTETICO')
            ->assertDontSee('href="'.route('struttura.edit').'#configurazione-questura"', false);
        $this->get('/istat-tabella-a?sid='.$struttura->id)->assertOk()
            ->assertDontSee('name="password"', false)->assertDontSee('Verifica configurazione Tabella A')
            ->assertSee('#configurazione-ross1000', false);
    }

    public function test_salvataggio_separato_preserva_credenziali_e_dati_anagrafici(): void
    {
        Http::preventStrayRequests();
        $admin = $this->actor('admin');
        $struttura = $this->structureFor($this->ownerFor($admin));
        $struttura->update(['istat_username' => 'UTENTE-SINTETICO', 'istat_password' => 'SEGRETO-SINTETICO',
            'questura_profilo' => 'appartamenti', 'questura_appartamento_id' => 123]);
        $before = $struttura->getRawOriginal();
        $this->actingAs($admin)->post('/istat-tabella-a/configurazione?struttura_id='.$struttura->id, ['struttura_id' => $struttura->id,
            'username' => '', 'password' => '', 'codice' => 'NUOVO-CODICE'])->assertRedirect(route('struttura.edit', ['struttura_id' => $struttura->id]).'#configurazione-ross1000');
        $after = $struttura->fresh()->getRawOriginal();
        foreach ($before as $key => $value) {
            if (! in_array($key, ['istat_codice_struttura', 'istat_ws_url', 'updated_at'], true)) {
                $this->assertSame($value, $after[$key], $key);
            }
        }
        $this->assertSame('NUOVO-CODICE', $struttura->fresh()->istat_codice_struttura);
        Http::assertNothingSent();
    }

    public function test_configurazione_non_scrive_su_struttura_estranea(): void
    {
        $admin = $this->actor('admin');
        $other = $this->structureFor($this->ownerFor($this->actor('admin')));
        $before = $other->getRawOriginal();
        $this->actingAs($admin)->post('/istat-tabella-a/configurazione?struttura_id='.$other->id, ['struttura_id' => $other->id, 'codice' => 'NON-AUTORIZZATO'])->assertForbidden();
        $this->assertSame($before, $other->fresh()->getRawOriginal());
    }
}
