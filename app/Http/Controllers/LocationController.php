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

    public function provincesAll(Request $request)
    {
        // Registra los parámetros recibidos
        Log::info('Petición a provincesAll', $request->all());

        $provinces = $this->nationService->getAllProvinces();

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
        $provinceCode = $request->input('sigla_provincia');
        $capEntries = $this->nationService->getCapByProvince($provinceCode);

        return response()->json($capEntries);
    }

    public function citiesByProvince(Request $request)
    {
        Log::info('Petición a citiesByProvince', $request->all());
        $provinceCode = $request->input('sigla_provincia');
        $cities = $this->nationService->getCitiesByProvince($provinceCode);
        Log::info('Respuesta de citiesByProvince', $cities);

        return response()->json($cities);
    }
}
