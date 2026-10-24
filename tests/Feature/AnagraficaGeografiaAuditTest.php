<?php

namespace Tests\Feature;

use App\Models\{GeoNazione, GeoRegione, GeoProvincia, GeoComune, GeoCap, GeoComuneCap};
use App\Services\AnagraficaGeografia;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class AnagraficaGeografiaAuditTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    public function test_cap_risolto_tramite_relazione_senza_colonna_inesistente_nel_pivot(): void
    {
        $n = GeoNazione::forceCreate(['codice_iso2' => 'ZQ', 'nome' => 'Italia']);
        $r = GeoRegione::forceCreate(['geo_nazione_id' => $n->id, 'codice_regione' => 'AUDIT', 'nome' => 'Regione audit']);
        $p = GeoProvincia::forceCreate(['geo_regione_id' => $r->id, 'sigla' => 'ZQ', 'nome' => 'Provincia audit']);
        $c = GeoComune::forceCreate(['geo_provincia_id' => $p->id, 'codice_istat' => 'AUDIT-GEO', 'nome' => 'Comune audit']);
        $cap = GeoCap::firstOrCreate(['cap' => '00001']);
        GeoComuneCap::forceCreate(['geo_comune_id' => $c->id, 'geo_cap_id' => $cap->id]);
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $s->update(['nazione' => 'Italia', 'regione' => $r->nome, 'provincia' => $p->sigla, 'citta' => $c->nome, 'cap' => $cap->cap]);
        $service = new AnagraficaGeografia;
        $this->assertSame($c->id, $service->form($s)['comune_id']);
        $this->assertSame('00001', $service->form($s)['cap']);
        $this->actingAs($this->actor('super_admin'))->get('/superadmin/strutture/'.$s->id.'/edit')->assertOk()->assertSee('classificazione_id');
        $this->actingAs($admin)->get('/admin/strutture/'.$s->id.'/edit')->assertOk()->assertSee('classificazione_id');
        $this->actingAs($this->actor('admin'))->get('/admin/strutture/'.$s->id.'/edit')->assertNotFound();
        // Un CAP esistente ma appartenente a un altro Comune non basta.
        $other = GeoComune::forceCreate(['geo_provincia_id' => $p->id, 'codice_istat' => 'AUDIT-GEO-2', 'nome' => 'Altro Comune audit']);
        $otherCap = GeoCap::firstOrCreate(['cap' => '00002']);
        GeoComuneCap::forceCreate(['geo_comune_id' => $other->id, 'geo_cap_id' => $otherCap->id]);
        $s->cap = '00002';
        $this->assertNull($service->form($s));
    }
}
