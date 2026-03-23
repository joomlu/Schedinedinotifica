<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StrutturaSelezioneController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if (method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            return redirect()->route('home');
        }

        $strutture = $this->queryPerUtente($user)->orderBy('id')->get();

        return view('struttura.seleziona', [
            'strutture' => $strutture,
            'currentId' => StrutturaCorrente::getId(),
        ]);
    }

    public function seleziona(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $struttura = $this->queryPerUtente($user)->where('id', $id)->firstOrFail();
        StrutturaCorrente::setId($struttura->id);
        return redirect()->back()->with('success', 'Struttura selezionata.');
    }

    protected function queryPerUtente($user)
    {
        $query = Struttura::query();

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $query;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $query->whereHas('proprietario', function ($q) use ($user) {
                $q->where('admin_id', $user->id);
            });
        }

        if (method_exists($user, 'isProprietario') && $user->isProprietario()) {
            return $query->where('proprietario_id', $user->proprietario_id);
        }

        if (method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            return $query->where('id', $user->struttura_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
