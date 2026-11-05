<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\User;
use Tests\TestCase;

class SchedinaStoreTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    #[\PHPUnit\Framework\Attributes\DataProvider('tipiAlloggiato')]
    public function test_schedina_store_minimal_valid_payload_redirects_to_list_and_creates_record(string $capo, ?string $componente): void
    {
        \App\Models\GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $structure = $this->structureFor(null);
        $this->cataloghiSchedina($structure->id);
        $user = $this->actor('struttura_user', null, $structure->id);

        $payload = $this->validSchedinaPayload();

        $payload['relationship'] = $capo;
        if ($componente !== null) {
            $payload['cant_people'] = '2';
            $payload['componenti'] = [[
                'name' => 'Ospite sintetico', 'surname' => 'Esempio', 'sex' => 'F', 'relationship' => $componente,
                'exent' => 'No', 'date_nac' => '1990-01-01', 'country_nac' => 'FRANCIA', 'city_nac' => 'FRANCESE',
                'country' => 'FRANCIA', 'city' => 'Parigi',
            ]];
        }
        $response = $this->actingAs($user)->post(route('schedina.store'), $payload);

        $schedina = Schedina::query()->latest('id')->first();

        $response->assertSessionHasNoErrors();
        $this->assertNotNull($schedina);
        $response->assertRedirect(route('schedina'));
        $this->assertSame('QA', $schedina->name);
        $this->assertSame('Schedina', $schedina->surname);
        $this->assertSame('Roma', $schedina->oa_city);
        $this->assertSame('00100', $schedina->or_cap);
        $this->assertSame($capo, $schedina->relationship);
        $this->assertCount($componente === null ? 0 : 1, $schedina->componenti);
        $payload['name'] = 'QA Modificato';
        $this->put(route('schedina.update', ['id' => $schedina->id]), $payload)->assertSessionHasNoErrors();
        $this->assertSame('QA Modificato', $schedina->fresh()->name);
        $this->assertCount($componente === null ? 0 : 1, $schedina->fresh()->componenti);
    }
    public static function tipiAlloggiato(): array
    {
        return [['OSPITE SINGOLO', null], ['CAPO FAMIGLIA', 'FAMILIARE'], ['CAPO GRUPPO', 'MEMBRO GRUPPO']];
    }
}
