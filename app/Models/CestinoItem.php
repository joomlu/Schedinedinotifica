<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CestinoItem extends Model
{
    protected $table = 'cestino_items';

    protected $fillable = [
        'struttura_id',
        'user_id',
        'entity_type',
        'entity_class',
        'original_id',
        'title',
        'code',
        'circuito',
        'source',
        'payload',
        'deleted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'deleted_at' => 'datetime',
    ];
}
