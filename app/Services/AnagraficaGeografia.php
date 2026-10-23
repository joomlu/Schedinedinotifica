<?php

namespace App\Services;

use App\Models\GeoComune;
use App\Models\GeoComuneCap;
use Illuminate\Database\Eloquent\Model;

class AnagraficaGeografia
{
    public function form(Model $entity): ?array
    {
        if (blank($entity->citta) || blank($entity->provincia) || blank($entity->cap)) return null;
        $rows = GeoComune::with('provincia.regione.nazione')->where('nome', $entity->citta)
            ->whereHas('provincia', fn ($q) => $q->where('sigla', $entity->provincia)->orWhere('nome', $entity->provincia))->get();
        if ($rows->count() !== 1) return null;
        $city = $rows->first(); $province = $city->provincia; $region = $province?->regione; $country = $region?->nazione;
        if (! $country || mb_strtolower(trim((string) $country->nome)) !== mb_strtolower(trim((string) $entity->nazione))
            || mb_strtolower(trim((string) $region->nome)) !== mb_strtolower(trim((string) $entity->regione))) return null;
        if (! GeoComuneCap::where('geo_comune_id', $city->id)->where('cap', $entity->cap)->exists()) return null;
        return ['nazione_id' => $country->id, 'nazione' => $country->nome,
            'regione_id' => $region->id, 'regione_text' => $region->nome,
            'provincia_id' => $province->id, 'provincia_text' => $province->sigla,
            'comune_id' => $city->id, 'comune_text' => $city->nome, 'cap' => $entity->cap];
    }
}
