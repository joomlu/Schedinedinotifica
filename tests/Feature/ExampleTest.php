<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_redirects(): void
    {
        $response = $this->get('/');

        // Si sabés a dónde redirige, especificá la ruta:
        // $response->assertRedirect('/login');
        $response->assertRedirect(); // genérico
    }
}
