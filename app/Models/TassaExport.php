<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class TassaExport extends Model
{
    use AppartieneAStruttura;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['snapshot'];

    protected $casts = ['snapshot' => 'encrypted:array', 'created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Gli export Tassa consolidati sono immutabili.'));
        static::deleting(fn () => throw new LogicException('Gli export Tassa consolidati sono immutabili.'));
    }
}
