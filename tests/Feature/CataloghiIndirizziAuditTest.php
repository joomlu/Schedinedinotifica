<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CataloghiIndirizziAuditTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    public function test_autorita_creata_in_una_struttura_non_compare_nell_altra(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id))
            ->withSession(['struttura_corrente_id' => $a->id]);
        $this->post('/rilasciato', ['name' => 'AutoritaPrivataAuditA'])->assertSessionHasNoErrors();
        $this->actingAs($this->actor('struttura_user', null, $b->id))
            ->withSession(['struttura_corrente_id' => $b->id]);
        $this->get('/rilasciato?q=AutoritaPrivataAuditA')->assertOk()->assertViewHas('rilasciati', fn ($p) => $p->total() === 0);
    }

    public function test_tipo_via_creato_in_una_struttura_non_modificabile_dall_altra(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id))
            ->withSession(['struttura_corrente_id' => $a->id]);
        $this->post('/tipovia_store', ['abbr' => 'Audit privata A', 'attivo' => 1])->assertSessionHasNoErrors();
        $id = DB::table('tipo_via')->where('abbr', 'Audit privata A')->value('id');
        $this->actingAs($this->actor('struttura_user', null, $b->id))
            ->withSession(['struttura_corrente_id' => $b->id]);
        $this->put('/tipovia_update/'.$id, ['abbr' => 'Manipolata B', 'attivo' => 1])->assertNotFound();
        $this->assertDatabaseHas('tipo_via', ['id' => $id, 'abbr' => 'Audit privata A']);
    }

    public function test_ricerca_non_scalare_viene_rifiutata_senza_errore_server(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin', null, $a->id))
            ->withSession(['struttura_corrente_id' => $a->id]);
        foreach (['/rilasciato', '/tipovia', '/geo/comuni/logo'] as $url) {
            $this->get($url.'?q[]=forgiato')->assertStatus(400);
            $this->get($url.'?q=')->assertOk();
        }
    }
}
