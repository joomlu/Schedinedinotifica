<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeoDataController extends Controller
{
    protected $nationService;

    public function __construct(\App\Services\NationService $nationService)
    {
        $this->nationService = $nationService;
    }

    /**
     * Get all nations
     */
    public function getNations()
    {
        return response()->json($this->nationService->getAllNations());
    }

    /**
     * Get all regions (for Italy)
     */
    public function getRegions()
    {
        return response()->json($this->nationService->getAllRegions());
    }

    /**
     * Get provinces filtered by region or search term
     */
    public function getProvinces(Request $request)
    {
        $regionCode = $request->input('region_code');
        $search = $request->input('search');

        if ($search) {
            $provinces = $this->nationService->searchProvinces($search);
        } elseif ($regionCode) {
            $provinces = $this->nationService->getProvincesByRegion($regionCode);
        } else {
            $provinces = $this->nationService->getAllProvinces();
        }

        return response()->json($provinces);
    }

    /**
     * Get comuni filtered by province or search term
     */
    public function getComuni(Request $request)
    {
        $provinceCode = $request->input('province_code');
        $search = $request->input('search');

        if ($search) {
            $comuni = $this->nationService->searchComuni($search);
        } elseif ($provinceCode) {
            $comuni = $this->nationService->getComuniByProvince($provinceCode);
        } else {
            // Don't return all 11k+ cities without filter
            return response()->json([]);
        }

        return response()->json($comuni);
    }

    /**
     * Get CAP filtered by province or comune
     */
    public function getCap(Request $request)
    {
        $provinceCode = $request->input('province_code');
        $comuneCode = $request->input('comune_code');

        if ($comuneCode) {
            $cap = $this->nationService->getCapByComune($comuneCode);
        } elseif ($provinceCode) {
            $cap = $this->nationService->getCapByProvince($provinceCode);
        } else {
            $cap = $this->nationService->getAllCap();
        }

        return response()->json($cap);
    }

    /**
     * Generic search endpoint for autocomplete widgets
     * type: nation | region | province | comune | cap
     * optional filters: region_code, province_code, comune_code
     * q: query text
     */
    public function search(Request $request)
    {
        $type = $request->input('type');
        $q = trim((string) $request->input('q', ''));
        $limit = (int) ($request->input('limit', 20));
        $regionCode = $request->input('region_code');
        $provinceCode = $request->input('province_code');
        $comuneCode = $request->input('comune_code');

        $results = [];
        $qLower = mb_strtolower($q);

        switch ($type) {
            case 'nation':
                $items = $this->nationService->getAllNations();
                foreach ($items as $n) {
                    $name = $n['denominazione_nazione'] ?? '';
                    $code = $n['sigla_nazione'] ?? '';
                    if ($q === '' || str_contains(mb_strtolower($name), $qLower) || str_starts_with(mb_strtolower($code), $qLower)) {
                        $results[] = [
                            'label' => $name,
                            'value' => $code,
                        ];
                    }
                    if (count($results) >= $limit) {
                        break;
                    }
                }
                break;

            case 'region':
                $items = $this->nationService->getAllRegions();
                foreach ($items as $r) {
                    $name = $r['denominazione_regione'] ?? '';
                    $code = $r['codice_regione'] ?? '';
                    if ($q === '' || str_contains(mb_strtolower($name), $qLower) || str_starts_with(mb_strtolower($code), $qLower)) {
                        $results[] = ['label' => $name, 'value' => $code];
                    }
                    if (count($results) >= $limit) {
                        break;
                    }
                }
                break;

            case 'province':
                $items = $regionCode
                    ? $this->nationService->getProvincesByRegion($regionCode)
                    : $this->nationService->getAllProvinces();
                foreach ($items as $p) {
                    $name = $p['denominazione_provincia'] ?? '';
                    $code = $p['sigla_provincia'] ?? '';
                    if ($q === '' || str_contains(mb_strtolower($name), $qLower) || str_starts_with(mb_strtolower($code), $qLower)) {
                        $results[] = ['label' => $name.' ('.$code.')', 'value' => $code, 'name' => $name];
                    }
                    if (count($results) >= $limit) {
                        break;
                    }
                }
                break;

            case 'comune':
                if ($provinceCode) {
                    $items = $this->nationService->getComuniByProvince($provinceCode);
                } else {
                    $items = $this->nationService->getAllCities();
                }
                foreach ($items as $c) {
                    $name = $c['denominazione_ita'] ?? ($c['denominazione_ufficiale'] ?? '');
                    $code = $c['codice_istat'] ?? '';
                    $prov = $c['sigla_provincia'] ?? '';
                    if ($q === '' || str_contains(mb_strtolower($name), $qLower)) {
                        $results[] = ['label' => $name.($prov ? ' ('.$prov.')' : ''), 'value' => $code, 'name' => $name, 'province' => $prov];
                    }
                    if (count($results) >= $limit) {
                        break;
                    }
                }
                break;

            case 'cap':
                if ($comuneCode) {
                    $items = $this->nationService->getCapByComune($comuneCode);
                } elseif ($provinceCode) {
                    $items = $this->nationService->getCapByProvince($provinceCode);
                } else {
                    $items = $this->nationService->getAllCap();
                }
                foreach ($items as $cap) {
                    $capCode = (string) ($cap['cap'] ?? '');
                    if ($q === '' || str_starts_with($capCode, $q)) {
                        $results[] = ['label' => $capCode, 'value' => $capCode];
                    }
                    if (count($results) >= $limit) {
                        break;
                    }
                }
                break;

            default:
                $results = [];
        }

        return response()->json($results);
    }

    /**
     * Search comuni with autocomplete and return full hierarchy
     * Returns: { label, value (codice), sigla_provincia, codice_regione, denominazione_provincia, denominazione_regione }
     */
    public function searchComuniWithHierarchy(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $provinceCode = $request->input('province_code');
        $limit = (int) ($request->input('limit', 20));

        $comuni = $provinceCode
            ? $this->nationService->getComuniByProvince($provinceCode)
            : $this->nationService->getAllCities();

        $results = [];
        $qLower = mb_strtolower($q);

        foreach ($comuni as $c) {
            $name = $c['denominazione_ita'] ?? '';
            $code = $c['codice_istat'] ?? '';
            $provCode = $c['sigla_provincia'] ?? '';

            if ($q === '' || str_contains(mb_strtolower($name), $qLower)) {
                // Get province and region info
                $province = \DB::table('provinces')->where('sigla_provincia', $provCode)->first();
                $region = $province ? \DB::table('regions')->where('id', $province->region_id)->first() : null;

                $results[] = [
                    'label' => $name.($provCode ? ' ('.$provCode.')' : ''),
                    'value' => $code,
                    'denominazione' => $name,
                    'sigla_provincia' => $provCode,
                    'denominazione_provincia' => $province->denominazione ?? '',
                    'codice_regione' => $region->codice_regione ?? '',
                    'denominazione_regione' => $region->denominazione ?? '',
                ];
            }
            if (count($results) >= $limit) {
                break;
            }
        }

        return response()->json($results);
    }

    /**
     * Search province with autocomplete and return region info
     * Returns: { label, value (sigla), denominazione, codice_regione, denominazione_regione }
     */
    public function searchProvinceWithHierarchy(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $regionCode = $request->input('region_code');
        $limit = (int) ($request->input('limit', 20));

        $provinces = $regionCode
            ? $this->nationService->getProvincesByRegion($regionCode)
            : $this->nationService->getAllProvinces();

        $results = [];
        $qLower = mb_strtolower($q);

        foreach ($provinces as $p) {
            $name = $p['denominazione_provincia'] ?? '';
            $code = $p['sigla_provincia'] ?? '';
            $regionCode = $p['codice_regione'] ?? '';

            if ($q === '' || str_contains(mb_strtolower($name), $qLower) || str_starts_with(mb_strtolower($code), $qLower)) {
                // Get region info
                $region = \DB::table('regions')->where('codice_regione', $regionCode)->first();

                $results[] = [
                    'label' => $name.' ('.$code.')',
                    'value' => $code,
                    'denominazione' => $name,
                    'codice_regione' => $regionCode,
                    'denominazione_regione' => $region->denominazione ?? '',
                ];
            }
            if (count($results) >= $limit) {
                break;
            }
        }

        return response()->json($results);
    }

    /**
     * Get hierarchy info from a comune code (reverse lookup)
     */
    public function getComuneHierarchy(Request $request, $codice)
    {
        $comune = \DB::table('comuni as c')
            ->leftJoin('provinces as p', 'c.province_id', '=', 'p.id')
            ->leftJoin('regions as r', 'c.region_id', '=', 'r.id')
            ->where('c.codice_questura', $codice)
            ->select([
                'c.codice_questura',
                'c.denominazione as comune_denominazione',
                'c.sigla_provincia',
                'p.denominazione as provincia_denominazione',
                'p.sigla_provincia as provincia_sigla',
                'r.codice_regione',
                'r.denominazione as regione_denominazione',
            ])
            ->first();

        if (! $comune) {
            return response()->json(['error' => 'Comune not found'], 404);
        }

        return response()->json([
            'comune' => [
                'codice' => $comune->codice_questura,
                'denominazione' => $comune->comune_denominazione,
            ],
            'provincia' => [
                'sigla' => $comune->sigla_provincia,
                'denominazione' => $comune->provincia_denominazione,
            ],
            'regione' => [
                'codice' => $comune->codice_regione,
                'denominazione' => $comune->regione_denominazione,
            ],
        ]);
    }

    /**
     * Get comune by denomination/name
     * Used for esenzioni section to find comune_id by name
     */
    public function getComuneByName(Request $request)
    {
        $name = $request->input('name');

        if (! $name) {
            return response()->json(['error' => 'Name parameter required'], 400);
        }

        // Search for exact match first (case-insensitive) with hierarchy
        $comune = \App\Models\Comuni::with(['province', 'region'])
            ->whereRaw('UPPER(denominazione) = ?', [strtoupper($name)])
            ->first();

        // If not found, try partial match
        if (! $comune) {
            $comune = \App\Models\Comuni::with(['province', 'region'])
                ->where('denominazione', 'LIKE', '%'.$name.'%')
                ->first();
        }

        if (! $comune) {
            return response()->json(['error' => 'Comune not found'], 404);
        }

        return response()->json([
            'id' => $comune->id,
            'codice_questura' => $comune->codice_questura,
            'denominazione' => $comune->denominazione,
            'sigla_provincia' => $comune->sigla_provincia,
            'codice_regione' => $comune->codice_regione,
            // Add hierarchy names for auto-fill
            'denominazione_provincia' => $comune->province ? $comune->province->denominazione : null,
            'denominazione_regione' => $comune->region ? $comune->region->denominazione : null,
            'codice_provincia' => $comune->province ? $comune->province->sigla_provincia : $comune->sigla_provincia,
        ]);
    }

    /**
     * Get province by name with region hierarchy
     * Used for auto-fill when user types province manually
     */
    public function getProvinceByName(Request $request)
    {
        $name = $request->input('name');

        if (! $name) {
            return response()->json(['error' => 'Name parameter required'], 400);
        }

        // Search for exact match first (case-insensitive) with region
        $province = \App\Models\Province::with(['region'])
            ->whereRaw('UPPER(denominazione) = ?', [strtoupper($name)])
            ->first();

        // If not found, try partial match
        if (! $province) {
            $province = \App\Models\Province::with(['region'])
                ->where('denominazione', 'LIKE', '%'.$name.'%')
                ->first();
        }

        // Also try by sigla (e.g., "FC", "RM")
        if (! $province && strlen($name) <= 3) {
            $province = \App\Models\Province::with(['region'])
                ->whereRaw('UPPER(sigla_provincia) = ?', [strtoupper($name)])
                ->first();
        }

        if (! $province) {
            return response()->json(['error' => 'Province not found'], 404);
        }

        return response()->json([
            'sigla_provincia' => $province->sigla_provincia,
            'denominazione' => $province->denominazione,
            'codice_regione' => $province->codice_regione,
            // Add region name for auto-fill
            'denominazione_regione' => $province->region ? $province->region->denominazione : null,
        ]);
    }

    /**
     * Get logo for a specific comune
     */
    public function getComuneLogo($id)
    {
        $comune = \App\Models\Comuni::with('logo')->find($id);

        if (! $comune) {
            return response()->json(['error' => 'Comune not found'], 404);
        }

        if (! $comune->logo) {
            return response()->json([
                'has_logo' => false,
                'comune_name' => $comune->denominazione,
            ]);
        }

        return response()->json([
            'has_logo' => true,
            'logo_filename' => $comune->logo->logo_filename,
            'logo_url' => asset('storage/logos/'.$comune->logo->logo_filename),
            'comune_name' => $comune->denominazione,
        ]);
    }
}
