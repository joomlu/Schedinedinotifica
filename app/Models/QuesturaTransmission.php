<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuesturaTransmission extends Model
{
    protected $table = 'questura_transmissions';

    protected $hidden = ['result', 'payload', 'response_code', 'response_message', 'response_detail', 'receipt_path', 'receipt_filename'];

    public function esitoSicuro(): array
    {
        return \App\Services\EsitoTrasmissioneQuestura::storico($this->status, $this->result);
    }

    protected $fillable = [
        'identity_reserved_at',
        'sha256', 'byte_size', 'charset', 'component_ids', 'send_key',
        'struttura_id',
        'user_id',
        'questura_export_id',
        'mode',
        'scope_type',
        'dal',
        'al',
        'schedina_ids',
        'schedine_count',
        'righe_count',
        'status',
        'response_code',
        'response_message',
        'response_detail',
        'payload',
        'result',
        'receipt_filename',
        'receipt_path',
        'executed_at',
    ];

    protected $casts = [
        'identity_reserved_at' => 'datetime',
        'finalized_at' => 'datetime', 'payload_deleted_at' => 'datetime', 'reconciled_at' => 'datetime',
        'component_ids' => 'array',
        'dal' => 'date',
        'al' => 'date',
        'schedina_ids' => 'array',
        'payload' => 'array',
        'result' => 'array',
        'executed_at' => 'datetime',
    ];
    protected static function booted(): void
    {
        static::updating(function ($model) {
            if ($model->getRawOriginal('finalized_at')) { throw new \LogicException('Trasmissione Questura finalizzata e minimizzata.'); }
            if ($model->getRawOriginal('status') !== 'in_progress') {
                throw new \LogicException('Esito storico Questura immutabile: utilizzare un nuovo evento di audit.');
            }
            foreach (['identity_reserved_at', 'struttura_id', 'user_id', 'questura_export_id', 'mode', 'dal', 'al', 'schedina_ids', 'payload', 'sha256', 'byte_size', 'charset', 'send_key', 'component_ids', 'scope_type', 'schedine_count', 'righe_count', 'executed_at'] as $field) {
                if ($model->isDirty($field)) { throw new \LogicException('Snapshot Questura immutabile.'); }
            }
        });
        static::deleting(fn () => throw new \LogicException('Archivio Questura non eliminabile tramite CRUD.'));
    }
}
