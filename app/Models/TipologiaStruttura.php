<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipologiaStruttura extends Model
{
    use HasFactory;

    protected $table = 'tipologie_struttura';
    protected $fillable = ['tipologia_generale_id', 'nome'];

    public function generale()
    {
        return $this->belongsTo(TipologiaGenerale::class, 'tipologia_generale_id');
    }

    public function classificazioni()
    {
        return $this->belongsToMany(Classificazione::class, 'classificazione_tipologia', 'tipologia_struttura_id', 'classificazione_id');
    }
}
