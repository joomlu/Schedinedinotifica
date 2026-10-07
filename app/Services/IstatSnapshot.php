<?php

namespace App\Services;

use Illuminate\Support\Collection;

final class IstatSnapshot
{
    public function make(Collection $schedine): array
    {
        $fields = ['name', 'surname', 'sex', 'relationship', 'arrive', 'departure', 'cant_people', 'room', 'beds',
            'oa_country', 'oa_city', 'oa_prov', 'oa_city_nac', 'oa_date_nac', 'or_country', 'or_city', 'or_prov',
            'istat_tipo_turismo', 'istat_mezzo_trasporto', 'istat_canale_prenotazione', 'istat_titolo_studio', 'istat_professione',
            'date_nac', 'city_nac', 'country_nac', 'country', 'city', 'province', 'comune_nac', 'province_nac'];
        $snapshot = [];
        foreach ($schedine as $schedina) {
            $snapshot['S'.$schedina->id] = hash('sha256', json_encode($schedina->only($fields), JSON_THROW_ON_ERROR));
            foreach ($schedina->componenti as $component) {
                $snapshot['C'.$component->id] = hash('sha256', json_encode([
                    $component->only($fields), $schedina->arrive, $schedina->departure,
                ], JSON_THROW_ON_ERROR));
            }
        }
        ksort($snapshot);

        return $snapshot;
    }
}
