<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use App\Http\Requests\TipoDocumentoRequest;

class TipoDocumentoController extends Controller
{
    public function index()
    {
        $qRaw = trim((string) request('q', ''));
        $q = mb_strtolower($qRaw);
        $terms = $this->normalizedTerms($qRaw);
        $query = TipoDocumento::query();

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

        $tipoDocumenti = $query->orderBy('id')->paginate(10)->withQueryString();

        return view('tipo_documento.index', compact('tipoDocumenti'));
    }

    public function create()
    {
        abort(403, 'Catalogo ufficiale: creazione non consentita.');
    }

    public function store(TipoDocumentoRequest $request)
    {
        abort(403, 'Catalogo ufficiale: creazione non consentita.');
    }

    public function edit(TipoDocumento $tipo_documento)
    {
        $this->abortIfLocked($tipo_documento);
        abort(403, 'Catalogo ufficiale: modifica non consentita.');
    }

    public function update(TipoDocumentoRequest $request, TipoDocumento $tipo_documento)
    {
        $this->abortIfLocked($tipo_documento);
        abort(403, 'Catalogo ufficiale: modifica non consentita.');
    }

    public function destroy(TipoDocumento $tipo_documento)
    {
        $this->abortIfLocked($tipo_documento);
        abort(403, 'Catalogo ufficiale: eliminazione non consentita.');
    }

    private function abortIfLocked(TipoDocumento $tipoDocumento): void
    {
        if ($tipoDocumento->locked) {
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
