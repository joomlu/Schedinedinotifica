<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesturaReceipt extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['path'];
    protected $casts = ['remote_date' => 'date', 'acquired_at' => 'datetime', 'retained_until' => 'datetime', 'purged_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Ricevuta Questura immutabile.'));
        static::deleting(fn () => throw new \LogicException('Ricevuta Questura non eliminabile tramite CRUD.'));
    }
}
