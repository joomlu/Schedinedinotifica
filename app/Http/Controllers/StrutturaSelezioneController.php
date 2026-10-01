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

        $isTopbarSwitchFlow = $request->input('selection_context') === 'topbar_switch';
        if ($isTopbarSwitchFlow) {
            return redirect()->route('root')->with('success', 'Struttura selezionata.');
        }

        $isAdminGestisciFlow = method_exists($user, 'isAdmin')
            && $user->isAdmin()
            && $request->input('selection_context') === 'admin_strutture_gestisci';

        if ($isAdminGestisciFlow) {
            return redirect()->route('root')->with('success', 'Struttura selezionata.');
        }

        return redirect()->back()->with('success', 'Struttura selezionata.');
    }

    protected function queryPerUtente($user)
    {
        return \App\Support\StrutturaAccess::query($user);
    }
}
