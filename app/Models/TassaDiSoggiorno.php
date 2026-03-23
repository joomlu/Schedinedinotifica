<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Model;

class TassaDiSoggiorno extends Model
{
    use AppartieneAStruttura;

    protected $table = 'tassa_di_soggiorno';

    protected $fillable = [
        'struttura_id',
        'tassa_soggiorno',
        'giorni_massimo',
        'inizio',
        'fine',
        'max_age_children',
        'min_age_adult',
        'note',
    ];

    protected $casts = [
        'inizio' => 'date',
        'fine' => 'date',
        'tassa_soggiorno' => 'decimal:2',
        'giorni_massimo' => 'integer',
        'max_age_children' => 'integer',
        'min_age_adult' => 'integer',
    ];

    public function struttura()
    {
        return $this->belongsTo(\App\Models\Struttura::class, 'struttura_id');
    }
}
