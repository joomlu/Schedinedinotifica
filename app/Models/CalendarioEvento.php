<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Model;

class CalendarioEvento extends Model
{
    use AppartieneAStruttura;

    protected $table = 'calendario_eventi';

    protected $fillable = [
        'struttura_id',
        'ambito',
        'user_scope_id',
        'tipo',
        'titolo',
        'descrizione',
        'data_evento',
        'ora_evento',
        'priorita',
        'stato',
        'created_by',
        'updated_by',
        'closed_by',
        'visto_at',
        'completato_at',
        'chiuso_at',
    ];

    protected $casts = [
        'data_evento' => 'date',
        'visto_at' => 'datetime',
        'completato_at' => 'datetime',
        'chiuso_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_scope_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function prioritaLabel(): string
    {
        return match ((string) $this->priorita) {
            'bassa' => 'Bassa',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
            default => 'Normale',
        };
    }

    public function statoLabel(): string
    {
        return match ((string) $this->stato) {
            'vista' => 'Vista',
            'completata' => 'Completata',
            'chiusa' => 'Chiusa',
            default => 'Da fare',
        };
    }

    public function tipoLabel(): string
    {
        return match ((string) $this->tipo) {
            'compleanno' => 'Compleanno',
            'checkin' => 'Check-in',
            'checkout' => 'Check-out',
            default => 'Manuale',
        };
    }

    public function badgeClass(): string
    {
        if ($this->tipo === 'compleanno') {
            return 'bg-warning-subtle text-warning';
        }

        if ($this->tipo === 'checkin') {
            return 'bg-success-subtle text-success';
        }

        if ($this->tipo === 'checkout') {
            return 'bg-info-subtle text-info';
        }

        return match ((string) $this->priorita) {
            'urgente' => 'bg-danger-subtle text-danger',
            'alta' => 'bg-warning-subtle text-warning',
            'bassa' => 'bg-light text-body',
            default => 'bg-primary-subtle text-primary',
        };
    }
}
