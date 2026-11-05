<?php

namespace App\Http\Controllers;

use App\Models\CestinoItem;
use App\Services\CestinoService;
use App\Support\StrutturaAccess;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;

class CestinoController extends Controller
{
    private const SECTION_MAP = [
        'Amministratore' => 'Configurazioni',
        'Proprietario' => 'Configurazioni',
        'Struttura' => 'Configurazioni',
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
        $request->validate(['q' => ['nullable', 'string', 'max:180'], 'tipo' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', \Illuminate\Validation\Rule::in([10,25,50,100])]]);
        $q = trim((string) $request->query('q', ''));
        $tipo = trim((string) $request->query('tipo', ''));
        $items = $this->scopedItems()
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
                $item->admin_read_only = !$this->canMutate($item);
                $item->display_rows = app(\App\Services\CestinoDettaglio::class)->rows($item);
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
        $perPage = (int) $request->query('per_page', 10);
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
            'eliminabili' => $this->scopedItems()->get()->filter(fn ($item) => $this->canMutate($item))->count(),
        ]);
    }

    public function destroy(int $id)
    {
        $item = $this->scopedItems()->findOrFail($id);

        abort_unless($this->canMutate($item), 403);

        app(CestinoService::class)->purgeItem($item);

        return redirect()->back()->with('success', 'Elemento eliminato definitivamente dal cestino.');
    }

    public function destroySelected(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['required', 'integer', 'min:1', 'distinct'],
            'conferma' => ['accepted'],
        ]);
        $count = \DB::transaction(function () use ($data) {
            $items = $this->scopedItems()->whereIn('id', $data['ids'])->lockForUpdate()->get();
            abort_unless($items->count() === count($data['ids']), 404);
            foreach ($items as $item) {
                abort_unless($this->canMutate($item), 403);
            }
            foreach ($items as $item) {
                app(CestinoService::class)->purgeItem($item);
            }
            return $items->count();
        });

        return redirect()->back()->with('success', $count.' elementi eliminati definitivamente dal cestino.');
    }

    public function empty(Request $request)
    {
        $request->validate(['conferma' => ['accepted']]);
        $count = \DB::transaction(function () {
            $items = $this->scopedItems()->lockForUpdate()->get()
                ->filter(fn ($item) => $this->canMutate($item));
            foreach ($items as $item) {
                app(CestinoService::class)->purgeItem($item);
            }
            return $items->count();
        });

        return redirect()->route('cestino.index')->with('success', $count.' elementi eliminati definitivamente. Eventuali elementi protetti sono stati conservati.');
    }

    public function restore(int $id)
    {
        $item = $this->scopedItems()->findOrFail($id);

        abort_unless($this->canMutate($item), 403);

        app(CestinoService::class)->restoreItem($item);

        return redirect()->back()->with('success', 'Elemento ripristinato dal cestino.');
    }

    private function scopedItems()
    {
        $query = CestinoItem::query();
        $user = auth()->user();
        $strutturaId = StrutturaCorrente::getId() ?? $user?->struttura_id;

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query->whereIn('entity_class', [
                ...CestinoService::TENANT_CLASSES, ...CestinoService::GLOBAL_CLASSES,
            ])->when($strutturaId, fn ($query) => $query->where(function ($inner) use ($strutturaId) {
                $inner->whereNull('struttura_id')->orWhere('struttura_id', $strutturaId);
            }));
        }

        if (!$user->isAdmin() && !$user->isProprietario() && !$user->isStrutturaUser()) {
            return $query->whereRaw('1 = 0');
        }

        // La selezione corrente restringe la membership, non la sostituisce.
        // Nessuna struttura autorizzata e struttura_id NULL non concedono accesso.
        return $query->whereIn('entity_class', CestinoService::TENANT_CLASSES)
            ->whereIn('struttura_id', StrutturaAccess::query($user)->select('struttura.id'))
            ->when($strutturaId, fn ($query) => $query->where('struttura_id', $strutturaId));
    }

    private function canMutate(CestinoItem $item): bool
    {
        return auth()->check()
            && !in_array($item->entity_class, [\App\Models\TipoAlloggiato::class, \App\Models\TipoDocumento::class, \App\Models\TipoCliente::class], true)
            && !(auth()->user()->isAdmin()
            && $item->entity_class === \App\Models\LicenzaAssegnazione::class);
    }

    private function sectionForEntityType(?string $entityType): string
    {
        return self::SECTION_MAP[$entityType ?? ''] ?? ($entityType ?: 'Altro');
    }
}
