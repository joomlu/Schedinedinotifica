<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\User;
use Tests\TestCase;

class SchedinaStoreTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase, \Tests\Support\StrutturaFixtures;

    #[\PHPUnit\Framework\Attributes\DataProvider('tipiAlloggiato')]
    public function test_schedina_store_minimal_valid_payload_redirects_to_list_and_creates_record(string $capo, ?string $componente): void
    {
        \App\Models\GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
        $structure = $this->structureFor(null);
        $user = $this->actor('struttura_user', null, $structure->id);

        $payload = [
            'customer_privacy_consent' => '1',
            'name' => 'QA',
            'surname' => 'Schedina',
            'sex' => 'M',
            'arrive' => '2026-03-15',
            'departure' => '2026-03-17',
            'cant_people' => '1',
            'room' => '1',
            'beds' => '1',
            'oa_country' => 'ITALIA',
            'oa_region' => 'Lazio',
            'oa_prov' => 'RM',
            'oa_city' => 'Roma',
            'oa_city_nac' => 'ITALIANA',
            'oa_date_nac' => '1990-01-01',
            'or_country' => 'ITALIA',
            'or_region' => 'Lazio',
            'or_prov' => 'RM',
            'or_city' => 'Roma',
            'or_cap' => '00100',
            'or_typeaway' => 'Via',
            'or_address' => 'Via Test',
            'or_num' => '10',
            'or_doctype' => "CARTA DI IDENTITA'",
            'or_doc' => 'QATEST',
            'or_published_date' => '2025-01-01',
            'or_expire' => '2030-01-01',
            'or_published' => 'Comune di Roma',
            'or_published_country' => 'ITALIA',
            'or_published_city' => 'Roma',
        ];

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
