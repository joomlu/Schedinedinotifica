<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IstatTransmission extends Model
{
    protected $table = 'istat_transmissions';

    // Legacy provider data must not leak through implicit JSON serialization.
    protected $hidden = ['result', 'payload', 'response_detail', 'response_message', 'response_code', 'receipt_path', 'receipt_filename'];

    public function esitoSicuro(): array
    {
        return \App\Services\EsitoTrasmissioneIstat::storico($this->status, $this->result);
    }

    protected $fillable = [
        'struttura_id', 'user_id', 'istat_export_id', 'mode', 'dal', 'al', 'schedina_ids', 'schedine_count', 'movimenti_count',
        'status', 'response_code', 'response_message', 'response_detail', 'payload', 'result', 'receipt_filename', 'receipt_path', 'executed_at',
    ];

    protected $casts = [
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
        'payload' => 'array',
        'result' => 'array',
        'executed_at' => 'datetime',
    ];
}
