<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TassaEssente extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tassa_essenti';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'comuni_tassa_esenti_id',
        'cod_esenz',
        'nome',
        'descrizione',
        'active',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the comune that owns this esenzione.
     */
    public function comune(): BelongsTo
    {
        return $this->belongsTo(Comuni::class, 'comuni_tassa_esenti_id');
    }

    /**
     * Scope a query to only include active esenzioni.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to filter by comune.
     */
    public function scopeByComune(Builder $query, $comuneId): Builder
    {
        return $query->where('comuni_tassa_esenti_id', $comuneId);
    }
}
