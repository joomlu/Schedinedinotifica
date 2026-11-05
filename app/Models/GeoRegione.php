<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoRegione extends Model
{
    use HasFactory;

    protected $table = 'geo_regioni';

    protected $fillable = [
        'geo_nazione_id',
        'codice_regione',
        'nome',
    ];

    public function nazione()
    {
        return $this->belongsTo(GeoNazione::class, 'geo_nazione_id');
    }

    public function province()
    {
        return $this->hasMany(GeoProvincia::class, 'geo_regione_id');
    }
}
