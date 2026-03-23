<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesturaExport extends Model
{
    protected $table = 'questura_exports';

    protected $fillable = [
        'struttura_id',
        'user_id',
        'dal',
        'al',
        'filename',
        'path',
        'schedine_count',
        'righe_count',
        'schedina_ids',
    ];

    protected $casts = [
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
    ];
}
