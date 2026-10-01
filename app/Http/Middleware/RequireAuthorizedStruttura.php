<?php

namespace App\Http\Middleware;

use App\Support\StrutturaAccess;
use App\Support\StrutturaCorrente;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthorizedStruttura
{
    public function handle(Request $request, Closure $next): Response
    {
        $struttura = StrutturaAccess::resolve($request);
        $request->attributes->set(StrutturaAccess::ATTRIBUTE, $struttura);
        StrutturaCorrente::setId((int) $struttura->id);
        return $next($request);
    }
}
