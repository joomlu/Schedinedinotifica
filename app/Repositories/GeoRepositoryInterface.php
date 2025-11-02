<?php

namespace App\Repositories;

interface GeoRepositoryInterface
{
    // Raw lists
    public function getAllNations(): array;

    public function getAllRegions(): array;

    public function getAllProvinces(): array;

    public function getAllCities(): array;

    public function getAllCap(): array;

    // Filters
    public function getProvincesByRegion(string $regionCode): array;

    public function getCitiesByProvince(string $provinceCode): array;

    public function getCapByProvince(string $provinceCode): array;
}
