<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoNation extends Model
{
    // Tabella rinominata: 'stati'
    protected $table = 'stati';

    public $timestamps = false;

    protected $fillable = [
        'sigla_nazione', 'denominazione_nazione', 'denominazione_cittadinanza',
    ];
}
