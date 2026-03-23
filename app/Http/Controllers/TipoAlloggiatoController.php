<?php

namespace App\Http\Controllers;

use App\Models\TipoAlloggiato;
use Illuminate\Http\Request;

class TipoAlloggiatoController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = TipoAlloggiato::query();

        if ($q !== '') {
            $query->where(function ($inner) use ($q, $terms) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $inner->orWhereRaw($this->normalizedFieldExpr('codice') . ' LIKE ?', [$like])
                        ->orWhereRaw($this->normalizedFieldExpr('descrizione') . ' LIKE ?', [$like]);
                }
            });
        }

        $tipiAlloggiato = $query->orderBy('id')->paginate(10)->withQueryString();

        return view('tipo_alloggiato.index', compact('tipiAlloggiato'));
    }

    public function create()
    {
        abort(403, 'Catalogo ufficiale: creazione non consentita.');
    }

    public function store(Request $request)
    {
        abort(403, 'Catalogo ufficiale: creazione non consentita.');
    }

    public function edit(TipoAlloggiato $tipo_alloggiato)
    {
        $this->abortIfLocked($tipo_alloggiato);
        abort(403, 'Catalogo ufficiale: modifica non consentita.');
    }

    public function show(TipoAlloggiato $tipo_alloggiato)
    {
        $this->abortIfLocked($tipo_alloggiato);
        abort(403, 'Catalogo ufficiale: consultazione dettaglio non consentita.');
    }

    public function update(Request $request, TipoAlloggiato $tipo_alloggiato)
    {
        $this->abortIfLocked($tipo_alloggiato);
        abort(403, 'Catalogo ufficiale: modifica non consentita.');
    }

    public function destroy(TipoAlloggiato $tipo_alloggiato)
    {
        $this->abortIfLocked($tipo_alloggiato);
        abort(403, 'Catalogo ufficiale: eliminazione non consentita.');
    }

    private function abortIfLocked(TipoAlloggiato $tipoAlloggiato): void
    {
        if ($tipoAlloggiato->locked) {
            abort(403, 'Record bloccato dalla Questura e non modificabile.');
        }
    }

    private function normalizedTerms(string $value): array
    {
        $clean = mb_strtolower(trim($value));
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean);
        if (!is_string($clean) || $clean === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $clean)));
    }

    private function normalizedFieldExpr(string $column): string
    {
        $expr = "LOWER(COALESCE($column, ''))";
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($expr, '''', ''), '.', ' '), ',', ' '), '-', ' '), '/', ' '), '(', ' '), ')', ' ')";
    }
}
