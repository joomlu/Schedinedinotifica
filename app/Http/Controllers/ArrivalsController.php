<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Schedina, Struttura, GeoNazione, GeoComune};
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;

class ArrivalsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q'=>['nullable','string','max:100'], 'page'=>['nullable','integer','min:1']]);
        $struttura = $this->resolveStruttura($request);
        $q = trim((string) $request->query('q', ''));

        $baseQuery = Schedina::query()
            ->where('struttura_id', $struttura->id)
            ->inArrivi()
            ->with('componenti')
            ->when($q !== '', function ($inner) use ($q) {
                $like = '%' . $q . '%';
                $inner->where(function ($where) use ($like, $q) {
                    $where->where('scheda', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('id_prenotazione_esterna', 'like', $like)
                        ->orWhere('oa_city', 'like', $like)
                        ->orWhere('oa_country', 'like', $like)
                        ->orWhere('departure', 'like', $like)->orWhere('room', 'like', $like);
                    if (str_contains(\App\Support\ArriviDay::today(), $q) || str_contains(now()->format('d/m/Y'), $q)) $where->orWhereRaw('1=1');
                });
            });

        $totaliCollection = (clone $baseQuery)->get(['id', 'arrive', 'cant_people', 'room']);

        $query = $baseQuery
            ->orderByDesc('scheda')
            ->orderByDesc('id');

        $arrivals = $query->paginate(10)->withQueryString();
        $arrivals->getCollection()->transform(function (Schedina $arrival) {
            return $this->decorateSchedina($arrival);
        });

        return view('arrivals.list', [
            'arrivals' => $arrivals,
            'struttura' => $struttura,
            'q' => $q,
            'arriviStats' => [
                'in_arrivo_oggi' => $totaliCollection->count(),
                'tot_persone' => (int) $totaliCollection->sum(fn ($item) => (int) ($item->cant_people ?? 0)),
                'tot_camere' => (int) $totaliCollection->sum(fn ($item) => (int) ($item->room ?? 0)),
            ],
        ]);
    }

    public function new(Request $request)
    {
        return redirect()->route('newschedina', ['arrival' => 1]);
    }

    public function clock(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        \App\Support\SchedinaFormContext::validate($request, (int) $struttura->id);
        return response()->json(\App\Support\ArriviDay::clock())->header('Cache-Control', 'no-store');
    }

    public function search(Request $request)
    {
        return app(SchedinaController::class)->searchCustomers($request);
    }

    public function store(Request $request)
    {
        $request->merge(['save_mode' => 'to_arrivi']);
        return app(SchedinaController::class)->store($request);
    }

    public function a_schedina(Request $request, int $id)
    {
        return app(SchedinaController::class)->convertArrival($request, $id);
    }

    public function destroy(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request);
        \App\Support\SchedinaFormContext::validate($request, (int) $struttura->id);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($struttura, $id) {
            Struttura::withoutGlobalScopes()->whereKey($struttura->id)->lockForUpdate()->firstOrFail();
            $schedina = Schedina::query()->where('struttura_id', $struttura->id)->inArrivi()->lockForUpdate()->findOrFail($id);
            app(CestinoService::class)->archiveModel($schedina, ['source'=>'Arrivi','entity_type'=>'Arrivo','circuito'=>'arrivi']);
            $schedina->delete();
            return redirect()->back()->with('success', 'Arrivo spostato nel cestino.');
        });
    }

    private function resolveStruttura(Request $request): Struttura
    {
        $id = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        abort_unless($id, 403, 'Struttura non selezionata.');

        return Struttura::query()->findOrFail($id);
    }

    private function decorateSchedina(Schedina $schedina): Schedina
    {
        $schedina->oa_country = $this->resolveGeoLabelValue($schedina->oa_country, GeoNazione::query(), 'nome');
        $schedina->oa_city = $this->resolveGeoLabelValue($schedina->oa_city, GeoComune::query(), 'nome');
        $schedina->or_country = $this->resolveGeoLabelValue($schedina->or_country, GeoNazione::query(), 'nome');
        $schedina->or_city = $this->resolveGeoLabelValue($schedina->or_city, GeoComune::query(), 'nome');

        return $schedina;
    }

    private function resolveGeoLabelValue($raw, $query, string $labelColumn): string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (!ctype_digit($value)) {
            return $value;
        }

        return (string) ((clone $query)->whereKey((int) $value)->value($labelColumn) ?? $value);
    }
}
