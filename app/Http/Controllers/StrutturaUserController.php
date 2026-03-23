<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StrutturaUserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $struttura = $this->resolveStrutturaCorrente($user);
        $utenti = User::where('struttura_id', $struttura->id)->orderBy('id', 'desc')->get();

        return view('struttura.utenti.index', [
            'struttura' => $struttura,
            'utenti' => $utenti,
        ]);
    }

    public function create(Request $request)
    {
        $struttura = $this->resolveStrutturaCorrente($request->user());
        return view('struttura.utenti.create', [
            'struttura' => $struttura,
        ]);
    }

    public function store(Request $request)
    {
        $struttura = $this->resolveStrutturaCorrente($request->user());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ruolo' => 'struttura_user',
            'struttura_id' => $struttura->id,
        ]);

        return redirect()->route('strutture.utenti.index')->with('success', 'Utente creato.');
    }

    public function resetPassword(Request $request, int $id)
    {
        $struttura = $this->resolveStrutturaCorrente($request->user());
        $utente = User::where('struttura_id', $struttura->id)->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $utente->password = Hash::make($data['password']);
        $utente->save();

        return redirect()->route('strutture.utenti.index')->with('success', 'Password aggiornata.');
    }

    protected function resolveStrutturaCorrente($user): Struttura
    {
        if (!$user) {
            abort(403);
        }

        if (method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            $struttura = $user->struttura;
            if (!$struttura) {
                abort(403, 'Struttura non trovata.');
            }
            StrutturaCorrente::setId($struttura->id);
            return $struttura;
        }

        $currentId = StrutturaCorrente::getId();
        if (!$currentId) {
            abort(403, 'Seleziona una struttura.');
        }

        $struttura = Struttura::find($currentId);
        if (!$struttura) {
            abort(403, 'Struttura non trovata.');
        }

        // Permessi per admin/proprietario/super_admin
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $struttura;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $ok = Struttura::where('id', $struttura->id)
                ->whereHas('proprietario', fn($q) => $q->where('admin_id', $user->id))
                ->exists();
            if ($ok) {
                return $struttura;
            }
        }

        if (method_exists($user, 'isProprietario') && $user->isProprietario()) {
            if ($struttura->proprietario_id === $user->proprietario_id) {
                return $struttura;
            }
        }

        abort(403, 'Struttura non autorizzata.');
    }
}
