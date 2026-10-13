<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titolo extends Model
{
    use \App\Models\Concerns\AppartieneAStruttura;

    protected static function booted(): void
    {
        static::addGlobalScope('catalogo_corrente', function ($query) {
            $sid = \App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
            $sid ? $query->where('titolo.struttura_id', $sid) : $query->whereRaw('1 = 0');
        });
    }

    protected $table = 'titolo';
    protected $fillable = ['struttura_id', 'base_nome', 'nome', 'descrizione', 'attivo'];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}
