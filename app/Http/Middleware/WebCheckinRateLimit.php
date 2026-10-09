<?php

namespace App\Http\Middleware;

use App\Services\WebCheckinLink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebCheckinRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        if (! preg_match('#^(?:checkin|w)/([^/]+)(?:/completato)?$#', $request->path(), $parts)) {
            return $next($request);
        }
        $ip = hash('sha256', (string) $request->ip());
        $retry = $this->hit('ingresso:'.$ip, 300, 60);
        if (! $retry) {
            $r = app(WebCheckinLink::class)->resolve(rawurldecode($parts[1]));
            if ($r) {
                $post = $request->isMethod('POST');
                $retry = $this->hit(($post ? 'scritture:' : 'letture:').$r->struttura_id.':'.$r->id.':'.$ip,
                    $post ? 20 : 60, $post ? 600 : 60);
            }
        }
        if ($retry) {
            return response('Troppe richieste.', 429)->header('Retry-After', (string) $retry);
        }
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    private function hit(string $key, int $max, int $seconds): int
    {
        // Store locale persistente e lock condiviso fra processi, non cache array per richiesta.
        $store = Cache::store('file');
        $key = 'web-checkin:'.hash('sha256', $key);

        return $store->lock($key.':lock', 10)->block(5, function () use ($store, $key, $max, $seconds) {
            $now = now()->getTimestamp();
            $counter = $store->get($key);
            if (! $counter || $counter['until'] <= $now) {
                $counter = ['count' => 0, 'until' => $now + $seconds];
            }
            if ($counter['count'] >= $max) {
                return max(1, $counter['until'] - $now);
            }
            $counter['count']++;
            $store->put($key, $counter, $counter['until'] - $now);

            return 0;
        });
    }
}
