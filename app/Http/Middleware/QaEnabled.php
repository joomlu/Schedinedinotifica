<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QaEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = app()->environment() !== 'production' || (bool) env('QA_ENABLED', false);

        if (!$enabled) {
            abort(403, 'QA non abilitata in produzione.');
        }

        return $next($request);
    }
}
