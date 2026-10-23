<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StrutturaUserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $struttura = $this->resolveStrutturaCorrente($user);
        $utenti = User::where('struttura_id', $struttura->id)->where('ruolo', 'struttura_user')->orderBy('id', 'desc')->get();

        return view('struttura.utenti.index', [
            'struttura' => $struttura,
            'utenti' => $utenti,
        ]);
    }

    public function create(Request $request)
    {
        $struttura = $this->resolveStrutturaCorrente($request->user());
        abort_unless($request->user()->isProprietario() || $request->user()->canManageGestioneOperativa($struttura->id), 403, 'Operazione riservata alla gestione della struttura.');

        return view('struttura.utenti.create', [
            'struttura' => $struttura,
        ]);
    }

    public function store(Request $request)
    {
        abort_if($request->hasAny(['id', 'struttura_id', 'proprietario_id', 'admin_id', 'ruolo']), 403);
        $struttura = $this->resolveStrutturaCorrente($request->user());
        abort_unless($request->user()->isProprietario() || $request->user()->canManageGestioneOperativa($struttura->id), 403, 'Operazione riservata alla gestione della struttura.');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'avatar' => '',
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ruolo' => 'struttura_user',
            'struttura_id' => $struttura->id,
            'proprietario_id' => $struttura->proprietario_id,
            'ruolo_operativo' => 'reception',
            'attivo' => true,
        ]);

        return redirect()->route('strutture.utenti.index')->with('success', 'Utente creato.');
    }

    public function resetPassword(Request $request, int $id)
    {
        $struttura = $this->resolveStrutturaCorrente($request->user());
        abort_unless($request->user()->isProprietario() || $request->user()->canManageGestioneOperativa($struttura->id), 403, 'Operazione riservata alla gestione della struttura.');
        $utente = User::where('struttura_id', $struttura->id)->where('ruolo', 'struttura_user')->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $utente->password = Hash::make($data['password']);
        $utente->save();

        return redirect()->route('strutture.utenti.index')->with('success', 'Password aggiornata.');
    }

    protected function resolveStrutturaCorrente($user): Struttura
    {
        return \App\Support\StrutturaAccess::resolve(request());
    }
}
