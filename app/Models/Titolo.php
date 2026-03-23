<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titolo extends Model
{
    protected $table = 'titolo';
    protected $fillable = ['nome', 'descrizione', 'attivo'];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}
