<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RilasciatoDa extends Model
{
    protected $table = 'rilasciato_da';

    protected $fillable = [
        'name',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}
