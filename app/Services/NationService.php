<?php

namespace App\Services;

use App\Repositories\GeoRepositoryInterface;

class NationService
{
    public function __construct(private readonly GeoRepositoryInterface $repo) {}

    // Raw lists
    public function getAllNations(): array
    {
        return $this->repo->getAllNations();
    }

    public function getAllRegions(): array
    {
        return $this->repo->getAllRegions();
    }

    public function getAllProvinces(): array
    {
        return $this->repo->getAllProvinces();
    }

    public function getAllCities(): array
    {
        return $this->repo->getAllCities();
    }

    public function getAllCap(): array
    {
        return $this->repo->getAllCap();
    }

    // Filters
    public function getCitiesByProvince(string $provinceCode): array
    {
        return $this->repo->getCitiesByProvince($provinceCode);
    }

    public function getProvincesByRegion(string $regionCode): array
    {
        return $this->repo->getProvincesByRegion($regionCode);
    }

    public function getCapByProvince(string $provinceCode): array
    {
        return $this->repo->getCapByProvince($provinceCode);
    }
}
