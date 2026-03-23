<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesturaTransmission extends Model
{
    protected $table = 'questura_transmissions';

    protected $fillable = [
        'struttura_id',
        'user_id',
        'questura_export_id',
        'mode',
        'scope_type',
        'dal',
        'al',
        'schedina_ids',
        'schedine_count',
        'righe_count',
        'status',
        'response_code',
        'response_message',
        'response_detail',
        'payload',
        'result',
        'receipt_filename',
        'receipt_path',
        'executed_at',
    ];

    protected $casts = [
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
        'payload' => 'array',
        'result' => 'array',
        'executed_at' => 'datetime',
    ];
}
