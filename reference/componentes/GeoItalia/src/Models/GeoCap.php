<?php

namespace GeoItalia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoCap extends Model
{
    use HasFactory;

    protected $table = 'geo_cap';

    protected $fillable = [
        'cap',
        'lat',
        'lng',
    ];

    public function comuni()
    {
        return $this->belongsToMany(GeoComune::class, 'geo_comuni_cap', 'geo_cap_id', 'geo_comune_id')
            ->withPivot(['principale', 'priorita', 'localita', 'note'])
            ->withTimestamps();
    }

    public function pivotComuni()
    {
        return $this->hasMany(GeoComuneCap::class, 'geo_cap_id');
    }
}
