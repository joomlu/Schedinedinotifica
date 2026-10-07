<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IstatExport extends Model
{
    protected static function booted(): void
    {
        static::updating(function (self $export) {
            if ($export->isDirty(['struttura_id', 'user_id', 'dal', 'al', 'path', 'sha256', 'snapshot', 'encrypted_file', 'schedina_ids'])) {
                throw new \LogicException('Identità e contenuto dell’export ISTAT sono immutabili.');
            }
        });
    }

    protected $table = 'istat_exports';

    protected $fillable = [
        'sha256', 'encrypted_file', 'snapshot', 'expires_at', 'minimized_at', 'struttura_id', 'user_id', 'dal', 'al', 'filename', 'path', 'schedine_count', 'movimenti_count', 'schedina_ids',
    ];

    protected $hidden = ['path', 'snapshot'];

    protected $casts = [
        'snapshot' => 'array',
        'encrypted_file' => 'boolean',
        'expires_at' => 'datetime',
        'minimized_at' => 'datetime',
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
    ];
}
