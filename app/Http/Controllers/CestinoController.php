<?php

namespace App\Http\Controllers;

use App\Models\CestinoItem;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;

class CestinoController extends Controller
{
    private const SECTION_MAP = [
        'Cliente' => 'Clienti',
        'Schedina' => 'Schedine',
        'Arrivo' => 'Arrivi',
        'Web Check-in' => 'Web Check-in',
        'Componente' => 'Componenti',
        'Gruppo' => 'Configurazioni',
        'Titolo' => 'Configurazioni',
        'Tipo Cliente' => 'Configurazioni',
        'Tipo Via' => 'Configurazioni',
        'Tipo Documento' => 'Configurazioni',
        'Rilasciato da' => 'Configurazioni',
        'Esenzione tassa' => 'Configurazioni',
        'Articolo' => 'Configurazioni',
        'Licenza' => 'Configurazioni',
    ];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $tipo = trim((string) $request->query('tipo', ''));
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;

        $items = CestinoItem::query()
            ->when($strutturaId, fn ($query) => $query->where(function ($inner) use ($strutturaId) {
                $inner->whereNull('struttura_id')->orWhere('struttura_id', $strutturaId);
            }))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('entity_type', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('source', 'like', $like);
                });
            })
            ->orderByDesc('deleted_at')
            ->get()
            ->map(function (CestinoItem $item) {
                $item->sezione = $this->sectionForEntityType($item->entity_type);
                return $item;
            });

        if ($tipo !== '') {
            $items = $items->filter(fn (CestinoItem $item) => $item->sezione === $tipo)->values();
        }

        $tipi = $items
            ->pluck('sezione')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;
        $total = $items->count();
        $pageItems = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('cestino.index', [
            'items' => $items,
            'tipi' => $tipi,
            'tipoSelezionato' => $tipo,
        ]);
    }

    public function destroy(int $id)
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;

        $item = CestinoItem::query()
            ->when($strutturaId, fn ($query) => $query->where(function ($inner) use ($strutturaId) {
                $inner->whereNull('struttura_id')->orWhere('struttura_id', $strutturaId);
            }))
            ->findOrFail($id);

        $item->delete();

        return redirect()->back()->with('success', 'Elemento eliminato definitivamente dal cestino.');
    }

    public function restore(int $id)
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;

        $item = CestinoItem::query()
            ->when($strutturaId, fn ($query) => $query->where(function ($inner) use ($strutturaId) {
                $inner->whereNull('struttura_id')->orWhere('struttura_id', $strutturaId);
            }))
            ->findOrFail($id);

        app(CestinoService::class)->restoreItem($item);

        return redirect()->back()->with('success', 'Elemento ripristinato dal cestino.');
    }

    private function sectionForEntityType(?string $entityType): string
    {
        return self::SECTION_MAP[$entityType ?? ''] ?? ($entityType ?: 'Altro');
    }
}
