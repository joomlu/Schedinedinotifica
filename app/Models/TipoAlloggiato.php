<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAlloggiato extends Model
{
    use HasFactory;

    protected $table = 'tipo_alloggiato';

    protected $fillable = [
        'codice',
        'descrizione',
        'locked',
    ];

    protected $casts = [
        'locked' => 'boolean',
    ];
}

