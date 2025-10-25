<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tassa extends Model
{
    protected $table = 'tassa';
    protected $fillable = ['tassa_soggiorno','giorni_massimo','inizio','fine','province','city','region', 'max_age_children', 'min_age_adult', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
