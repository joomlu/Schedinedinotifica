<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoRegion extends Model
{
    protected $table = 'geo_regions';
    protected $primaryKey = 'codice_regione';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'codice_regione', 'denominazione_regione', 'tipologia_regione', 'ripartizione_geografica'
    ];

    public function provinces()
    {
        return $this->hasMany(GeoProvince::class, 'codice_regione', 'codice_regione');
    }
}
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
