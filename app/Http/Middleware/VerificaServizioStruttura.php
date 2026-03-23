<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificaServizioStruttura
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if ((method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
            return $next($request);
        }

        $currentId = StrutturaCorrente::getId();
        if ($currentId === null && method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            $currentId = $user->struttura_id;
        }

        if ($currentId === null) {
            abort(403, 'Servizio non disponibile per questa struttura.');
        }

        $struttura = Struttura::find($currentId);
        if (!$struttura) {
            abort(403, 'Servizio non disponibile per questa struttura.');
        }

        if (method_exists($struttura, 'servizioAttivo') && !$struttura->servizioAttivo()) {
            abort(403, 'Servizio sospeso o scaduto per questa struttura.');
        }

        return $next($request);
    }
}
