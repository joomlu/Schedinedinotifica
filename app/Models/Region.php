<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $codice_regione
 * @property string|null $denominazione
 * @property int|null $stato_id
 */
class Region extends Model
{
    protected $table = 'regioni';

    protected $fillable = [
        'codice_regione',
        'denominazione',
        'stato_id',
    ];

    /**
     * Get the stato that owns the region.
     */
    public function stato(): BelongsTo
    {
        return $this->belongsTo(Stati::class, 'stato_id');
    }

    /**
     * Get the provinces for this region.
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'region_id');
    }

    /**
     * Get the comuni for this region.
     */
    public function comuni(): HasMany
    {
        return $this->hasMany(Comuni::class, 'region_id');
    }
}
