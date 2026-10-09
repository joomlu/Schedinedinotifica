<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class BaselineAcceptanceArriviAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_arrivo_non_salva_riferimento_cliente_di_tenant_estraneo(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $a->id);
        $c = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'RISERVATO-B', 'surname' => 'Sintetico']);
        $before = DB::table('clienti')->where('id', $c->id)->first();
        $this->actingAs($u);
        $response = $this->postJson('/arrivi', ['save_mode' => 'to_arrivi', 'customer_id' => $c->id, 'name' => 'Persona sintetica', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-12']);
        $this->assertEquals($before, DB::table('clienti')->where('id', $c->id)->first());
        $count = Schedina::withoutGlobalScopes()->where('struttura_id', $a->id)->where('customer_id', $c->id)->count();
        fwrite(STDOUT, '\nA15 '.json_encode(['status' => $response->status(), 'collegamenti_cross_tenant' => $count]).'\n');
        $this->assertSame(0, $count, 'Arrivo salvato con cliente di tenant non autorizzato');
        $response->assertUnprocessable();
    }
}
