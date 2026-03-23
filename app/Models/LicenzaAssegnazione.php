<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenzaAssegnazione extends Model
{
    use HasFactory;

    protected $table = 'licenza_assegnazioni';

    protected $fillable = [
        'numero_licenza',
        'articolo_id',
        'proprietario_id',
        'struttura_id',
        'admin_id',
        'quantita',
        'prezzo',
        'stato_pagamento',
        'data_inizio',
        'data_scadenza',
        'attiva',
        'note',
    ];

    protected $casts = [
        'attiva' => 'boolean',
        'quantita' => 'integer',
        'prezzo' => 'decimal:2',
        'data_inizio' => 'date',
        'data_scadenza' => 'date',
    ];

    public function articolo()
    {
        return $this->belongsTo(LicenzaArticolo::class, 'articolo_id');
    }

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id');
    }

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getCodiceTrackingAttribute(): string
    {
        $baseCode = $this->articolo?->codice ?: ($this->articolo?->accesso_key ?: 'LIC');
        $normalized = strtoupper(str_replace([' ', '/'], '-', $baseCode));

        return $normalized.'-'.($this->numero_licenza ?: 'PENDING');
    }
}
