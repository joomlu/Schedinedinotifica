<?php

namespace App\Services;

use App\Models\Stati;
use Illuminate\Support\Facades\Schema;

class NationService
{
    /**
     * Obtiene todas las naciones.
     */
    public function getAllNations(): array
    {
        // Prefer DB 'stati' if available, fallback to JSON file for backward compatibility
        try {
            if (class_exists(Stati::class) && Schema::hasTable('stati')) {
                $rows = Stati::valid()->orderBy('denominazione')->get();

                // Map to legacy JSON structure keys expected by the UI
                return $rows->map(function ($s) {
                    return [
                        'sigla_nazione' => $s->sigla, // best-effort mapping
                        'codice_belfiore' => $s->codice_istat ?: $s->codice_questura,
                        'denominazione_nazione' => $s->denominazione,
                        'denominazione_cittadinanza' => $s->cittadinanza,
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {
            // Fall through to JSON on any issue
        }

        $path = storage_path('app/gi_nazioni.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);

            return json_decode($json, true);
        }

        return [];
    }

    /**
     * Obtiene todas las regiones.
     */
    public function getAllRegions(): array
    {
        $path = storage_path('app/gi_regioni.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);

            return json_decode($json, true);
        }

        return [];
    }

    /**
     * Obtiene todas las provincias.
     */
    public function getAllProvinces(): array
    {
        $path = storage_path('app/gi_province.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);

            return json_decode($json, true);
        }

        return [];
    }

    public function getAllCities(): array
    {
        $path = storage_path('app/gi_comuni.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);

            return json_decode($json, true);
        }

        return [];
    }

    /**
     * Obtiene todos los CAP (desde gi_comuni_cap.json).
     */
    public function getAllCap(): array
    {
        $path = storage_path('app/gi_comuni_cap.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);

            return json_decode($json, true);
        }

        return [];
    }

    /**
     * Obtiene las ciudades (comuni) filtradas según la provincia.
     *
     * @param  string  $provinceCode  Sigla de la provincia.
     * @return array Ciudades filtradas.
     */
    public function getCitiesByProvince(string $provinceCode): array
    {
        $path = storage_path('app/gi_comuni.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $cities = json_decode($json, true);
            $filtered = array_filter($cities, function ($city) use ($provinceCode) {
                return $city['sigla_provincia'] == $provinceCode;
            });

            return array_values($filtered);
        }

        return [];
    }

    // ----------------------------
    // Métodos de filtrado existentes
    // ----------------------------

    /**
     * Filtra las provincias según el código de región.
     */
    public function getProvincesByRegion(string $regionCode): array
    {
        $provinces = $this->getAllProvinces();
        $filtered = array_filter($provinces, function ($province) use ($regionCode) {
            return $province['codice_regione'] == $regionCode;
        });

        return array_values($filtered);
    }

    /**
     * Filtra los CAP según la provincia.
     */
    public function getCapByProvince(string $provinceCode): array
    {
        $capEntries = $this->getAllCap();
        $filtered = array_filter($capEntries, function ($cap) use ($provinceCode) {
            return $cap['sigla_provincia'] == $provinceCode;
        });

        return array_values($filtered);
    }
}
