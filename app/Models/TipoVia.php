<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVia extends Model
{
    use HasFactory;

    protected $table = 'tipo_via';

    protected $fillable = [
        'nome',
        'abbr',
        'descrizione',
        'attivo',
    ];
}
