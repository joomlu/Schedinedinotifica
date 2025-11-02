<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCity extends Model
{
    protected $table = 'geo_cities';

    public $timestamps = false;

    protected $primaryKey = 'codice_istat';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'codice_istat', 'denominazione_ita', 'denominazione_ita_altra', 'sigla_provincia', 'codice_regione', 'flag_capoluogo', 'codice_belfiore', 'lat', 'lon', 'superficie_kmq', 'codice_sovracomunale',
    ];
}
