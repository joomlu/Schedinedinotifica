<?php

// Verifica isolata della pipeline dei ruoli: nessun bootstrap applicativo,
// database, server HTTP, sessione reale o filesystem dei loghi.
function abort($code, $message = '', array $headers = [])
{
    throw new \Symfony\Component\HttpKernel\Exception\HttpException($code, $message);
}

require __DIR__.'/../../vendor/autoload.php';

use App\Http\Middleware\Ruolo;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

function verificaRuolo(?string $ruolo, string $policy, int $atteso, string $metodo, string $path): void
{
    $request = Request::create($path, $metodo);
    $request->setUserResolver(fn () => $ruolo === null ? null : (object) ['ruolo' => $ruolo]);
    try {
        $response = (new Pipeline(new Container()))->send($request)
            ->through([Ruolo::class.':'.$policy])
            ->then(fn () => new Response('Autorizzato', 200));
        $status = $response->getStatusCode();
    } catch (HttpException $exception) {
        $status = $exception->getStatusCode();
    }
    if ($status !== $atteso) {
        throw new RuntimeException("Permesso errato: {$metodo} {$path}, ruolo ".($ruolo ?? 'anonimo').", HTTP {$status}, atteso {$atteso}");
    }
}

foreach (['GET' => '/geo/comuni/logo', 'POST' => '/geo/comuni/7500/logo', 'DELETE' => '/geo/comuni/7500/logo'] as $metodo => $path) {
    foreach (['super_admin', 'admin', 'proprietario', 'struttura_user', '', 'ADMIN', null] as $ruolo) {
        verificaRuolo($ruolo, 'super_admin,admin', in_array($ruolo, ['super_admin', 'admin'], true) ? 200 : 403, $metodo, $path);
    }
}
foreach (['super_admin', 'admin', 'proprietario'] as $policy) {
    foreach (['super_admin', 'admin', 'proprietario', 'struttura_user', null] as $ruolo) {
        verificaRuolo($ruolo, $policy, $ruolo === $policy ? 200 : 403, 'GET', '/configurazione');
    }
}
verificaRuolo('admin', '', 403, 'GET', '/geo/comuni/logo');
echo "PASS: 37 verifiche della pipeline dei ruoli, incluse GET/POST/DELETE e policy singole.\n";
