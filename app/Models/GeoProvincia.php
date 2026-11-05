<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoProvincia extends Model
{
    use HasFactory;

    protected $table = 'geo_province';

    protected $fillable = [
        'geo_regione_id',
        'sigla',
        'nome',
        'codice_provincia',
    ];

    public function regione()
    {
        return $this->belongsTo(GeoRegione::class, 'geo_regione_id');
    }

    public function comuni()
    {
        return $this->hasMany(GeoComune::class, 'geo_provincia_id');
    }
}
