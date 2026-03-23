<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;
    use AppartieneAStruttura;

    public const STATI = [
        'aperto' => 'Aperto',
        'in_lavorazione' => 'In lavorazione',
        'in_attesa_struttura' => 'In attesa struttura',
        'chiuso' => 'Chiuso',
    ];

    public const PRIORITA = [
        'bassa' => 'Bassa',
        'normale' => 'Normale',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public const CATEGORIE = [
        'clienti_schedine' => 'Clienti e schedine',
        'invio_telematico' => 'Invio telematico',
        'configurazioni' => 'Configurazioni',
        'accesso' => 'Accesso e utenti',
        'statistica' => 'Statistica',
        'altro' => 'Altro',
    ];

    protected $fillable = [
        'struttura_id',
        'opened_by_user_id',
        'assigned_admin_id',
        'ticket_code',
        'titolo',
        'categoria',
        'priorita',
        'stato',
        'modulo_riferimento',
        'descrizione',
        'ultimo_messaggio_at',
        'ultimo_messaggio_da',
        'last_admin_read_at',
        'last_struttura_read_at',
        'chiuso_at',
    ];

    protected $casts = [
        'ultimo_messaggio_at' => 'datetime',
        'last_admin_read_at' => 'datetime',
        'last_struttura_read_at' => 'datetime',
        'chiuso_at' => 'datetime',
    ];

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'support_ticket_id')->orderBy('created_at');
    }

    public function statoLabel(): string
    {
        return self::STATI[$this->stato] ?? ucfirst((string) $this->stato);
    }

    public function prioritaLabel(): string
    {
        return self::PRIORITA[$this->priorita] ?? ucfirst((string) $this->priorita);
    }

    public function categoriaLabel(): string
    {
        return self::CATEGORIE[$this->categoria] ?? ucfirst(str_replace('_', ' ', (string) $this->categoria));
    }

    public function hasUnreadForAdmin(): bool
    {
        return $this->ultimo_messaggio_da === 'struttura'
            && $this->ultimo_messaggio_at
            && (!$this->last_admin_read_at || $this->ultimo_messaggio_at->gt($this->last_admin_read_at));
    }

    public function hasUnreadForStruttura(): bool
    {
        return $this->ultimo_messaggio_da === 'admin'
            && $this->ultimo_messaggio_at
            && (!$this->last_struttura_read_at || $this->ultimo_messaggio_at->gt($this->last_struttura_read_at));
    }

    public function scopeUnreadForAdmin($query)
    {
        return $query->where('ultimo_messaggio_da', 'struttura')
            ->whereNotNull('ultimo_messaggio_at')
            ->where(function ($inner) {
                $inner->whereNull('last_admin_read_at')
                    ->orWhereColumn('ultimo_messaggio_at', '>', 'last_admin_read_at');
            });
    }

    public function scopeUnreadForStruttura($query)
    {
        return $query->where('ultimo_messaggio_da', 'admin')
            ->whereNotNull('ultimo_messaggio_at')
            ->where(function ($inner) {
                $inner->whereNull('last_struttura_read_at')
                    ->orWhereColumn('ultimo_messaggio_at', '>', 'last_struttura_read_at');
            });
    }
}
