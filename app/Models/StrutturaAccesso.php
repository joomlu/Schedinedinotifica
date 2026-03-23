<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrutturaAccesso extends Model
{
    use HasFactory;
    use AppartieneAStruttura;

    protected $table = 'struttura_accessi';

    protected $fillable = [
        'struttura_id',
        'user_id',
        'entrata_at',
        'uscita_at',
        'ip_entrata',
        'ip_uscita',
        'user_agent',
    ];

    protected $casts = [
        'entrata_at' => 'datetime',
        'uscita_at' => 'datetime',
    ];

    public function utente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
