<?php

namespace App\Http\Controllers;

use App\Services\NationService;
use Illuminate\Http\Request;
use Log;

class LocationController extends Controller
{
    protected $nationService;

    public function __construct(NationService $nationService)
    {
        $this->nationService = $nationService;
    }

    public function provincesByRegion(Request $request)
    {
        // Registra los parámetros recibidos
        Log::info('Petición a provincesByRegion', $request->all());

        $regionCode = $request->input('codice_regione');
        $provinces = $this->nationService->getProvincesByRegion($regionCode);

        // Registra la respuesta que se va a devolver
        Log::info('Respuesta de provincesByRegion', $provinces);

        return response()->json($provinces);
    }

    public function nations(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $items = $this->nationService->getAllNations();
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $items = array_values(array_filter($items, function ($n) use ($qLower) {
                $name = isset($n['denominazione_cittadinanza']) ? mb_strtolower($n['denominazione_cittadinanza']) : '';
                $nation = isset($n['denominazione_nazione']) ? mb_strtolower($n['denominazione_nazione']) : '';
                $sigla = isset($n['sigla_nazione']) ? mb_strtolower($n['sigla_nazione']) : '';

                return str_contains($name, $qLower) || str_contains($nation, $qLower) || str_contains($sigla, $qLower);
            }));
        }

        return response()->json($items);
    }

    public function regions(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $items = $this->nationService->getAllRegions();
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $items = array_values(array_filter($items, function ($r) use ($qLower) {
                $name = isset($r['denominazione_regione']) ? mb_strtolower($r['denominazione_regione']) : '';
                $code = isset($r['codice_regione']) ? mb_strtolower($r['codice_regione']) : '';

                return str_contains($name, $qLower) || str_contains($code, $qLower);
            }));
        }

        return response()->json($items);
    }

    public function provincesAll(Request $request)
    {
        // Registra los parámetros recibidos
        Log::info('Petición a provincesAll', $request->all());
        $q = trim((string) $request->input('q', ''));
        $regionCode = (string) $request->input('codice_regione', '');
        $provinces = $this->nationService->getAllProvinces();
        if ($regionCode !== '') {
            $provinces = array_values(array_filter($provinces, function ($p) use ($regionCode) {
                return isset($p['codice_regione']) && (string) $p['codice_regione'] === $regionCode;
            }));
        }
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $provinces = array_values(array_filter($provinces, function ($p) use ($qLower) {
                $name = isset($p['denominazione_provincia']) ? mb_strtolower($p['denominazione_provincia']) : '';
                $sigla = isset($p['sigla_provincia']) ? mb_strtolower($p['sigla_provincia']) : '';

                return str_contains($name, $qLower) || str_contains($sigla, $qLower);
            }));
        }

        // Registra la respuesta que se va a devolver
        Log::info('Respuesta de provincesByRegion', $provinces);

        return response()->json($provinces);
    }

    /**
     * Retorna los CAP filtrados según la provincia.
     * Se utiliza en la petición AJAX.
     */
    public function capByProvince(Request $request)
    {
        $provinceCode = (string) $request->input('sigla_provincia');
        $comune = trim((string) $request->input('comune', ''));
        $istat = trim((string) $request->input('codice_istat', ''));
        $q = trim((string) $request->input('q', ''));
        if ($provinceCode !== '') {
            $capEntries = $this->nationService->getCapByProvince($provinceCode);
        } else {
            $capEntries = $this->nationService->getAllCap();
        }
        // Filtra per comune/codice ISTAT se forniti
        if ($istat !== '') {
            $capEntries = array_values(array_filter($capEntries, function ($c) use ($istat) {
                $cod = isset($c['codice_istat']) ? (string) $c['codice_istat'] : '';

                return $cod !== '' && $cod === $istat;
            }));
        } elseif ($comune !== '') {
            $comuneLower = mb_strtolower($comune);
            $capEntries = array_values(array_filter($capEntries, function ($c) use ($comuneLower) {
                $name = isset($c['denominazione_ita']) ? mb_strtolower($c['denominazione_ita']) : (isset($c['Comune']) ? mb_strtolower($c['Comune']) : '');

                return $name === $comuneLower;
            }));
        }
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $capEntries = array_values(array_filter($capEntries, function ($c) use ($qLower) {
                $cap = isset($c['cap']) ? mb_strtolower($c['cap']) : (isset($c['CAP']) ? mb_strtolower($c['CAP']) : '');
                $comune = isset($c['denominazione_ita']) ? mb_strtolower($c['denominazione_ita']) : (isset($c['Comune']) ? mb_strtolower($c['Comune']) : '');

                return str_contains($cap, $qLower) || str_contains($comune, $qLower);
            }));
        }

        return response()->json($capEntries);
    }

    public function citiesByProvince(Request $request)
    {
        Log::info('Petición a citiesByProvince', $request->all());
        $provinceCode = (string) $request->input('sigla_provincia');
        $q = trim((string) $request->input('q', ''));
        if ($provinceCode !== '') {
            $cities = $this->nationService->getCitiesByProvince($provinceCode);
        } else {
            $cities = $this->nationService->getAllCities();
        }
        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $cities = array_values(array_filter($cities, function ($c) use ($qLower) {
                $name = isset($c['denominazione_ita']) ? mb_strtolower($c['denominazione_ita']) : '';
                $cap = isset($c['cap']) ? mb_strtolower($c['cap']) : '';

                return str_contains($name, $qLower) || str_contains($cap, $qLower);
            }));
        }
        Log::info('Respuesta de citiesByProvince', $cities);

        return response()->json($cities);
    }
}
