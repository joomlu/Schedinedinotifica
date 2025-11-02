<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    /** @test */
    public function it_returns_ok_json_from_health_endpoint(): void
    {
        $response = $this->get('/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status', 'app', 'env', 'time',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}
