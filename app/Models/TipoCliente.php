<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    use HasFactory;

    protected $table = 'tipo_cliente';

    protected $fillable = [
        'codice',
        'descrizione',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}

