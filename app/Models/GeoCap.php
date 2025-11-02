<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCap extends Model
{
    protected $table = 'geo_caps';
    public $timestamps = false;
    protected $fillable = [
        'codice_istat', 'cap', 'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'denominazione_regione'
    ];

    public function city()
    {
        return $this->belongsTo(GeoCity::class, 'codice_istat', 'codice_istat');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCap extends Model
{
    protected $table = 'geo_caps';

    public $timestamps = false;

    protected $fillable = [
        'codice_istat', 'cap', 'sigla_provincia', 'denominazione_provincia', 'codice_regione', 'denominazione_regione',
    ];
}
