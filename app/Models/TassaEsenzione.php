<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class TassaEsenzione extends Model
{
    use AppartieneAStruttura;

    protected $table = 'tassa_esenzioni';

    protected $fillable = [
        'struttura_id',
        'codice',
        'descrizione',
        'attivo',
        'richiede_nota',
        'ordine',
    ];

    protected $casts = [
        'attivo' => 'boolean',
        'richiede_nota' => 'boolean',
    ];
}
