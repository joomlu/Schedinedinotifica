<?php

namespace App\Http\Controllers\Proprietario;

use App\Http\Controllers\Controller;
use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;

class StruttureController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $strutture = Struttura::where('proprietario_id', $user->proprietario_id)
            ->orderBy('nome_struttura')
            ->get();

        return view('proprietario.strutture.index', [
            'strutture' => $strutture,
            'currentId' => StrutturaCorrente::getId(),
        ]);
    }
}
