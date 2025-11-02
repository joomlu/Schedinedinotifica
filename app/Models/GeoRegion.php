<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoRegion extends Model
{
    protected $table = 'geo_regions';

    public $timestamps = false;

    protected $fillable = [
        'codice_regione', 'denominazione_regione', 'tipologia_regione', 'ripartizione_geografica',
    ];
}
