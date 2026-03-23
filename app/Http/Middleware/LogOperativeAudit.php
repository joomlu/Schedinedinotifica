<?php

namespace App\Http\Middleware;

use App\Models\StrutturaAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogOperativeAudit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (!$user || !$user->struttura_id) {
            return $response;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '' || !$this->shouldLog($routeName)) {
            return $response;
        }

        [$entityType, $entityId] = $this->resolveEntity($request, $routeName);

        StrutturaAuditLog::withoutGlobalScopes()->create([
            'struttura_id' => $user->struttura_id,
            'user_id' => $user->id,
            'route_name' => $routeName,
            'metodo' => $request->method(),
            'entita_tipo' => $entityType,
            'entita_id' => $entityId,
            'descrizione' => $this->humanLabel($routeName, $request->method()),
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return $response;
    }

    private function shouldLog(string $routeName): bool
    {
        foreach (['customer.', 'schedina.', 'arrival.', 'arrivals.', 'componenti.', 'web_checkin.', 'questura.', 'istat.tabella_a.', 'gestione.operativa.', 'supporto.', 'tassa_di_soggiorno.', 'tassa_esenzioni.', 'tipo_cliente.', 'tipo_documento.', 'tipo_alloggiato.', 'tipo_via.', 'gruppo.', 'rilasciato.'] as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolveEntity(Request $request, string $routeName): array
    {
        foreach (['id', 'user', 'schedina', 'customer', 'comanda'] as $key) {
            $value = $request->route($key);
            if ($value !== null) {
                return [strtok($routeName, '.'), (string) $value];
            }
        }

        return [strtok($routeName, '.'), null];
    }

    private function humanLabel(string $routeName, string $method): string
    {
        $label = match ($routeName) {
            'supporto.store' => 'Ha aperto un ticket di supporto',
            'supporto.reply' => 'Ha risposto a un ticket di supporto',
            'supporto.status' => 'Ha cambiato lo stato di un ticket di supporto',
            'supporto.assign' => 'Ha preso in carico un ticket di supporto',
            default => null,
        };

        if ($label !== null) {
            return $label;
        }

        $verb = match ($method) {
            'POST' => 'Creazione',
            'PUT', 'PATCH' => 'Aggiornamento',
            'DELETE' => 'Eliminazione',
            default => 'Operazione',
        };

        return $verb . ' ' . str_replace(['.', '_'], [' / ', ' '], $routeName);
    }
}
