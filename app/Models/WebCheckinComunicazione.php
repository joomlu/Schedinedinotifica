<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCheckinComunicazione extends Model
{
    protected $table = 'web_checkin_comunicazioni';
    protected $guarded = ['id'];
    protected $casts = ['contenuto' => 'encrypted:array'];
    protected $hidden = ['contenuto', 'idempotenza'];
}
