<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gruppo extends Model
{
    use \App\Models\Concerns\AppartieneAStruttura;

    protected static function booted(): void
    {
        static::addGlobalScope('catalogo_corrente', function ($query) {
            $id = \App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
            $id ? $query->where('gruppi.struttura_id', $id) : $query->whereRaw('1 = 0');
        });
    }

    protected $table = 'gruppi';

    protected $fillable = [
        'struttura_id',
        'nome',
        'descrizione',
        'livello',
        'parent_id',
        'tipo',
    ];

    protected $casts = [
        'livello' => 'integer',
        'parent_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getTipoLabelAttribute(): string
    {
        if ($this->livello) {
            return self::tipoFromLivello((int) $this->livello);
        }

        return $this->tipo ?? 'Legacy';
    }

    public static function tipoFromLivello(?int $livello): string
    {
        return match ($livello) {
            1 => 'Gruppi I',
            2 => 'Gruppi II',
            3 => 'Gruppi III',
            default => 'Gruppo',
        };
    }
}
