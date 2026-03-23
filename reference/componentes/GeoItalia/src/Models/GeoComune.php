<?php

namespace GeoItalia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoComune extends Model
{
    use HasFactory;

    protected $table = 'geo_comuni';

    // Espone un alias compatibile con la vecchia colonna `denominazione_ita` usata nelle view legacy.
    protected $appends = ['denominazione_ita'];

    protected $fillable = [
        'geo_provincia_id',
        'codice_istat',
        'nome',
        'lat',
        'lng',
        'logo',
        'logo_citta',
    ];

    public function provincia()
    {
        return $this->belongsTo(GeoProvincia::class, 'geo_provincia_id');
    }

    public function caps()
    {
        return $this->belongsToMany(GeoCap::class, 'geo_comuni_cap', 'geo_comune_id', 'geo_cap_id')
            ->withPivot(['principale', 'priorita', 'localita', 'note'])
            ->withTimestamps();
    }

    public function pivotCaps()
    {
        return $this->hasMany(GeoComuneCap::class, 'geo_comune_id');
    }

    public function getDenominazioneItaAttribute(): string
    {
        return $this->nome;
    }
}
