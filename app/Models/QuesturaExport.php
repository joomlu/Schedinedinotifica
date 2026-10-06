<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesturaExport extends Model
{
    protected $hidden = ['path'];

    protected $table = 'questura_exports';

    protected $fillable = [
        'status', 'sha256', 'byte_size', 'charset', 'component_ids',
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
        'finalized_at' => 'datetime', 'payload_deleted_at' => 'datetime', 'reconciled_at' => 'datetime',
        'component_ids' => 'array',
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
    ];
    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('TXT Questura immutabile.'));
        static::deleting(fn () => throw new \LogicException('Archivio Questura non eliminabile tramite CRUD.'));
    }
}
