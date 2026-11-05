<?php

namespace Tests\Feature;

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

        // GET / serve il documento pubblico: non è la dashboard autenticata.
        $response->assertOk()->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('SchedinaNotifica')->assertSee('href="/contatti#demo"', false)
            ->assertSee('href="/login"', false)->assertDontSee('{{catalog.');
    }
}
