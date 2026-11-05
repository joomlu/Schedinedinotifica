<?php

namespace GeoItalia\Http\Controllers;

use GeoItalia\Models\GeoCap;
use GeoItalia\Models\GeoComune;
use GeoItalia\Models\GeoComuneCap;
use GeoItalia\Models\GeoNazione;
use GeoItalia\Models\GeoProvincia;
use GeoItalia\Models\GeoRegione;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class GeoController extends Controller
{
    private function perPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->input('per_page', $default);
        return max(5, min($perPage, 100));
    }

    private function select2Response(LengthAwarePaginator $paginator): array
    {
        return [
            'results' => $paginator->items(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ];
    }

    public function nazioni(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = $this->perPage($request);

        $query = GeoNazione::query();

        if ($request->has('is_italia')) {
            $query->where('is_italia', $request->boolean('is_italia'));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('cittadinanza', 'like', "%{$q}%")
                    ->orWhere('codice_iso2', 'like', "%{$q}%");
            });
        }

        $paginator = $query
            ->orderByDesc('is_italia')
            ->orderBy('nome')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (GeoNazione $item) {
            $text = $item->nome . ($item->codice_iso2 ? " ({$item->codice_iso2})" : '');
            return [
                'id' => $item->id,
                'text' => $text,
                'nome' => $item->nome,
                'codice_iso2' => $item->codice_iso2,
                'is_italia' => (bool) $item->is_italia,
                'cittadinanza' => $item->cittadinanza,
            ];
        });

        $paginator->setCollection($items);

        return $this->select2Response($paginator);
    }

    public function regioni(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = $this->perPage($request);

        $query = GeoRegione::query();

        if ($request->filled('geo_nazione_id')) {
            $query->where('geo_nazione_id', $request->input('geo_nazione_id'));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('codice_regione', 'like', "%{$q}%");
            });
        }

        $paginator = $query
            ->orderBy('nome')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (GeoRegione $item) {
            return [
                'id' => $item->id,
                'text' => $item->nome,
                'nome' => $item->nome,
                'codice_regione' => $item->codice_regione,
                'geo_nazione_id' => $item->geo_nazione_id,
            ];
        });

        $paginator->setCollection($items);

        return $this->select2Response($paginator);
    }

    public function province(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = $this->perPage($request);

        $query = GeoProvincia::query();

        if ($request->filled('geo_regione_id')) {
            $query->where('geo_regione_id', $request->input('geo_regione_id'));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('sigla', 'like', "%{$q}%")
                    ->orWhere('codice_provincia', 'like', "%{$q}%");
            });
        }

        $paginator = $query
            ->orderBy('nome')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (GeoProvincia $item) {
            $label = $item->sigla ? sprintf('%s (%s)', $item->nome, $item->sigla) : $item->nome;
            return [
                'id' => $item->id,
                'text' => $label,
                'nome' => $item->nome,
                'sigla' => $item->sigla,
                'geo_regione_id' => $item->geo_regione_id,
            ];
        });

        $paginator->setCollection($items);

        return $this->select2Response($paginator);
    }

    public function comuni(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = $this->perPage($request);

        $query = GeoComune::query();

        if ($request->filled('geo_provincia_id')) {
            $query->where('geo_provincia_id', $request->input('geo_provincia_id'));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', "%{$q}%")
                    ->orWhere('codice_istat', 'like', "%{$q}%");
            });
        }

        $paginator = $query
            ->orderBy('nome')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function (GeoComune $item) {
            return [
                'id' => $item->id,
                'text' => $item->nome,
                'nome' => $item->nome,
                'codice_istat' => $item->codice_istat,
                'geo_provincia_id' => $item->geo_provincia_id,
            ];
        });

        $paginator->setCollection($items);

        return $this->select2Response($paginator);
    }

    public function cap(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = $this->perPage($request);

        $query = GeoComuneCap::query()
            ->select([
                'geo_cap.cap',
                'geo_comuni_cap.principale',
                'geo_comuni_cap.priorita',
                'geo_comuni_cap.localita',
                'geo_comuni_cap.geo_comune_id',
                'geo_comuni.geo_provincia_id',
            ])
            ->join('geo_cap', 'geo_cap.id', '=', 'geo_comuni_cap.geo_cap_id')
            ->join('geo_comuni', 'geo_comuni.id', '=', 'geo_comuni_cap.geo_comune_id');

        if ($request->filled('geo_comune_id')) {
            $query->where('geo_comuni_cap.geo_comune_id', $request->input('geo_comune_id'));
        } elseif ($request->filled('geo_provincia_id')) {
            $query->where('geo_comuni.geo_provincia_id', $request->input('geo_provincia_id'));
        }

        if ($q !== '') {
            $query->where('geo_cap.cap', 'like', "%{$q}%");
        }

        $paginator = $query
            ->orderByDesc('geo_comuni_cap.principale')
            ->orderBy('geo_comuni_cap.priorita')
            ->orderBy('geo_cap.cap')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function ($row) {
            return [
                'id' => $row->cap,
                'text' => $row->cap,
                'cap' => $row->cap,
                'geo_comune_id' => $row->geo_comune_id,
                'geo_provincia_id' => $row->geo_provincia_id,
                'principale' => (bool) $row->principale,
                'priorita' => $row->priorita,
                'localita' => $row->localita,
            ];
        });

        $paginator->setCollection($items);

        return $this->select2Response($paginator);
    }

    public function resolve(Request $request)
    {
        $capValue = trim((string) $request->input('cap', ''));
        $comuneId = $request->input('geo_comune_id');
        $codiceIstat = $request->input('codice_istat');
        $siglaProvincia = trim((string) $request->input('sigla_provincia', ''));

        $comune = null;
        $provincia = null;
        $regione = null;
        $nazione = null;
        $caps = collect();
        $capDefault = null;

        if ($capValue !== '') {
            $capEntity = GeoCap::where('cap', $capValue)->first();
            if ($capEntity) {
                $pivot = GeoComuneCap::with(['comune.provincia.regione.nazione'])
                    ->where('geo_cap_id', $capEntity->id)
                    ->orderByDesc('principale')
                    ->orderBy('priorita')
                    ->orderBy('id')
                    ->first();

                if ($pivot && $pivot->comune) {
                    $comune = $pivot->comune;
                }

                $caps = GeoComuneCap::query()
                    ->select([
                        'geo_cap.cap',
                        'geo_comuni_cap.principale',
                        'geo_comuni_cap.priorita',
                        'geo_comuni_cap.localita',
                        'geo_comuni_cap.geo_comune_id',
                    ])
                    ->join('geo_cap', 'geo_cap.id', '=', 'geo_comuni_cap.geo_cap_id')
                    ->where('geo_comuni_cap.geo_cap_id', $capEntity->id)
                    ->orderByDesc('geo_comuni_cap.principale')
                    ->orderBy('geo_comuni_cap.priorita')
                    ->orderBy('geo_cap.cap')
                    ->get()
                    ->map(function ($row) {
                        return [
                            'cap' => $row->cap,
                            'geo_comune_id' => $row->geo_comune_id,
                            'principale' => (bool) $row->principale,
                            'priorita' => $row->priorita,
                            'localita' => $row->localita,
                        ];
                    });

                $capDefault = $caps->first()['cap'] ?? null;
            }
        }

        if (!$comune && $comuneId) {
            $comune = GeoComune::with(['provincia.regione.nazione'])->find($comuneId);
        }

        if (!$comune && $codiceIstat) {
            $comune = GeoComune::with(['provincia.regione.nazione'])
                ->where('codice_istat', $codiceIstat)
                ->first();
        }

        if ($comune) {
            $provincia = $comune->provincia;
        }

        if (!$provincia && $siglaProvincia !== '') {
            $provincia = GeoProvincia::with(['regione.nazione'])
                ->where('sigla', $siglaProvincia)
                ->first();
        }

        if ($provincia) {
            $regione = $provincia->regione ?: null;
        }

        if ($regione) {
            $nazione = $regione->nazione ?: null;
        }

        if (!$caps->count() && $comune) {
            $caps = $this->capsForComune($comune->id);
            $capDefault = $caps->first()['cap'] ?? $capDefault;
        }

        return [
            'nazione' => $this->mapNazione($nazione),
            'regione' => $this->mapRegione($regione),
            'provincia' => $this->mapProvincia($provincia),
            'comune' => $this->mapComune($comune),
            'caps' => $caps,
            'cap_default' => $capDefault,
        ];
    }

    private function capsForComune(int $comuneId)
    {
        return GeoComuneCap::query()
            ->select([
                'geo_cap.cap',
                'geo_comuni_cap.principale',
                'geo_comuni_cap.priorita',
                'geo_comuni_cap.localita',
                'geo_comuni_cap.geo_comune_id',
            ])
            ->join('geo_cap', 'geo_cap.id', '=', 'geo_comuni_cap.geo_cap_id')
            ->where('geo_comuni_cap.geo_comune_id', $comuneId)
            ->orderByDesc('geo_comuni_cap.principale')
            ->orderBy('geo_comuni_cap.priorita')
            ->orderBy('geo_cap.cap')
            ->get()
            ->map(function ($row) {
                return [
                    'cap' => $row->cap,
                    'geo_comune_id' => $row->geo_comune_id,
                    'principale' => (bool) $row->principale,
                    'priorita' => $row->priorita,
                    'localita' => $row->localita,
                ];
            });
    }

    private function mapNazione(?GeoNazione $nazione): ?array
    {
        if (!$nazione) {
            return null;
        }

        return [
            'id' => $nazione->id,
            'nome' => $nazione->nome,
            'text' => $nazione->nome,
            'codice_iso2' => $nazione->codice_iso2,
            'is_italia' => (bool) $nazione->is_italia,
            'cittadinanza' => $nazione->cittadinanza,
        ];
    }

    private function mapRegione(?GeoRegione $regione): ?array
    {
        if (!$regione) {
            return null;
        }

        return [
            'id' => $regione->id,
            'nome' => $regione->nome,
            'text' => $regione->nome,
            'codice_regione' => $regione->codice_regione,
            'geo_nazione_id' => $regione->geo_nazione_id,
        ];
    }

    private function mapProvincia(?GeoProvincia $provincia): ?array
    {
        if (!$provincia) {
            return null;
        }

        $label = $provincia->sigla ? sprintf('%s (%s)', $provincia->nome, $provincia->sigla) : $provincia->nome;

        return [
            'id' => $provincia->id,
            'nome' => $provincia->nome,
            'text' => $label,
            'sigla' => $provincia->sigla,
            'geo_regione_id' => $provincia->geo_regione_id,
        ];
    }

    private function mapComune(?GeoComune $comune): ?array
    {
        if (!$comune) {
            return null;
        }

        return [
            'id' => $comune->id,
            'nome' => $comune->nome,
            'text' => $comune->nome,
            'codice_istat' => $comune->codice_istat,
            'geo_provincia_id' => $comune->geo_provincia_id,
        ];
    }
}
