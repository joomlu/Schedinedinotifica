<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classificazione extends Model
{
    use HasFactory;

    protected $table = 'classificazioni';
    protected $fillable = ['nome'];

    public function tipologieStruttura()
    {
        return $this->belongsToMany(TipologiaStruttura::class, 'classificazione_tipologia', 'classificazione_id', 'tipologia_struttura_id');
    }
}
