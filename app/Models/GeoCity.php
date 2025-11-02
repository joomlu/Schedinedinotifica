<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCity extends Model
{
    protected $table = 'geo_cities';
    protected $primaryKey = 'codice_istat';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'codice_istat', 'denominazione_ita', 'denominazione_ita_altra', 'sigla_provincia', 'codice_regione', 'flag_capoluogo', 'codice_belfiore', 'lat', 'lon', 'superficie_kmq', 'codice_sovracomunale'
    ];

    public function province()
    {
        return $this->belongsTo(GeoProvince::class, 'sigla_provincia', 'sigla_provincia');
    }

    public function region()
    {
        return $this->belongsTo(GeoRegion::class, 'codice_regione', 'codice_regione');
    }

    public function caps()
    {
        return $this->hasMany(GeoCap::class, 'codice_istat', 'codice_istat');
    }
}
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
