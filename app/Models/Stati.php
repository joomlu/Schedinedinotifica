<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stati extends Model
{
    protected $table = 'stati';

    protected $fillable = [
        'codice_questura',
        'sigla',
        'denominazione',
        'cittadinanza',
        'codice_istat',
        'data_fine_validita',
    ];

    protected $casts = [
        'data_fine_validita' => 'date',
    ];

    // Backward compatibility accessors for old state_nac table fields
    protected $appends = ['name', 'code'];

    /**
     * Get name attribute (alias for denominazione)
     */
    public function getNameAttribute()
    {
        return $this->denominazione;
    }

    /**
     * Get code attribute (alias for sigla)
     */
    public function getCodeAttribute()
    {
        return $this->sigla;
    }

    /**
     * Get the regions for this stato.
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'stato_id');
    }

    /**
     * Get the comuni for this stato.
     */
    public function comuni(): HasMany
    {
        return $this->hasMany(Comuni::class, 'stato_id');
    }

    /**
     * Scope to get only currently valid stati (not expired).
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('data_fine_validita')
                ->orWhere('data_fine_validita', '>=', now());
        });
    }
}
