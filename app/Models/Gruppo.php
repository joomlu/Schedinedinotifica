<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gruppo extends Model
{
    protected $table = 'gruppi';

    protected $fillable = [
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
