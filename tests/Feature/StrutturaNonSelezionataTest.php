<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class StrutturaNonSelezionataTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public static function sezioni(): array
    {
        $cases = [];
        foreach (['admin', 'super_admin', 'proprietario'] as $role) {
            foreach (['/questura', '/istat-tabella-a', '/statistica/presenze', '/statistica/presenze/stampa-riepilogo', '/statistica/presenze/stampa-dettaglio', '/struttura', '/gruppi', '/tipovia', '/clienti', '/clienti/nuovo', '/clienti/import', '/clienti/liste-export', '/strutture/utenti', '/schedine/nuova', '/tassa_di_soggiorno', '/web-checkin', '/web-checkin/nuovo'] as $url) {
                $cases[$role.' '.$url] = [$role, $url];
            }
        }
        return $cases;
    }

    #[DataProvider('sezioni')]
    public function test_senza_selezione_invita_a_scegliere_la_struttura(string $role, string $url): void
    {
        $admin = $this->actor($role);
        $owner = $this->ownerFor($role === 'proprietario' ? $this->actor('admin') : $admin);
        if ($role === 'proprietario') $admin->update(['proprietario_id' => $owner->id]);
        $mine = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($admin)->get($url)
            ->assertRedirect(route('strutture.seleziona.index'))
            ->assertSessionHas('warning', 'Seleziona una struttura per continuare.')
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertNull(session('struttura_corrente_id'));
        $response = $this->get(route('strutture.seleziona.index'))
            ->assertOk()->assertSee('Seleziona una struttura per continuare.')
            ->assertSee($mine->nome_struttura);
        if (in_array($role, ['admin', 'proprietario'], true)) {
            $response->assertDontSee($foreign->nome_struttura);
        }
        $this->assertSame($url, session(\App\Exceptions\StrutturaNonSelezionata::RETURN_TO));
        $this->post(route('strutture.seleziona', $mine->id), ['selection_context' => 'struttura_richiesta'])->assertRedirect($url);
        $this->assertNull(session(\App\Exceptions\StrutturaNonSelezionata::RETURN_TO));
        $this->assertSame($mine->id, session('struttura_corrente_id'));
        $this->get('/struttura')->assertOk()->assertSee($mine->nome_struttura);
    }

    public function test_json_e_operazioni_senza_struttura_non_procedono(): void
    {
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs($this->actor($role))->withSession(['struttura_corrente_id' => null]);
            foreach (['/questura', '/istat-tabella-a', '/statistica/presenze', '/statistica/presenze/stampa-riepilogo', '/statistica/presenze/stampa-dettaglio'] as $url) {
                $this->getJson($url)->assertStatus(409)
                    ->assertJsonPath('message', 'Seleziona una struttura per continuare.')
                    ->assertJsonPath('error_code', 'STRUTTURA_NON_SELEZIONATA')
                    ->assertJsonPath('redirect', route('strutture.seleziona.index'))
                    ->assertHeader('Cache-Control', 'no-store, private');
            }
            $this->post('/questura/ws/send')->assertRedirect(route('strutture.seleziona.index'));
            $this->postJson('/istat-tabella-a/ws/send')->assertStatus(409);
            $this->assertDatabaseCount('questura_transmissions', 0);
            $this->assertDatabaseCount('istat_transmissions', 0);
        }
    }

    public function test_riferimenti_non_autorizzati_o_invalidi_restano_vietati(): void
    {
        $admin = $this->actor('admin');
        $mine = $this->structureFor($this->ownerFor($admin));
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($admin);
        foreach (['/questura', '/istat-tabella-a', '/struttura', '/statistica/presenze', '/statistica/presenze/stampa-riepilogo', '/statistica/presenze/stampa-dettaglio'] as $url) {
            foreach (['sid='.$foreign->id, 'sid=0', 'sid[]=1', 'sid=abc', 'sid='.$mine->id.'&struttura_id='.$foreign->id] as $query) {
                $this->withSession(['struttura_corrente_id' => null])->get($url.'?'.$query)->assertForbidden();
            }
            $this->withSession(['struttura_corrente_id' => $foreign->id])->get($url)->assertForbidden();
            $this->withSession(['struttura_corrente_id' => 999999999])->get($url)->assertForbidden();
        }
    }

    public function test_il_bundle_modificato_riceve_un_url_diverso_anche_con_nome_stabile(): void
    {
        $admin = $this->actor('admin');
        $manifest = json_decode(file_get_contents(public_path('build/.vite/manifest.json')), true, 512, JSON_THROW_ON_ERROR);
        $bundle = public_path('build/'.$manifest['resources/js/app.js']['file']);
        $originale = file_get_contents($bundle);
        $url = function (): string {
            $html = $this->get(route('strutture.seleziona.index'))->assertOk()->getContent();
            $this->assertSame(1, preg_match('/<script type="module" src="([^"]+)" data-entry="app-js">/', $html, $match));
            return html_entity_decode($match[1]);
        };
        $this->actingAs($admin);
        $prima = $url();
        try {
            $modificato = $originale."\n// Prova isolata di invalidazione cache.\n";
            file_put_contents($bundle, $modificato);
            $dopo = $url();
            $this->assertNotSame($prima, $dopo);
            parse_str(parse_url($dopo, PHP_URL_QUERY), $query);
            $this->assertSame(substr(hash('sha256', $modificato), 0, 16), $query['v']);
        } finally {
            file_put_contents($bundle, $originale);
        }
    }

    public function test_proprietario_senza_selezione_json_e_scritture_non_procedono_ma_area_globale_resta_aperta(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $user = $this->actor('proprietario', $owner->id);
        $this->actingAs($user);
        $this->get(route('proprietario.clienti-condivisione.index'))->assertOk();
        $this->assertNull(session('struttura_corrente_id'));
        foreach (['/struttura', '/clienti/nuovo'] as $url) {
            $this->getJson($url)->assertStatus(409)->assertJsonPath('error_code', 'STRUTTURA_NON_SELEZIONATA');
        }
        $this->postJson('/clienti', ['name' => 'Non deve essere salvato'])->assertStatus(409);
        $this->putJson('/struttura', ['nome_struttura' => 'Non modificare'])->assertStatus(409);
        $this->assertDatabaseCount('clienti', 0);
        $this->assertSame($mine->nome_struttura, $mine->fresh()->nome_struttura);
    }

    public function test_proprietario_non_puo_usare_selezioni_estranee_per_ottenere_il_rinvio(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($this->actor('proprietario', $owner->id));
        foreach (['sid='.$foreign->id, 'sid=0', 'sid[]=1', 'sid=abc', 'sid='.$mine->id.'&struttura_id='.$foreign->id] as $query) {
            $this->withSession(['struttura_corrente_id' => null])->get('/struttura?'.$query)->assertForbidden();
        }
        $this->withSession(['struttura_corrente_id' => $foreign->id])->get('/struttura')->assertForbidden();
        $this->post(route('strutture.seleziona', $foreign->id))->assertNotFound();
    }

    public function test_destinazione_esterna_o_alterata_non_viene_usata_dal_selettore(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $hotel = $this->structureFor($owner);
        $this->actingAs($this->actor('proprietario', $owner->id));
        foreach (['https://esterno.invalid', '//esterno.invalid', '/\\esterno.invalid', "/struttura\r\nLocation: https://esterno.invalid", '/%2f%2festerno.invalid'] as $target) {
            $this->withSession([\App\Exceptions\StrutturaNonSelezionata::RETURN_TO => $target])
                ->from('/strutture/seleziona')->post(route('strutture.seleziona', $hotel->id), ['selection_context' => 'struttura_richiesta'])
                ->assertRedirect('/strutture/seleziona');
            $this->assertNull(session(\App\Exceptions\StrutturaNonSelezionata::RETURN_TO));
        }
    }

    public function test_scrittura_senza_contesto_non_viene_memorizzata_come_destinazione_da_ripetere(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $this->structureFor($owner);
        $this->actingAs($this->actor('proprietario', $owner->id));
        $this->withSession([\App\Exceptions\StrutturaNonSelezionata::RETURN_TO => '/struttura'])
            ->post('/clienti', ['name' => 'Non salvare'])->assertRedirect(route('strutture.seleziona.index'));
        $this->assertNull(session(\App\Exceptions\StrutturaNonSelezionata::RETURN_TO));
        $this->assertDatabaseCount('clienti', 0);
    }

}
