<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class StrutturaNonSelezionata extends HttpException
{
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

        return redirect()->route('strutture.seleziona.index')
            ->with('warning', $this->getMessage())
            ->with('warning_title', 'Seleziona una struttura')
            ->header('Cache-Control', 'no-store');
    }
}
