<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ruolo
{
    public function handle(Request $request, Closure $next, string $ruoli): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $allowed = collect(explode(',', $ruoli))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->all();

        if (empty($allowed)) {
            abort(403);
        }

        if (!in_array($user->ruolo, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
