<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinParentSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        // Contratto approvato: fixture attive nel loro periodo; originale conservato in Diagnostics.
        $this->travelTo(\Carbon\Carbon::parse('2026-04-01 12:00:00', 'Europe/Rome'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function richiesta(int $tenant, ?int $parent, string $stato = 'convertito'): WebCheckinRichiesta
    {
        return WebCheckinRichiesta::create(['struttura_id' => $tenant, 'schedina_id' => $parent, 'codice' => 'AUDIT', 'numero_prenotazione' => 'SINTETICA', 'email' => 'audit@example.invalid', 'nome_referente' => 'Persona sintetica A', 'arrivo' => '2026-06-01', 'partenza' => '2026-06-02', 'quantita_persone' => 1, 'token' => str_repeat('a', 64), 'stato' => $stato]);
    }

    private function stato(): array
    {
        $rows = [];
        foreach (['schedina', 'schedina_camere', 'componenti', 'web_checkin_richieste', 'clienti', 'cestino_items'] as $table) {
            $rows[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        return $rows;
    }

    public static function percorsi(): array
    {
        return [['GET', '/checkin/%s'], ['POST', '/checkin/%s'], ['GET', '/checkin/%s/completato'], ['GET', '/w/%s'], ['POST', '/w/%s'], ['GET', '/w/%s/completato']];
    }

    #[DataProvider('percorsi')]
    public function test_parent_estero_non_espone_o_modifica_dati(string $method, string $path): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'RISERVATO-TENANT-B', 'surname' => 'Sintetico', 'arrive' => '2026-06-01', 'departure' => '2026-06-02', 'cant_people' => 1]);
        $r = $this->richiesta($a->id, $p->id);
        $before = $this->stato();
        $response = $this->call($method, sprintf($path, $r->token));
        $this->assertSame($before, $this->stato());
        $response->assertNotFound()->assertDontSee('RISERVATO-TENANT-B');
    }

    public static function percorsiPrivati(): array
    {
        return [['GET', '/web-checkin/%d/modifica'], ['PUT', '/web-checkin/%d'], ['DELETE', '/web-checkin/%d'], ['POST', '/web-checkin/%d/converti'], ['GET', '/web-checkin']];
    }

    #[DataProvider('percorsiPrivati')]
    public function test_gestione_privata_blocca_parent_incoerente(string $method, string $path): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'RISERVATO-TENANT-B', 'surname' => 'Sintetico']);
        $r = $this->richiesta($a->id, $p->id, 'da_inviare');
        $r->update(['token' => '']);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $before = $this->stato();
        $response = $this->call($method, str_contains($path, '%') ? sprintf($path, $r->id) : $path);
        $this->assertSame($before, $this->stato());
        $response->assertNotFound()->assertDontSee('RISERVATO-TENANT-B');
    }

    public function test_parent_mancante_non_viene_riparato_silenziosamente(): void
    {
        $a = $this->structureFor(null);
        $r = $this->richiesta($a->id, 999999);
        $before = $this->stato();
        $this->get('/checkin/'.$r->token)->assertNotFound();
        $this->assertSame($before, $this->stato());
    }

    public function test_parent_autorizzato_restato_accessibile_e_richiesta_estranea_negata(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'VISIBILE-TENANT-A', 'surname' => 'Sintetico', 'arrive' => '2026-06-01', 'departure' => '2026-06-02', 'cant_people' => 1]);
        $r = $this->richiesta($a->id, $p->id);
        $before = $this->stato();
        $this->get('/checkin/'.$r->token)->assertOk()->assertContent('Check-in recibido');
        $this->get('/checkin/'.$r->token.'/completato')->assertOk();
        $this->assertSame($before, $this->stato());
        $this->actingAs($this->actor('struttura_user', null, $b->id))->get('/web-checkin/'.$r->id.'/modifica')->assertNotFound();
    }

    public function test_creazione_autorizzata_associa_parent_e_tenant_e_modifica_funzionante(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $data = ['numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-01', 'partenza' => '2026-06-02', 'quantita_persone' => 1];
        $this->post('/web-checkin', $data)->assertSessionHasNoErrors()->assertRedirect();
        $r = WebCheckinRichiesta::sole();
        $p = Schedina::findOrFail($r->schedina_id);
        $this->assertSame($a->id, (int) $p->struttura_id);
        $this->get('/web-checkin/'.$r->id.'/modifica')->assertOk();
        $data['nome_referente'] = 'Sintetico aggiornato';
        $this->put('/web-checkin/'.$r->id, $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Sintetico aggiornato', $p->fresh()->name);
    }
}
