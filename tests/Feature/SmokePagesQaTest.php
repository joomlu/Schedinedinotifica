<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class SmokePagesQaTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function authContext(): array
    {
        $user = $this->actor('super_admin');
        $strutturaId = $this->structureFor(null)->id;

        $this->assertNotNull($user, 'Nessun utente disponibile per test.');
        $this->assertNotNull($strutturaId, 'Nessuna struttura disponibile per test.');

        return [$user, $strutturaId];
    }

    public function test_clienti_nuovo_page_loads(): void
    {
        [$user, $strutturaId] = $this->authContext();

        $response = $this
            ->actingAs($user)
            ->withSession(['struttura_corrente_id' => (int) $strutturaId])
            ->get('/clienti/nuovo');

        $response->assertOk();
    }

    public function test_schedine_nuova_page_loads(): void
    {
        [$user, $strutturaId] = $this->authContext();

        $response = $this
            ->actingAs($user)
            ->withSession(['struttura_corrente_id' => (int) $strutturaId])
            ->get('/schedine/nuova');

        $response->assertOk();
    }

    public function test_arrivi_nuovo_page_loads(): void
    {
        [$user, $strutturaId] = $this->authContext();

        $response = $this
            ->actingAs($user)
            ->withSession(['struttura_corrente_id' => (int) $strutturaId])
            ->get('/arrivi/nuovo');

        $response->assertOk();
    }

    public function test_componenti_nuovo_page_loads(): void
    {
        [$user, $strutturaId] = $this->authContext();

        $customerId = Customers::create(['struttura_id' => $strutturaId, 'name' => 'Cliente sintetico', 'surname' => 'Smoke'])->id;
        $schedinaId = Schedina::forceCreate(['struttura_id' => $strutturaId, 'customer_id' => $customerId,
            'name' => 'Ospite sintetico', 'surname' => 'Smoke', 'arrive' => '2026-06-10', 'departure' => '2026-06-11'])->id;

        $response = $this
            ->actingAs($user)
            ->withSession(['struttura_corrente_id' => (int) $strutturaId])
            ->get("/componenti/nuovo/{$schedinaId}/{$customerId}");

        $response->assertRedirect(route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp']));
    }
}
