<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrutturaZona extends Model
{
    use HasFactory;

    protected $table = 'struttura_zone';

    protected $fillable = [
        'struttura_id',
        'geo_comune_id',
        'tipo',
        'nome',
        'attiva',
        'ordine',
    ];

    protected $casts = [
        'attiva' => 'boolean',
    ];

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function comune()
    {
        return $this->belongsTo(GeoComune::class, 'geo_comune_id');
    }
}
