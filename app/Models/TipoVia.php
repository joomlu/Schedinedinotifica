<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVia extends Model
{
    use HasFactory, \App\Models\Concerns\AppartieneAStruttura;

    protected static function booted(): void
    {
        static::addGlobalScope('catalogo_corrente', function ($query) {
            $sid = \App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
            $sid ? $query->where('tipo_via.struttura_id', $sid) : $query->whereRaw('1 = 0');
        });
    }

    protected $casts = ['attivo' => 'boolean'];

    protected $table = 'tipo_via';

    protected $fillable = [
        'struttura_id',
        'nome',
        'abbr',
        'descrizione',
        'attivo',
    ];
}
