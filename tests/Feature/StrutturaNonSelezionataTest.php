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
        foreach (['admin', 'super_admin'] as $role) {
            foreach (['/questura', '/istat-tabella-a', '/statistica/presenze', '/statistica/presenze/stampa-riepilogo', '/statistica/presenze/stampa-dettaglio', '/struttura', '/gruppi', '/tipovia'] as $url) {
                $cases[$role.' '.$url] = [$role, $url];
            }
        }
        return $cases;
    }

    #[DataProvider('sezioni')]
    public function test_senza_selezione_invita_a_scegliere_la_struttura(string $role, string $url): void
    {
        $admin = $this->actor($role);
        $mine = $this->structureFor($this->ownerFor($admin));
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $this->actingAs($admin)->get($url)
            ->assertRedirect(route('strutture.seleziona.index'))
            ->assertSessionHas('warning', 'Seleziona una struttura per continuare.')
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertNull(session('struttura_corrente_id'));
        $response = $this->get(route('strutture.seleziona.index'))
            ->assertOk()->assertSee('Seleziona una struttura per continuare.')
            ->assertSee($mine->nome_struttura);
        if ($role === 'admin') {
            $response->assertDontSee($foreign->nome_struttura);
        }
        $this->post(route('strutture.seleziona', $mine->id))->assertRedirect();
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
}
