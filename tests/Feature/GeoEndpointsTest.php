<?php

namespace Tests\Feature;

use Tests\TestCase;

class GeoEndpointsTest extends TestCase
{
    /** @test */
    public function regions_endpoint_returns_lazio()
    {
        $resp = $this->get('/regions');
        $resp->assertOk();
        $json = $resp->json();
        $this->assertIsArray($json);
        $this->assertTrue(collect($json)->contains(function($r){
            return isset($r['denominazione_regione']) && $r['denominazione_regione'] === 'Lazio';
        }));
    }

    /** @test */
    public function provinces_all_contains_roma_rm()
    {
        $resp = $this->get('/provinces-all');
        $resp->assertOk();
        $json = $resp->json();
        $this->assertIsArray($json);
        $this->assertTrue(collect($json)->contains(function($p){
            return isset($p['sigla_provincia']) && $p['sigla_provincia'] === 'RM';
        }));
    }

    /** @test */
    public function cities_by_province_rm_contains_roma()
    {
        $resp = $this->get('/cities-by-province?sigla_provincia=RM');
        $resp->assertOk();
        $json = $resp->json();
        $this->assertIsArray($json);
        $this->assertTrue(collect($json)->contains(function($c){
            return isset($c['denominazione_ita']) && $c['denominazione_ita'] === 'Roma';
        }));
    }

    /** @test */
    public function cap_by_province_rm_contains_00100()
    {
        $resp = $this->get('/cap-by-province?sigla_provincia=RM');
        $resp->assertOk();
        $json = $resp->json();
        $this->assertIsArray($json);
        $this->assertTrue(collect($json)->contains(function($x){
            $cap = $x['cap'] ?? ($x['CAP'] ?? null);
            return $cap === '00100';
        }));
    }

    /** @test */
    public function nations_endpoint_is_available()
    {
        $resp = $this->get('/nations');
        $resp->assertOk();
        $this->assertIsArray($resp->json());
    }
}
