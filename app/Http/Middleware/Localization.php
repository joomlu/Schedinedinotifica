<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Sistema monolingua: forza sempre italiano.
        App::setLocale('it');
        if (session()->get('lang') !== 'it') {
            session()->put('lang', 'it');
        }

        return $next($request);
    }
}
