<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipologiaGenerale extends Model
{
    use HasFactory;

    protected $table = 'tipologie_generali';
    protected $fillable = ['nome'];

    public function tipologieStruttura()
    {
        return $this->hasMany(TipologiaStruttura::class, 'tipologia_generale_id');
    }
}
