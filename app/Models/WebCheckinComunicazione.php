<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCheckinComunicazione extends Model
{
    protected $table = 'web_checkin_comunicazioni';
    protected $guarded = ['id'];
    protected $casts = ['contenuto' => 'encrypted:array', 'nascosta_at' => 'datetime', 'tentativo_at' => 'datetime', 'accettato_at' => 'datetime'];
    protected $hidden = ['contenuto', 'idempotenza'];
}
