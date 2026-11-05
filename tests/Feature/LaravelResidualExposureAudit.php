<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LaravelResidualExposureAudit extends TestCase
{
    use RefreshDatabase;

    public function test_email_crlf_rifiutata_anche_annidata_e_reset_non_invia(): void
    {
        Mail::fake();
        $malicious = "\"prima\r\n seconda\"@example.test";
        $this->assertTrue((new \Illuminate\Validation\Validator(app('translator'), ['email' => $malicious], ['email' => 'email']))->passes());
        $this->assertFalse(Validator::make(['email' => $malicious], ['email' => 'email'])->passes());
        $this->assertFalse(Validator::make(['ospiti' => [['email' => $malicious]]], ['ospiti.*.email' => 'email'])->passes());
        $this->assertTrue(Validator::make(['email' => 'utente+etichetta@example.test'], ['email' => 'email'])->passes());
        $this->assertTrue(Validator::make(['email' => null], ['email' => 'nullable|email'])->passes());
        $this->assertFalse(Validator::make(['email' => 'indirizzo-non-valido'], ['email' => 'email'])->passes());
        $this->postJson('/password/email', ['email' => $malicious])->assertUnprocessable()->assertJsonValidationErrors('email');
        Mail::assertNothingSent();
    }

    public function test_route_non_usano_url_firmati_e_avatar_array_non_elude_la_regola_scalare(): void
    {
        $signed = collect(app('router')->getRoutes()->getRoutes())->filter(fn ($route) => collect($route->gatherMiddleware())->contains(fn ($middleware) => str_starts_with($middleware, 'signed') || str_contains($middleware, 'ValidateSignature')));
        $this->assertCount(0, $signed);
        $this->assertFalse(is_subclass_of(User::class, \Illuminate\Contracts\Auth\MustVerifyEmail::class));
        $before = User::count();
        $this->postJson('/register', ['name' => 'Sintetico', 'email' => 'registrazione@example.test', 'password' => 'Password-sintetica-123!', 'password_confirmation' => 'Password-sintetica-123!', 'avatar' => [UploadedFile::fake()->image('sintetica.png')]])
            ->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame($before, User::count());
    }
}
