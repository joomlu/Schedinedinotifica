<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoProvince extends Model
{
    protected $table = 'geo_provinces';

    public $timestamps = false;

    protected $fillable = [
        'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'tipologia_provincia',
    ];
}
