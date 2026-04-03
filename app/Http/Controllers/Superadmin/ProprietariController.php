<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Proprietario;
use App\Models\User;
use Illuminate\Http\Request;

class ProprietariController extends Controller
{
    public function index()
    {
        $proprietari = Proprietario::with('admin')->orderBy('nome')->get();
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        return view('superadmin.proprietari.index', [
            'proprietari' => $proprietari,
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        return view('superadmin.proprietari.form', [
            'proprietario' => new Proprietario(),
            'admins' => $admins,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'admin_id' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ]);

        Proprietario::create([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
            'note' => $data['note'] ?? null,
            'attivo' => true,
        ]);

        return redirect()->route('superadmin.proprietari.index')->with('status', 'Proprietario creato');
    }

    public function edit(int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        return view('superadmin.proprietari.form', [
            'proprietario' => $proprietario,
            'admins' => $admins,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $proprietario = Proprietario::findOrFail($id);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'admin_id' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ]);

        $proprietario->update([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('superadmin.proprietari.index')->with('status', 'Proprietario aggiornato');
    }

    public function disable(int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proprietario->attivo = false;
        $proprietario->save();

        return redirect()->route('superadmin.proprietari.index')->with('status', 'Proprietario disabilitato');
    }

    public function assegnaAdmin(Request $request, int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $data = $request->validate([
            'admin_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $proprietario->admin_id = $data['admin_id'] ?? null;
        $proprietario->save();

        return redirect()->route('superadmin.proprietari.index')->with('status', 'Admin assegnato');
    }
}
