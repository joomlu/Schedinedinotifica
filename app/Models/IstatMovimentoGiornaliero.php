<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Model;

class IstatMovimentoGiornaliero extends Model
{
    use AppartieneAStruttura;

    protected $table = 'istat_movimenti_giornalieri';

    protected $fillable = [
        'struttura_id', 'giorno', 'aperta', 'movimento_zero', 'camere_disponibili', 'letti_disponibili',
        'camere_occupate', 'arrivi', 'partenze', 'presenti', 'override_payload', 'note', 'confermato_il', 'confermato_da',
    ];

    protected $casts = [
        'giorno' => 'date',
        'aperta' => 'boolean',
        'movimento_zero' => 'boolean',
        'override_payload' => 'array',
        'confermato_il' => 'datetime',
    ];
}
