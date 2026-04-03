<?php

namespace App\Http\Middleware;

use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpostaStrutturaCorrente
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            // Super admin può operare senza filtro; se presente, conserva la selezione corrente per coerenza UI.
            $selected = $request->session()->get('struttura_corrente_id');
            if ($selected) {
                StrutturaCorrente::setId((int) $selected);
            }
            return $next($request);
        }

        if (method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            StrutturaCorrente::setId($user->struttura_id);
            return $next($request);
        }

        if (method_exists($user, 'isProprietario') && $user->isProprietario()) {
            if (!\Schema::hasTable('proprietari')) {
                return $next($request); // evita errore se migrazioni non eseguite
            }
            $allowed = Struttura::where('proprietario_id', $user->proprietario_id)->pluck('id')->all();
            $currentId = $this->resolveCurrentId($request, $allowed);
            if ($currentId === null) {
                abort(403, 'Nessuna struttura associata.');
            }
            StrutturaCorrente::setId($currentId);
            return $next($request);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            if (!\Schema::hasTable('proprietari')) {
                return $next($request); // evita errore se migrazioni non eseguite
            }
            $allowed = Struttura::where(function ($q) use ($user) {
                $q->whereHas('proprietario', function ($q2) use ($user) {
                    $q2->where('admin_id', $user->id);
                })->orWhereNull('proprietario_id'); // legacy senza proprietario
            })->pluck('id')->all();
            $currentId = $this->resolveCurrentId($request, $allowed);
            if ($currentId === null) {
                abort(403, 'Nessuna struttura disponibile.');
            }
            StrutturaCorrente::setId($currentId);
            return $next($request);
        }

        return $next($request);
    }

    protected function resolveCurrentId(Request $request, array $allowed): ?int
    {
        if (empty($allowed)) {
            return null;
        }

        // Consente selezione esplicita da query string (?struttura_id= / ?sid=) quando l'ID è ammesso
        $requestedId = $request->query('struttura_id', $request->query('sid'));
        if ($requestedId && in_array((int) $requestedId, $allowed, true)) {
            $request->session()->put('struttura_corrente_id', (int) $requestedId);
            StrutturaCorrente::setId((int) $requestedId);
            return (int) $requestedId;
        }

        $currentId = StrutturaCorrente::getId();
        if ($currentId !== null && in_array($currentId, $allowed, true)) {
            return $currentId;
        }

        $selected = $request->session()->get('struttura_corrente_id');
        if ($selected && in_array((int) $selected, $allowed, true)) {
            return (int) $selected;
        }

        $firstActive = Struttura::whereIn('id', $allowed)->where('attiva', true)->orderBy('id')->value('id');
        if ($firstActive !== null) {
            return (int) $firstActive;
        }

        $firstAny = Struttura::whereIn('id', $allowed)->orderBy('id')->value('id');
        return $firstAny !== null ? (int) $firstAny : null;
    }
}
