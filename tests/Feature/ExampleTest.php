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
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response->baseResponse);
        $this->assertSame(realpath(base_path('web/index.html')), $response->baseResponse->getFile()->getRealPath());
    }
}
