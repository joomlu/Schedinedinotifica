<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicSiteFallback
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user(), 404);

        return $next($request);
    }
}
