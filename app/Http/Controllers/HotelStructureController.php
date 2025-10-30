<?php

namespace App\Http\Controllers;

use App\Models\HotelStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HotelStructureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create structures')->only(['create', 'store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Filtrar estructuras según el rol del usuario
        if ($user->hasRole('superadmin')) {
            $structures = HotelStructure::with('cliente')->get();
        } else {
            // Cliente solo ve sus propias estructuras
            $structures = HotelStructure::where('cliente_id', $user->id)->get();
        }
        
        return view('hotel_structures.index', compact('structures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hotel_structures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fecha_registro' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'online' => 'boolean',
            'activo' => 'boolean',
            'username_hotel' => 'required|string|max:255|unique:hotel_structures,username_hotel',
            'password_hotel' => 'required|string|min:6',
            'schedina_url' => 'nullable|url',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'web' => 'nullable|url',
            'cf' => 'nullable|string|max:50',
            'piva' => 'nullable|string|max:50',
            'typology' => 'nullable|string|max:255',
            'clasification' => 'nullable|string|max:255',
            'roomdisp' => 'nullable|integer|min:0',
            'beddisp' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Asignar el cliente_id del usuario autenticado
        $validated['cliente_id'] = Auth::id();
        
        // Encriptar la contraseña del hotel
        $validated['password_hotel'] = Hash::make($validated['password_hotel']);
        
        // Manejar upload del logo
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('hotel_logos', 'public');
        }

        HotelStructure::create($validated);

        return redirect()->route('hotel-structures.index')
            ->with('success', 'Struttura hotel creata con successo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $structure = HotelStructure::findOrFail($id);
        
        // Verificar acceso
        $this->authorizeAccess($structure);
        
        return view('hotel_structures.show', compact('structure'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $structure = HotelStructure::findOrFail($id);
        
        // Verificar acceso
        $this->authorizeAccess($structure);
        
        return view('hotel_structures.edit', compact('structure'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $structure = HotelStructure::findOrFail($id);
        
        // Verificar acceso
        $this->authorizeAccess($structure);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fecha_registro' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'online' => 'boolean',
            'activo' => 'boolean',
            'username_hotel' => 'required|string|max:255|unique:hotel_structures,username_hotel,' . $id,
            'password_hotel' => 'nullable|string|min:6',
            'schedina_url' => 'nullable|url',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'web' => 'nullable|url',
            'cf' => 'nullable|string|max:50',
            'piva' => 'nullable|string|max:50',
            'typology' => 'nullable|string|max:255',
            'clasification' => 'nullable|string|max:255',
            'roomdisp' => 'nullable|integer|min:0',
            'beddisp' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Solo actualizar contraseña si se proporciona una nueva
        if (!empty($validated['password_hotel'])) {
            $validated['password_hotel'] = Hash::make($validated['password_hotel']);
        } else {
            unset($validated['password_hotel']);
        }
        
        // Manejar upload del logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($structure->logo) {
                Storage::disk('public')->delete($structure->logo);
            }
            $validated['logo'] = $request->file('logo')->store('hotel_logos', 'public');
        }

        $structure->update($validated);

        return redirect()->route('hotel-structures.index')
            ->with('success', 'Struttura hotel aggiornata con successo!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $structure = HotelStructure::findOrFail($id);
        
        // Verificar acceso
        $this->authorizeAccess($structure);
        
        // Eliminar logo si existe
        if ($structure->logo) {
            Storage::disk('public')->delete($structure->logo);
        }
        
        $structure->delete();

        return redirect()->route('hotel-structures.index')
            ->with('success', 'Struttura hotel eliminata con successo!');
    }

    /**
     * Verificar que el usuario tiene acceso a esta estructura
     */
    private function authorizeAccess(HotelStructure $structure)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('superadmin') && $structure->cliente_id !== $user->id) {
            abort(403, 'Non hai il permesso di accedere a questa struttura.');
        }
    }
}
