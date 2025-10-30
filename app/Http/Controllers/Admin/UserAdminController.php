<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function __construct()
    {
        // Acceso a gestión de usuarios: superadmin (manage users), admin (manage users), cliente (manage staff)
        $this->middleware('permission:manage users|manage staff');
    }

    public function index(): View
    {
        $users = User::with('roles')->orderBy('id')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles([$request->role]);

        return redirect()->back()->with('status', 'Ruolo aggiornato per '.$user->email);
    }
}
