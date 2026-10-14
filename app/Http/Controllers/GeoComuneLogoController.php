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
        abort_if(is_array($request->query('q')), 400);
        $request->validate(['q' => ['nullable', 'string', 'max:191']]);
        $q = trim((string) $request->input('q', ''));

        $comuni = GeoComune::query()
            ->with('provincia')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('nome', 'like', "%{$q}%")
                        ->orWhereHas('caps', function ($capsQuery) use ($q) {
                            $capsQuery->where('geo_cap.cap', 'like', "%{$q}%");
                        });
                });
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
            'q' => ['nullable', 'string', 'max:191'],
            'logo' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096', 'dimensions:min_width=1,min_height=1,max_width=4096,max_height=4096'],
        ]);

        $file = $data['logo'];
        $filename = $comune->id.'-'.Str::uuid().'.'.$file->extension();
        $storedPath = null;
        try {
            $storedPath = $file->storeAs('geo_comuni/logo', $filename, 'public');
            if (!$storedPath) {
                throw new \RuntimeException('Scrittura del logo non riuscita.');
            }
            $oldLogos = \Illuminate\Support\Facades\DB::transaction(function () use ($id, $storedPath) {
                $comune = GeoComune::whereKey($id)->lockForUpdate()->firstOrFail();
                $old = array_filter([$comune->logo_citta, $comune->logo]);
                $comune->update(['logo_citta' => 'storage/'.$storedPath, 'logo' => null]);
                return $old;
            });
        } catch (\Throwable $error) {
            Storage::disk('public')->delete('geo_comuni/logo/'.$filename);
            report($error);
            throw \Illuminate\Validation\ValidationException::withMessages(['logo' => 'Caricamento non riuscito. Il logo precedente è stato conservato.']);
        }
        foreach (array_unique($oldLogos) as $oldLogo) $this->deleteOldLogo($oldLogo);
        return redirect()->route('geo.comuni.logo', ['q' => $request->input('q')])->with('success', 'Logo caricato correttamente.');
    }

    public function destroy(Request $request, int $id)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:191']]);
        $old = \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $comune = GeoComune::whereKey($id)->lockForUpdate()->firstOrFail();
            $old = array_filter([$comune->logo_citta, $comune->logo]);
            $comune->update(['logo_citta' => null, 'logo' => null]);
            return $old;
        });
        foreach (array_unique($old) as $path) $this->deleteOldLogo($path);
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

        // I consumatori storici possono conservare questo percorso: non romperne i riferimenti.
        if (GeoComune::where('logo_citta', $path)->orWhere('logo', $path)->exists()) return;
        foreach (['logo_citta', 'logo_città'] as $column) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('struttura', $column)
                && \Illuminate\Support\Facades\DB::table('struttura')->where($column, $path)->exists()) return;
        }

        $relative = substr($path, strlen('storage/'));
        if (str_contains($relative, '..') || str_contains($relative, '\\')) {
            return;
        }

        Storage::disk('public')->delete($relative);
    }
}
