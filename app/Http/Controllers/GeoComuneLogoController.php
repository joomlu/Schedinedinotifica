<?php

namespace App\Http\Controllers;

use App\Models\GeoComune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeoComuneLogoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $comuni = GeoComune::query()
            ->with('provincia')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nome', 'like', "%{$q}%")
                    ->orWhere('codice_istat', 'like', "%{$q}%");
            })
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('geo.comuni-logo', compact('comuni', 'q'));
    }

    public function store(Request $request, int $id)
    {
        $comune = GeoComune::findOrFail($id);

        $data = $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $file = $data['logo'];
        $slug = Str::slug($comune->nome) ?: 'comune';
        $filename = $comune->id . '-' . $slug . '.' . $file->extension();
        Storage::disk('public')->makeDirectory('geo_comuni/logo');
        $storedPath = $file->storeAs('geo_comuni/logo', $filename, 'public');
        $publicPath = 'storage/' . $storedPath;

        $oldLogo = $comune->logo_citta ?? $comune->logo;
        if ($oldLogo !== $publicPath) {
            $this->deleteOldLogo($oldLogo);
        }

        $comune->update([
            'logo_citta' => $publicPath,
        ]);

        return redirect()->route('geo.comuni.logo', ['q' => $request->input('q')])->with('success', 'Logo caricato correttamente.');
    }

    public function destroy(Request $request, int $id)
    {
        $comune = GeoComune::findOrFail($id);

        $this->deleteOldLogo($comune->logo_citta ?? $comune->logo);
        $comune->update(['logo_citta' => null, 'logo' => null]);

        return redirect()->route('geo.comuni.logo', ['q' => $request->input('q')])->with('success', 'Logo rimosso.');
    }

    private function deleteOldLogo(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (!str_starts_with($path, 'storage/geo_comuni/logo/')) {
            return;
        }

        $relative = substr($path, strlen('storage/'));
        if (str_contains($relative, '..') || str_contains($relative, '\\')) {
            return;
        }

        Storage::disk('public')->delete($relative);
    }
}
