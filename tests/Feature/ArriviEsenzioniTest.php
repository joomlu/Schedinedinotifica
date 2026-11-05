<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class ArriviEsenzioniTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_nuovo_arrivo_con_catalogo_array_vuoto_si_apre_senza_scritture(): void
    {
        $s = $this->structureFor(null);
        $user = $this->actor('struttura_user', null, $s->id);
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id])
            ->get('/arrivi/nuovo')->assertRedirect(route('newschedina', ['arrival'=>1]));
        $this->get(route('newschedina', ['arrival'=>1]))->assertOk()->assertSee('Scheda in attesa')->assertSee('Salva in Arrivi')->assertViewHas('tassaDettaglio', fn($v)=>$v['righe']===[] && $v['totale']===null)->assertDontSee('Anteprima ricevuta');
        $this->assertDatabaseCount('schedina', 0);
        $this->assertDatabaseCount('tassa_exports', 0);
        $this->assertDatabaseCount('tassa_esenzioni', 0);
    }
}
