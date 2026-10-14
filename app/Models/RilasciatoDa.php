<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RilasciatoDa extends Model
{
    use \App\Models\Concerns\AppartieneAStruttura;

    protected static function booted(): void
    {
        static::addGlobalScope('catalogo_corrente', function ($query) {
            $sid = \App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
            $sid ? $query->where('rilasciato_da.struttura_id', $sid) : $query->whereRaw('1 = 0');
        });
    }

    protected $table = 'rilasciato_da';

    protected $fillable = [
        'struttura_id',
        'name',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}
