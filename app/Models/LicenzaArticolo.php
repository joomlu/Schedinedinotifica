<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenzaArticolo extends Model
{
    use HasFactory;

    protected $table = 'licenza_articoli';

    protected $fillable = [
        'parent_id',
        'nome',
        'codice',
        'descrizione',
        'accesso_key',
        'prezzo_base',
        'attivo',
        'ordine',
        'note',
    ];

    protected $casts = [
        'attivo' => 'boolean',
        'prezzo_base' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('ordine')->orderBy('nome');
    }

    public function assegnazioni()
    {
        return $this->hasMany(LicenzaAssegnazione::class, 'articolo_id');
    }
}
