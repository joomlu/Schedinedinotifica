<?php

namespace Tests\Feature;

use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class SchedinaNavigationTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_link_conservano_ricerche_e_pagine_distinte_per_struttura(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $this->get('/schedine?q=ElencoSintetico&page=2')->assertOk();
        $this->get('/schedine/bozze?q=BozzaSintetica&page=3')->assertOk();
        $this->get('/schedine/nuova')->assertOk()
            ->assertSee('/schedine?q=ElencoSintetico&amp;page=2', false)
            ->assertSee('/schedine/bozze?q=BozzaSintetica&amp;page=3', false);
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get('/schedine/nuova')->assertOk()->assertDontSee('ElencoSintetico')->assertDontSee('BozzaSintetica');
    }

    public function test_salvataggio_incompleto_ripresa_e_diniego_altra_struttura(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $this->post('/schedine', ['save_mode' => 'draft', 'name' => 'BozzaSintetica', 'surname' => 'Navigazione'])
            ->assertSessionHasNoErrors()->assertRedirect(route('schedina.bozze'));
        $draft = Schedina::firstOrFail();
        $this->assertSame('bozza', $draft->circuito);
        $this->get('/schedine/bozze')->assertOk()->assertSee('Riprendi compilazione');
        $this->get(route('schedina.edit', $draft->id))->assertOk()->assertSee('BozzaSintetica');
        $this->put(route('schedina.update', $draft->id), ['save_mode' => 'draft', 'name' => 'BozzaRipresa', 'surname' => 'Navigazione'])
            ->assertSessionHasNoErrors()->assertRedirect(route('schedina.bozze'));
        $this->assertSame('BozzaRipresa', $draft->fresh()->name);
        $this->assertSame('bozza', $draft->fresh()->circuito);
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get('/schedine/bozze')->assertOk()->assertDontSee('BozzaRipresa');
        $this->get(route('schedina.edit', $draft->id))->assertNotFound();
    }
}
