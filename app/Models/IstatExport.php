<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IstatExport extends Model
{
    protected $table = 'istat_exports';

    protected $fillable = [
        'struttura_id', 'user_id', 'dal', 'al', 'filename', 'path', 'schedine_count', 'movimenti_count', 'schedina_ids',
    ];

    protected $casts = [
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
    ];
}
