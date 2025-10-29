<?php

use App\Models\User;

it('redirects guests from /health-auth', function () {
    $this->get('/health-auth')->assertRedirect('/login');
});

it('returns ok and user payload for authenticated users', function () {
    $user = User::factory()->create([
        'avatar' => 'test.png',
        // Se il model ha una colonna role la valorizziamo per completezza
        'role' => 'superadmin',
    ]);

    $response = $this->actingAs($user)->get('/health-auth');

    $response->assertOk();
    $response->assertJsonPath('ok', true);
    $response->assertJsonPath('user.email', $user->email);
    $response->assertJsonPath('user.id', $user->id);
});
