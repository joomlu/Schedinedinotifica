<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoProvince extends Model
{
    protected $table = 'geo_provinces';
    protected $primaryKey = 'sigla_provincia';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'tipologia_provincia'
    ];

    public function region()
    {
        return $this->belongsTo(GeoRegion::class, 'codice_regione', 'codice_regione');
    }

    public function cities()
    {
        return $this->hasMany(GeoCity::class, 'sigla_provincia', 'sigla_provincia');
    }
}
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
