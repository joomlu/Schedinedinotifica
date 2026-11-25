<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\StrutturaAccess;
use App\Support\StrutturaCorrente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AutenticaAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->isJson() && strlen($request->getContent()) <= 131072, 413);
        $key = (string) $request->header('X-Access-Connessione', '');
        $config = config('access.connections.'.$key);
        $token = $request->bearerToken();
        abort_unless(preg_match('/^[a-z0-9_-]{1,80}$/D', $key) && is_array($config)
            && ($config['enabled'] ?? false) === true && is_string($token) && strlen($token) >= 43
            && is_string($config['token_hash'] ?? null)
            && hash_equals($config['token_hash'], hash('sha256', $token)), 401);
        $user = User::find($config['user_id'] ?? 0);
        $sid = (int) ($config['struttura_id'] ?? 0);
        // Un trasferimento di proprieta o la disattivazione revoca il collegamento.
        $struttura = StrutturaAccess::query($user)->whereKey($sid)->where('attiva', true)
            ->where('proprietario_id', $config['proprietario_id'] ?? 0)->first();
        abort_unless($user && $struttura && $struttura->servizioAttivo(), 403);
        $previousUser = Auth::user();
        $previousId = StrutturaCorrente::getId();
        Auth::setUser($user);
        StrutturaCorrente::setId($sid);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('access_struttura', $struttura);
        $request->attributes->set('access_connessione', $key);
        try {
            $response = $next($request);
            $response->headers->set('Cache-Control', 'no-store');
            return $response;
        } finally {
            StrutturaCorrente::setId($previousId);
            $previousUser ? Auth::setUser($previousUser) : Auth::forgetUser();
        }
    }
}
