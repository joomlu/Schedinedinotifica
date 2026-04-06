<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Struttura;
use App\Models\Proprietario;
use App\Models\AdminServizio;
use App\Models\AdminFatturazione;
use App\Models\CrmLead;
use App\Models\CrmLeadActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'username',
        'display_name',
        'telefono',
        'qualifica',
        'ragione_sociale',
        'codice_fiscale',
        'partita_iva',
        'codice_destinatario',
        'pec',
        'indirizzo',
        'numero_civico',
        'cap',
        'citta',
        'provincia',
        'regione',
        'nazione',
        'ruolo',
        'ruolo_operativo',
        'struttura_id',
        'proprietario_id',
        'attivo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'attivo' => 'boolean',
        'ultimo_accesso_at' => 'datetime',
        'ultima_uscita_at' => 'datetime',
    ];

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id');
    }

    public function proprietariGestiti()
    {
        return $this->hasMany(Proprietario::class, 'admin_id');
    }

    public function adminServizi()
    {
        return $this->hasMany(AdminServizio::class, 'user_id');
    }

    public function adminFatturazioni()
    {
        return $this->hasMany(AdminFatturazione::class, 'user_id');
    }

    public function crmLeadsAssegnati()
    {
        return $this->hasMany(CrmLead::class, 'assigned_admin_id');
    }

    public function crmLeadsCreati()
    {
        return $this->hasMany(CrmLead::class, 'created_by_user_id');
    }

    public function crmLeadActivities()
    {
        return $this->hasMany(CrmLeadActivity::class, 'user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->ruolo === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->ruolo === 'admin';
    }

    public function isProprietario(): bool
    {
        return $this->ruolo === 'proprietario';
    }

    public function isStrutturaUser(): bool
    {
        return $this->ruolo === 'struttura_user';
    }

    public function canManageGestioneOperativa(?int $strutturaId = null): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        if (!$this->isStrutturaUser()) {
            return false;
        }

        if ($strutturaId !== null && (int) $this->struttura_id !== (int) $strutturaId) {
            return false;
        }

        return ($this->ruolo_operativo ?? '') === 'proprietario';
    }

    public function getDisplayNameAttribute($value): string
    {
        if (filled($value)) {
            return (string) $value;
        }

        $name = trim((string) ($this->attributes['name'] ?? ''));
        if ($name === '') {
            return 'Utente';
        }

        return (string) preg_split('/\s+/', $name)[0];
    }

    public function displayLabel(): string
    {
        $display = trim((string) ($this->display_name ?? ''));
        if ($display !== '') {
            return $display;
        }

        $name = trim((string) ($this->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return 'Utente';
    }

    public function ruoloOperativoLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        if ($this->isAdmin()) {
            return 'Admin';
        }

        if ($this->isProprietario()) {
            return 'Proprietario';
        }

        return match ((string) ($this->ruolo_operativo ?? '')) {
            'proprietario' => 'Proprietario',
            'reception' => 'Reception',
            default => 'Utente',
        };
    }
}
