<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCheckinModello extends Model
{
    protected $table = 'web_checkin_modelli';
    protected $fillable = ['struttura_id', 'iniziale', 'nome', 'oggetto', 'corpo'];
}
