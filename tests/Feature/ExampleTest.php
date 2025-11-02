<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        // In ambiente non autenticato la home reindirizza al login
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
