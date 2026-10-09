<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class ArriviCustomerReferenceSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public static function riferimenti(): array
    {
        return [['esterno', 'to_arrivi'], ['inesistente', 'to_arrivi'], ['malformato', 'to_arrivi'],
            ['esterno', 'draft'], ['esterno', 'full']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('riferimenti')]
    public function test_arrivo_non_salva_riferimento_cliente_di_tenant_estraneo(string $ref, string $mode): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $a->id);
        $c = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'RISERVATO-B', 'surname' => 'Sintetico']);
        $before = DB::table('clienti')->where('id', $c->id)->first();
        $this->actingAs($u);
        $snapshot = DB::table('schedina')->orderBy('id')->get()->toJson();
        $id = match ($ref) {
            'esterno' => $c->id, 'inesistente' => 999999, default => 'ID-INVALIDO'
        };
        $response = $this->postJson('/arrivi', ['save_mode' => $mode, 'customer_id' => $id, 'name' => 'Persona sintetica', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-12']);
        $this->assertEquals($before, DB::table('clienti')->where('id', $c->id)->first());
        $count = Schedina::withoutGlobalScopes()->where('struttura_id', $a->id)->where('customer_id', $c->id)->count();
        fwrite(STDOUT, '\nA15 '.json_encode(['status' => $response->status(), 'collegamenti_cross_tenant' => $count]).'\n');
        $this->assertSame(0, $count, 'Arrivo salvato con cliente di tenant non autorizzato');
        $response->assertUnprocessable()->assertJsonValidationErrors('customer_id');
        $this->assertSame($snapshot, DB::table('schedina')->orderBy('id')->get()->toJson());
    }

    public function test_cliente_proprio_e_assenza_cliente_consentiti(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $c = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Cliente A', 'surname' => 'Sintetico']);
        foreach ([$c->id, null] as $id) {
            $this->post('/arrivi', ['save_mode' => 'to_arrivi', 'customer_id' => $id, 'name' => 'Persona sintetica', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-12'])->assertSessionHasNoErrors()->assertRedirect();
            $this->assertEquals($id, Schedina::latest('id')->firstOrFail()->customer_id);
        }
        $this->assertSame(2, Schedina::count());
    }

    public function test_cliente_catena_autorizzata_localizzato_senza_modificare_origine(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $c = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente catena', 'surname' => 'Sintetico', 'num_doc_reg' => 'DOCUMENTO-SINTETICO']);
        $before = DB::table('clienti')->where('id', $c->id)->first();
        $this->actingAs($this->actor('proprietario', $owner->id))->withSession(['struttura_corrente_id' => $a->id]);
        foreach ([1, 2] as $attempt) {
            $this->post('/arrivi', ['save_mode' => 'to_arrivi', 'customer_id' => $c->id, 'name' => 'Arrivo catena', 'surname' => 'Sintetico'])->assertSessionHasNoErrors()->assertRedirect();
        }
        $local = Customers::withoutGlobalScopes()->where('struttura_id', $a->id)->sole();
        $this->assertSame(2, Customers::withoutGlobalScopes()->count());
        $this->assertSame(2, Schedina::withoutGlobalScopes()->where('struttura_id', $a->id)->where('customer_id', $local->id)->count());
        $this->assertNotEmpty($local->numero_cliente);
        $this->assertEquals($before, DB::table('clienti')->where('id', $c->id)->first());
    }
}
