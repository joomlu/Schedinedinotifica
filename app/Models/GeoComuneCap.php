<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoComuneCap extends Model
{
    use HasFactory;

    protected $table = 'geo_comuni_cap';

    protected $fillable = [
        'geo_comune_id',
        'geo_cap_id',
        'principale',
        'priorita',
        'localita',
        'note',
    ];

    public function comune()
    {
        return $this->belongsTo(GeoComune::class, 'geo_comune_id');
    }

    public function cap()
    {
        return $this->belongsTo(GeoCap::class, 'geo_cap_id');
    }
}
