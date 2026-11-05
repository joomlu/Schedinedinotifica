<?php

namespace App\Services;

use Illuminate\Support\Collection;

final class IstatSnapshot
{
    public const FORMAT = 'Ross1000 Emilia-Romagna XML-WS 2.4';

    public function forExport(Collection $schedine, \App\Models\Struttura $struttura): array
    {
        return $this->make($schedine) + [
            '@formato' => self::FORMAT,
            '@provenienze' => hash('sha256', json_encode($schedine->mapWithKeys(fn ($s) => ['S'.$s->id => [
                $s->or_region, $s->componenti->mapWithKeys(fn ($c) => ['C'.$c->id => $c->regione])->sortKeys()->all(),
            ]])->sortKeys()->all(), JSON_THROW_ON_ERROR)),
            '@struttura' => hash('sha256', json_encode($struttura->only([
                'istat_codice_struttura', 'regione', 'tipo_apertura', 'data_apertura',
                'data_chiusura', 'camere_disponibili', 'letti_disponibili',
            ]), JSON_THROW_ON_ERROR)),
        ];
    }

    public function matches(\App\Models\IstatExport $export, Collection $schedine, \App\Models\Struttura $struttura): bool
    {
        if ($export->snapshot === null) {
            return false;
        }
        $expected = $export->snapshot;
        $actual = $this->forExport($schedine, $struttura);
        // Gli snapshot precedenti non attestano il contesto della struttura.
        foreach (['@formato', '@struttura', '@provenienze'] as $key) {
            if (!array_key_exists($key, $expected)) unset($actual[$key]);
        }
        ksort($expected);
        ksort($actual);
        return $expected === $actual;
    }

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
