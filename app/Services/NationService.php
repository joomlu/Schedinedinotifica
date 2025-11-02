<?php

namespace App\Services;

class NationService
{
    private function readJson(string $relative): array
    {
        $relative = ltrim($relative, '/');
        $candidates = [
            storage_path('app/' . $relative),
            base_path('app/' . $relative),
            resource_path('json/' . basename($relative)),
        ];
        foreach ($candidates as $path) {
            if (is_string($path) && file_exists($path)) {
                $json = @file_get_contents($path);
                if ($json === false) { continue; }
                $arr = json_decode($json, true);
                return is_array($arr) ? $arr : [];
            }
        }
        return [];
    }
    /**
     * Obtiene todas las naciones.
     */
    public function getAllNations(): array
    {
        return $this->readJson('gi_nazioni.json');
    }

    /**
     * Obtiene todas las regiones.
     */
    public function getAllRegions(): array
    {
        return $this->readJson('gi_regioni.json');
    }

    /**
     * Obtiene todas las provincias.
     */
    public function getAllProvinces(): array
    {
        return $this->readJson('gi_province.json');
    }

    public function getAllCities(): array
    {
        return $this->readJson('gi_comuni.json');
    }

    /**
     * Obtiene todos los CAP (desde gi_comuni_cap.json).
     */
    public function getAllCap(): array
    {
        return $this->readJson('gi_comuni_cap.json');
    }

    /**
     * Obtiene las ciudades (comuni) filtradas según la provincia.
     *
     * @param string $provinceCode Sigla de la provincia.
     * @return array Ciudades filtradas.
     */
    public function getCitiesByProvince(string $provinceCode): array
    {
        $cities = $this->getAllCities();
        $filtered = array_filter($cities, function($city) use ($provinceCode) {
            return isset($city['sigla_provincia']) && (string)$city['sigla_provincia'] === (string)$provinceCode;
        });
        return array_values($filtered);
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
        $filtered = array_filter($provinces, function($province) use ($regionCode) {
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
        $filtered = array_filter($capEntries, function($cap) use ($provinceCode) {
            return $cap['sigla_provincia'] == $provinceCode;
        });
        return array_values($filtered);
    }
}
