<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Middleware\AuthenticateSession;

class AuthenticateAccountSession extends AuthenticateSession
{
    public function handle($request, Closure $next)
    {
        if ($request->hasSession() && $request->user()) {
            $hashKey = 'password_hash_'.$this->auth->getDefaultDriver();
            // Una sessione precedente al controllo non ha un hash affidabile.
            // Richiedere un nuovo login evita di adottare una password già cambiata.
            if (! $request->user()->attivo || ! $request->session()->has($hashKey)) {
                $this->logout($request);
            }
        }

        return parent::handle($request, $next);
    }
}
