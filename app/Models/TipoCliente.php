<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $deny = fn () => abort(403, 'Classificazioni predefinite del sistema, non modificabili.');
        static::saving($deny);
        static::deleting($deny);
    }

    public function scopeDiSistema(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('codice', array_keys(\App\Services\TipoClienteSistemaService::CLASSIFICAZIONI));
    }

    protected $table = 'tipo_cliente';

    protected $fillable = [
        'codice',
        'descrizione',
        'attivo',
    ];

    protected $casts = [
        'attivo' => 'boolean',
    ];
}

