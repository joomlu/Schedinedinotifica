<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proprietario;
use Illuminate\Http\Request;

class ProprietariController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $proprietari = Proprietario::where('admin_id', $user->id)->orderBy('nome')->get();

        return view('admin.proprietari.index', [
            'proprietari' => $proprietari,
            'admin' => $user,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.proprietari.form', [
            'proprietario' => new Proprietario(),
            'admin' => $request->user(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        Proprietario::create([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'admin_id' => $user->id,
            'note' => $data['note'] ?? null,
            'attivo' => true,
        ]);

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario creato');
    }

    public function edit(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        return view('admin.proprietari.form', [
            'proprietario' => $proprietario,
            'admin' => $request->user(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $proprietario->update([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario aggiornato');
    }

    public function disable(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proprietario->attivo = false;
        $proprietario->save();

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario disabilitato');
    }
}
