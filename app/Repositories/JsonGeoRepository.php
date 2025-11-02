<?php

namespace App\Repositories;

class JsonGeoRepository implements GeoRepositoryInterface
{
    protected function readJson(string $relative): array
    {
        $path = storage_path('app/'.ltrim($relative, '/'));
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $arr = json_decode($json, true);

            return is_array($arr) ? $arr : [];
        }

        return [];
    }

    public function getAllNations(): array
    {
        return $this->readJson('gi_nazioni.json');
    }

    public function getAllRegions(): array
    {
        return $this->readJson('gi_regioni.json');
    }

    public function getAllProvinces(): array
    {
        return $this->readJson('gi_province.json');
    }

    public function getAllCities(): array
    {
        return $this->readJson('gi_comuni.json');
    }

    public function getAllCap(): array
    {
        return $this->readJson('gi_comuni_cap.json');
    }

    public function getProvincesByRegion(string $regionCode): array
    {
        $provinces = $this->getAllProvinces();

        return array_values(array_filter($provinces, function ($p) use ($regionCode) {
            return isset($p['codice_regione']) && (string) $p['codice_regione'] === (string) $regionCode;
        }));
    }

    public function getCitiesByProvince(string $provinceCode): array
    {
        $cities = $this->getAllCities();

        return array_values(array_filter($cities, function ($c) use ($provinceCode) {
            return isset($c['sigla_provincia']) && (string) $c['sigla_provincia'] === (string) $provinceCode;
        }));
    }

    public function getCapByProvince(string $provinceCode): array
    {
        $caps = $this->getAllCap();

        return array_values(array_filter($caps, function ($c) use ($provinceCode) {
            return isset($c['sigla_provincia']) && (string) $c['sigla_provincia'] === (string) $provinceCode;
        }));
    }
}
