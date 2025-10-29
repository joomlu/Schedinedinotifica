<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $comune_id
 * @property string $logo_filename
 * @property-read Comuni $comune
 */
class ComuneLogo extends Model
{
    protected $table = 'comune_logos';

    protected $fillable = [
        'comune_id',
        'logo_filename',
    ];

    /**
     * Get the comune that owns this logo.
     */
    public function comune(): BelongsTo
    {
        return $this->belongsTo(Comuni::class, 'comune_id');
    }
}
