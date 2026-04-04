<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Proprietario;

class Struttura extends Model
{
    use HasFactory;

    protected $table = 'struttura';

    protected $fillable = [
        'proprietario_id',
        'logo',
        'nome_struttura',
        'cir',
        'tipologia_generale',
        'tipologia_struttura',
        'classificazione',
        'tipologia_generale_id',
        'tipologia_struttura_id',
        'classificazione_id',
        'tipo_apertura',
        'data_apertura',
        'data_chiusura',
        'nazione',
        'regione',
        'provincia',
        'citta',
        'città',
        'logo_citta',
        'logo_città',
        'zona',
        'localita',
        'località',
        'indirizzo',
        'numero_civico',
        'cap',
        'latitudine',
        'longitudine',
        'ragione_sociale',
        'partita_iva',
        'codice_fiscale',
        'cin',
        'codice_unico',
        'camere_disponibili',
        'letti_disponibili',
        'letti_agg',
        'istat_username',
        'istat_password',
        'istat_codice_struttura',
        'istat_ws_url',
        'istat_ws_simulazione',
        'questura_username',
        'questura_password',
        'questura_wskey',
        'questura_codici',
        'questura_puk',
        'questura_ws_simulazione',
        'camere_reali_enabled',
        'attiva',
        'avviso',
        'messaggio_offline',
        'messaggio_avviso',
        'scadenza_servizio',
        'piano',
        'stato_pagamento',
        'numero_ricevuta_pagamento',
        'telefono',
        'telefono_secondario',
        'fax',
        'email',
        'sito_web',
    ];

    protected $casts = [
        'data_apertura' => 'date',
        'data_chiusura' => 'date',
        'scadenza_servizio' => 'date',
        'istat_ws_simulazione' => 'boolean',
        'questura_ws_simulazione' => 'boolean',
        'camere_reali_enabled' => 'boolean',
        'attiva' => 'boolean',
    ];

    public function tipologiaGenerale()
    {
        return $this->belongsTo(TipologiaGenerale::class, 'tipologia_generale_id');
    }

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id');
    }

    public function accessoPrincipale()
    {
        return $this->hasOne(User::class, 'struttura_id')
            ->where('ruolo', 'struttura_user')
            ->latestOfMany();
    }

    public function tipologiaStruttura()
    {
        return $this->belongsTo(TipologiaStruttura::class, 'tipologia_struttura_id');
    }

    public function classificazioneRef()
    {
        return $this->belongsTo(Classificazione::class, 'classificazione_id');
    }

    public function servizioAttivo(): bool
    {
        if ($this->attiva === false) {
            return false;
        }

        if (empty($this->scadenza_servizio)) {
            return false;
        }

        return now()->toDateString() <= $this->scadenza_servizio;
    }

    public function getCittaAttribute($value)
    {
        return $value ?? ($this->attributes['città'] ?? null);
    }

    public function setCittaAttribute($value): void
    {
        $this->attributes['città'] = $value;
    }

    public function getLocalitaAttribute($value)
    {
        return $value ?? ($this->attributes['località'] ?? null);
    }

    public function setLocalitaAttribute($value): void
    {
        $this->attributes['località'] = $value;
    }

    public function getLogoCittaAttribute($value)
    {
        return $value ?? ($this->attributes['logo_città'] ?? null);
    }
}
