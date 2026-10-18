<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCheckinModello extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $casts = ['impostazioni' => 'array'];
    protected $table = 'web_checkin_modelli';
    protected $fillable = ['struttura_id', 'iniziale', 'nome', 'oggetto', 'corpo', 'impostazioni'];
}
