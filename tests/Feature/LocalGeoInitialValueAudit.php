<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalGeoInitialValueAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_form_schedina_conserva_nazioni_principale_e_componenti(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'GEO-SINTETICO', 'surname' => 'Audit', 'circuito' => 'web', 'oa_country' => 'ITALIA', 'or_country' => 'GERMANIA']);
        Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'name' => 'COMPONENTE-GEO', 'surname' => 'Audit', 'country_nac' => 'FRANCIA', 'country' => 'SPAGNA']);
        $html = $this->actingAs($u)->get('/schedine/'.$p->id.'/modifica')->assertOk()->getContent();
        preg_match_all("/data-initial='([^']+)'/", $html, $m);
        $nations = array_column(array_map(fn ($v) => json_decode(html_entity_decode($v, ENT_QUOTES), true), $m[1]), 'nazione_text');
        foreach (['ITALIA', 'GERMANIA', 'FRANCIA', 'SPAGNA'] as $n) {
            $this->assertContains($n, $nations);
        }
    }
}
