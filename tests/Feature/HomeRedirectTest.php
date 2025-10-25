<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeRedirectTest extends TestCase
{
    public function test_home_redirects(): void
    {
        $this->get('/')->assertRedirect(); // o ->assertRedirect('/login');
    }
}
