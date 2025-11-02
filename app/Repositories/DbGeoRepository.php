<?php

namespace App\Repositories;

use App\Models\GeoCap;
use App\Models\GeoCity;
use App\Models\GeoNation;
use App\Models\GeoProvince;
use App\Models\GeoRegion;

class DbGeoRepository implements GeoRepositoryInterface
{
    public function getAllNations(): array
    {
        return GeoNation::query()->get(['sigla_nazione', 'denominazione_nazione', 'denominazione_cittadinanza'])->toArray();
    }

    public function getAllRegions(): array
    {
        return GeoRegion::query()->get(['codice_regione', 'denominazione_regione', 'tipologia_regione', 'ripartizione_geografica'])->toArray();
    }

    public function getAllProvinces(): array
    {
        return GeoProvince::query()->get(['sigla_provincia', 'denominazione_provincia', 'codice_regione', 'tipologia_provincia'])->toArray();
    }

    public function getAllCities(): array
    {
        return GeoCity::query()->get(['codice_istat', 'denominazione_ita', 'denominazione_ita_altra', 'sigla_provincia', 'codice_regione', 'flag_capoluogo', 'codice_belfiore', 'lat', 'lon', 'superficie_kmq', 'codice_sovracomunale'])->toArray();
    }

    public function getAllCap(): array
    {
        $caps = GeoCap::query()->get(['codice_istat', 'cap', 'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'denominazione_regione'])->toArray();
        // Arricchisci con denominazione_ita (nome comune) usando codice_istat
        $istats = array_values(array_filter(array_map(fn ($c) => $c['codice_istat'] ?? null, $caps)));
        if (! empty($istats)) {
            $cities = GeoCity::query()
                ->whereIn('codice_istat', $istats)
                ->get(['codice_istat', 'denominazione_ita', 'sigla_provincia'])
                ->keyBy('codice_istat');
            foreach ($caps as &$c) {
                $istat = $c['codice_istat'] ?? null;
                if ($istat && isset($cities[$istat])) {
                    $c['denominazione_ita'] = $cities[$istat]['denominazione_ita'];
                }
            }
            unset($c);
        }

        return $caps;
    }

    public function getProvincesByRegion(string $regionCode): array
    {
        return GeoProvince::query()
            ->where('codice_regione', $regionCode)
            ->get(['sigla_provincia', 'denominazione_provincia', 'codice_regione', 'tipologia_provincia'])
            ->toArray();
    }

    public function getCitiesByProvince(string $provinceCode): array
    {
        return GeoCity::query()
            ->where('sigla_provincia', $provinceCode)
            ->get(['codice_istat', 'denominazione_ita', 'denominazione_ita_altra', 'sigla_provincia', 'codice_regione', 'flag_capoluogo', 'codice_belfiore', 'lat', 'lon', 'superficie_kmq', 'codice_sovracomunale'])
            ->toArray();
    }

    public function getCapByProvince(string $provinceCode): array
    {
        $caps = GeoCap::query()
            ->where('sigla_provincia', $provinceCode)
            ->get(['codice_istat', 'cap', 'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'denominazione_regione'])
            ->toArray();
        // Join in memoria con GeoCity per aggiungere il nome del comune
        $istats = array_values(array_filter(array_map(fn ($c) => $c['codice_istat'] ?? null, $caps)));
        if (! empty($istats)) {
            $cities = GeoCity::query()
                ->whereIn('codice_istat', $istats)
                ->get(['codice_istat', 'denominazione_ita', 'sigla_provincia'])
                ->keyBy('codice_istat');
            foreach ($caps as &$c) {
                $istat = $c['codice_istat'] ?? null;
                if ($istat && isset($cities[$istat])) {
                    $c['denominazione_ita'] = $cities[$istat]['denominazione_ita'];
                }
            }
            unset($c);
        }

        return $caps;
    }
}
