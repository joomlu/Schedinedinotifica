<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_is_ok(): void
    {
        $this->get(route('health'))->assertOk();
    }
}
