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
            ->get('/arrivi/nuovo')->assertOk()->assertViewIs('arrivals.new')
            ->assertViewHas('esenzioni', [])->assertSee('Nessuna esenzione')
            ->assertSee(route('arrival.store'), false);
        $this->assertDatabaseCount('schedina', 0);
        $this->assertDatabaseCount('tassa_exports', 0);
        $this->assertDatabaseCount('tassa_esenzioni', 0);
    }
}
