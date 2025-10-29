<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string|null $denominazione
 * @property string|null $sigla_provincia
 * @property string|null $codice_regione
 * @property int|null $province_id
 * @property int|null $region_id
 * @property string|null $codice_questura
 * @property-read Province|null $province
 * @property-read Region|null $region
 * @property-read ComuneLogo|null $logo
 */
class Comuni extends Model
{
    protected $table = 'comuni';

    protected $fillable = ['denominazione', 'sigla_provincia', 'codice_regione', 'province_id', 'region_id', 'codice_questura', 'created_at', 'updated_at'];

    protected $guarded = ['id'];

    /**
     * Province relationship.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Region relationship.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Logo relationship.
     */
    public function logo(): HasOne
    {
        return $this->hasOne(ComuneLogo::class, 'comune_id');
    }
}
