<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    use HasFactory;

    public const STATI = [
        'nuovo' => 'Nuovo',
        'da_contattare' => 'Da contattare',
        'in_contatto' => 'In contatto',
        'demo_fissata' => 'Demo fissata',
        'proposta_inviata' => 'Proposta inviata',
        'in_attesa_cliente' => 'In attesa cliente',
        'chiuso_vinto' => 'Chiuso vinto',
        'chiuso_perso' => 'Chiuso perso',
    ];

    public const FONTI = [
        'sito_web' => 'Sito web',
        'manuale_admin' => 'Inserimento manuale',
    ];

    protected $fillable = [
        'lead_code',
        'fonte',
        'assigned_admin_id',
        'created_by_user_id',
        'stato',
        'struttura',
        'nome_cognome',
        'persona_contatto',
        'localita',
        'email',
        'telefono',
        'cellulare',
        'sito_web',
        'modalita_contatto',
        'preferenza_contatto_label',
        'preferenza_contatto_at',
        'qualsiasi_orario',
        'messaggio',
        'note_interne',
        'ultimo_contatto_at',
        'prossimo_contatto_at',
        'chiuso_at',
    ];

    protected $casts = [
        'qualsiasi_orario' => 'boolean',
        'preferenza_contatto_at' => 'datetime',
        'ultimo_contatto_at' => 'datetime',
        'prossimo_contatto_at' => 'datetime',
        'chiuso_at' => 'datetime',
    ];

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function activities()
    {
        return $this->hasMany(CrmLeadActivity::class, 'crm_lead_id')->orderByDesc('created_at');
    }

    public function upcomingActivities()
    {
        return $this->hasMany(CrmLeadActivity::class, 'crm_lead_id')
            ->whereIn('stato', ['da_fare', 'registrata'])
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at');
    }

    public function statoLabel(): string
    {
        return self::STATI[$this->stato] ?? ucfirst(str_replace('_', ' ', (string) $this->stato));
    }

    public function fonteLabel(): string
    {
        return self::FONTI[$this->fonte] ?? ucfirst(str_replace('_', ' ', (string) $this->fonte));
    }

    public function statoBadgeClass(): string
    {
        return match ($this->stato) {
            'nuovo' => 'bg-primary-subtle text-primary',
            'da_contattare' => 'bg-warning-subtle text-warning',
            'in_contatto' => 'bg-info-subtle text-info',
            'demo_fissata' => 'bg-success-subtle text-success',
            'proposta_inviata' => 'bg-secondary-subtle text-secondary',
            'in_attesa_cliente' => 'bg-dark-subtle text-body',
            'chiuso_vinto' => 'bg-success-subtle text-success',
            'chiuso_perso' => 'bg-danger-subtle text-danger',
            default => 'bg-light text-body',
        };
    }
}
