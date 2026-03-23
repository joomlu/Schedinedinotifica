<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\User;
use Tests\TestCase;

class SmokePagesQaTest extends TestCase
{
    private function authContext(): array
    {
        $user = User::query()->where('ruolo', 'super_admin')->first() ?? User::query()->first();
        $strutturaId = Struttura::query()->value('id');

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

        $schedinaId = Schedina::query()->withoutGlobalScopes()->value('id');
        $customerId = Customers::query()->withoutGlobalScopes()->value('id');

        if ($schedinaId === null || $customerId === null) {
            $this->markTestSkipped('Dataset insufficiente: servono almeno 1 schedina e 1 cliente.');
        }

        $response = $this
            ->actingAs($user)
            ->withSession(['struttura_corrente_id' => (int) $strutturaId])
            ->get("/componenti/nuovo/{$schedinaId}/{$customerId}");

        $response->assertRedirect(route('schedina.edit', ['id' => $schedinaId, 'active_tab' => 'schedina-step-comp']));
    }
}
