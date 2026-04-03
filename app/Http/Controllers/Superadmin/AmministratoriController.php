<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AmministratoriController extends Controller
{
    public function index()
    {
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        return view('superadmin.amministratori.index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return view('superadmin.amministratori.form', [
            'admin' => new User(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $password = $data['password'] ?? Str::random(12);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'avatar' => '',
            'ruolo' => 'admin',
        ]);

        return redirect()->route('superadmin.amministratori.index')->with('status', 'Amministratore creato');
    }

    public function edit(int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);

        return view('superadmin.amministratori.form', [
            'admin' => $admin,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->avatar = $admin->avatar ?? '';
        $admin->save();

        return redirect()->route('superadmin.amministratori.index')->with('status', 'Amministratore aggiornato');
    }

    public function disable(int $id)
    {
        $admin = User::findOrFail($id);
        $admin->ruolo = 'admin_disabled';
        $admin->save();

        return redirect()->route('superadmin.amministratori.index')->with('status', 'Amministratore disabilitato');
    }
}
