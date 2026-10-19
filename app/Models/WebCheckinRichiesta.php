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
        'numero_adulti',
        'numero_bambini',
        'messaggio_ospite',
        'compilazione_snapshot',
        'conversione_snapshot',
        'note',
        'token',
        'stato',
        'ultimo_accesso_at',
        'compilato_at',
        'convertito_at',
    ];

    protected $casts = [
        'numero_adulti' => 'integer',
        'numero_bambini' => 'integer',
        'compilazione_snapshot' => 'encrypted:array',
        'conversione_snapshot' => 'encrypted:array',
        'link_expires_at' => 'datetime',
        'link_revoked_at' => 'datetime',
        'link_issued_at' => 'datetime',
        'arrivo' => 'date',
        'partenza' => 'date',
        'ultimo_accesso_at' => 'datetime',
        'compilato_at' => 'datetime',
        'convertito_at' => 'datetime',
    ];

    public function scopeStoriche($query)
    {
        return $query->where('stato', 'convertito')->where(fn ($q) => $q
            ->whereNotNull('conversione_snapshot')->orWhereHas('schedina', fn ($s) => $s->where('circuito', 'schedina')->whereColumn('schedina.struttura_id', 'web_checkin_richieste.struttura_id')));
    }

    public function processata(): bool
    {
        return $this->stato === 'convertito' && ($this->conversione_snapshot !== null || ($this->schedina?->circuito === 'schedina' && (int) $this->schedina->struttura_id === (int) $this->struttura_id));
    }

    public function comunicazioni()
    {
        return $this->hasMany(WebCheckinComunicazione::class, 'richiesta_id')->where('struttura_id', $this->struttura_id);
    }

    public function schedina(): BelongsTo
    {
        return $this->belongsTo(Schedina::class, 'schedina_id');
    }

    public function struttura(): BelongsTo
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }
}
