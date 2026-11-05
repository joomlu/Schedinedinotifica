<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class SchedinaCustomerReferenceSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function stato(): array
    {
        $rows = [];
        foreach (['schedina', 'schedina_camere', 'componenti', 'clienti'] as $table) {
            $rows[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        return $rows;
    }

    public static function rifiuti(): array
    {
        $out = [];
        foreach (['store', 'update'] as $action) {
            foreach (['draft', 'full', 'to_arrivi', 'componenti', 'component'] as $mode) {
                foreach (['estero', 'inesistente', 'malformato'] as $ref) {
                    $out[$action.'-'.$mode.'-'.$ref] = [$action, $mode, $ref];
                }
            }
        }

        return $out;
    }

    #[DataProvider('rifiuti')]
    public function test_riferimento_non_autorizzato_bloccato_prima_di_ogni_scrittura(string $action, string $mode, string $ref): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $foreign = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente B', 'surname' => 'Sintetico']);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $own = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Originale A', 'surname' => 'Sintetico', 'circuito' => 'bozza']);
        $id = match ($ref) {
            'estero' => $foreign->id,'inesistente' => 999999,default => 'ID-non-valido'
        };
        $payload = ['save_mode' => $mode, 'customer_id' => $id, 'name' => 'Modificato', 'surname' => 'Sintetico'];
        $before = $this->stato();
        $r = $action === 'store' ? $this->postJson(route('schedina.store'), $payload) : $this->putJson(route('schedina.update', ['id' => $own->id]), $payload);
        $this->assertSame($before, $this->stato());
        $r->assertUnprocessable()->assertJsonValidationErrors('customer_id');
    }

    public function test_cliente_proprio_in_bozza_creazione_e_modifica_consentite(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $c = Customers::create(['struttura_id' => $a->id, 'name' => 'Cliente A', 'surname' => 'Sintetico']);
        $data = ['save_mode' => 'draft', 'customer_id' => $c->id, 'name' => 'Bozza A', 'surname' => 'Sintetico'];
        $this->post(route('schedina.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $s = Schedina::sole();
        $this->assertSame($c->id, (int) $s->customer_id);
        $this->assertSame($a->id, (int) $s->struttura_id);
        $data['name'] = 'Bozza aggiornata';
        $this->put(route('schedina.update', ['id' => $s->id]), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Bozza aggiornata', $s->fresh()->name);
        $this->assertSame($c->id, (int) $s->fresh()->customer_id);
    }

    public function test_riuso_cliente_della_catena_localizzato_senza_duplicazioni(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $c = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente catena', 'surname' => 'Sintetico', 'num_doc_reg' => 'DOC-SINTETICO-1']);
        $before = $c->fresh()->getRawOriginal();
        $this->actingAs($this->actor('proprietario', $owner->id))->withSession(['struttura_corrente_id' => $a->id]);
        $data = ['save_mode' => 'draft', 'customer_id' => $c->id, 'name' => 'Bozza catena', 'surname' => 'Sintetico'];
        foreach ([1, 2] as $attempt) {
            $this->post(route('schedina.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        }
        $local = Customers::withoutGlobalScopes()->where('struttura_id', $a->id)->sole();
        $this->assertSame(2, Customers::withoutGlobalScopes()->count());
        $this->assertSame(2, Schedina::withoutGlobalScopes()->where('struttura_id', $a->id)->where('customer_id', $local->id)->count());
        $this->assertSame($before, $c->fresh()->getRawOriginal());
    }

    public static function scritture(): array
    {
        return [['store'], ['update']];
    }

    #[DataProvider('scritture')]
    public function test_componente_esterno_rifiutato_con_rollback_del_parent_e_del_cliente_localizzato(string $action): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $customer = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Cliente catena', 'surname' => 'Sintetico', 'num_doc_reg' => 'DOC-SINTETICO-2']);
        $foreign = Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'Parent B', 'surname' => 'Sintetico']);
        $comp = Componenti::forceCreate(['struttura_id' => $b->id, 'schedina_id' => $foreign->id, 'name' => 'Componente B', 'surname' => 'Sintetico']);
        $own = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Originale A', 'surname' => 'Sintetico', 'circuito' => 'bozza']);
        $this->actingAs($this->actor('proprietario', $owner->id))->withSession(['struttura_corrente_id' => $a->id]);
        $data = ['save_mode' => 'draft', 'customer_id' => $customer->id, 'name' => 'Modificato', 'surname' => 'Sintetico', 'componenti' => [['id' => $comp->id, 'name' => 'Estraneo', 'surname' => 'Sintetico', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']]];
        $before = $this->stato();
        $r = $action === 'store' ? $this->postJson(route('schedina.store'), $data) : $this->putJson(route('schedina.update', ['id' => $own->id]), $data);
        $this->assertSame($before, $this->stato());
        $r->assertUnprocessable()->assertJsonValidationErrors('componenti.0.id');
    }
}
