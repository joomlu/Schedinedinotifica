<?php

namespace GeoItalia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoNazione extends Model
{
    use HasFactory;

    protected $table = 'geo_nazioni';

    // Alias compatibili con i dataset legacy (denominazione_cittadinanza).
    protected $appends = ['denominazione_cittadinanza'];

    protected $fillable = [
        'codice_iso2',
        'nome',
        'cittadinanza',
        'is_italia',
    ];

    public function regioni()
    {
        return $this->hasMany(GeoRegione::class, 'geo_nazione_id');
    }

    public function getDenominazioneCittadinanzaAttribute(): string
    {
        return $this->cittadinanza ?: $this->nome;
    }
}
