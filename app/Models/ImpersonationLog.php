<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'impersonator_id',
        'impersonated_id',
        'started_at',
        'ended_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
