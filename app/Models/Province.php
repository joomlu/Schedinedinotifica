<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $sigla_provincia
 * @property string|null $denominazione
 * @property string|null $codice_regione
 * @property int|null $region_id
 * @property-read Region|null $region
 */
class Province extends Model
{
    protected $table = 'province';

    protected $fillable = [
        'sigla_provincia',
        'denominazione',
        'codice_regione',
        'region_id',
    ];

    /**
     * Get the region that owns the province.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Get the comuni for this province.
     */
    public function comuni(): HasMany
    {
        return $this->hasMany(Comuni::class, 'province_id');
    }
}
