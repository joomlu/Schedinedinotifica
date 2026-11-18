<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class StrutturaNonSelezionata extends HttpException
{
    public const RETURN_TO = 'struttura_access_return_to';

    public static function returnPath(mixed $path): ?string
    {
        return is_string($path) && strlen($path) <= 2048
            && preg_match('~^/[A-Za-z0-9][A-Za-z0-9_./-]*$~D', $path) ? $path : null;
    }

    public function __construct()
    {
        parent::__construct(409, 'Seleziona una struttura per continuare.');
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'error_code' => 'STRUTTURA_NON_SELEZIONATA',
                'redirect' => route('strutture.seleziona.index'),
            ], 409)->header('Cache-Control', 'no-store');
        }

        $target = $request->isMethod('GET') ? self::returnPath($request->getPathInfo()) : null;
        if ($target !== null) {
            $request->session()->put(self::RETURN_TO, $target);
        } else {
            $request->session()->forget(self::RETURN_TO);
        }

        return redirect()->route('strutture.seleziona.index')
            ->with('warning', $this->getMessage())
            ->with('warning_title', 'Seleziona una struttura')
            ->header('Cache-Control', 'no-store');
    }
}
