<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebCheckinRichiesta extends Model
{
    protected $table = 'web_checkin_richieste';

    protected $fillable = [
        'struttura_id',
        'schedina_id',
        'codice',
        'numero_prenotazione',
        'email',
        'whatsapp',
        'nome_referente',
        'arrivo',
        'partenza',
        'quantita_persone',
        'note',
        'token',
        'stato',
        'ultimo_accesso_at',
        'compilato_at',
        'convertito_at',
    ];

    protected $casts = [
        'arrivo' => 'date',
        'partenza' => 'date',
        'ultimo_accesso_at' => 'datetime',
        'compilato_at' => 'datetime',
        'convertito_at' => 'datetime',
    ];

    public function schedina(): BelongsTo
    {
        return $this->belongsTo(Schedina::class, 'schedina_id');
    }

    public function struttura(): BelongsTo
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }
}
