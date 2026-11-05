<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmLeadActivity extends Model
{
    use HasFactory;

    public const TIPI = [
        'richiesta_web' => 'Richiesta web',
        'nota' => 'Nota',
        'telefono' => 'Telefonata',
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'ticket' => 'Ticket',
        'riunione' => 'Riunione',
        'demo' => 'Demo',
    ];

    public const DIREZIONI = [
        'entrata' => 'Cliente verso di noi',
        'uscita' => 'Nostra risposta / uscita',
        'interna' => 'Nota interna',
    ];

    public const STATI = [
        'registrata' => 'Registrata',
        'da_fare' => 'Da fare',
        'completata' => 'Completata',
        'annullata' => 'Annullata',
    ];

    protected $fillable = [
        'crm_lead_id',
        'user_id',
        'tipo',
        'direzione',
        'stato',
        'titolo',
        'descrizione',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'crm_lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPI[$this->tipo] ?? ucfirst(str_replace('_', ' ', (string) $this->tipo));
    }

    public function statoLabel(): string
    {
        return self::STATI[$this->stato] ?? ucfirst(str_replace('_', ' ', (string) $this->stato));
    }

    public function direzioneLabel(): string
    {
        return self::DIREZIONI[$this->direzione] ?? ucfirst(str_replace('_', ' ', (string) $this->direzione));
    }
}
