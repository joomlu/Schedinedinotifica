<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proprietario extends Model
{
    use HasFactory;

    protected $table = 'proprietari';

    protected $fillable = [
        'admin_id',
        'nome',
        'email',
        'telefono',
        'ragione_sociale',
        'codice_fiscale',
        'partita_iva',
        'codice_destinatario',
        'codice_unico',
        'pec',
        'indirizzo',
        'numero_civico',
        'cap',
        'citta',
        'provincia',
        'regione',
        'nazione',
        'geo_manual',
        'latitudine',
        'longitudine',
        'attivo',
        'note',
        'note_amministrative',
    ];

    protected $casts = [
        'attivo' => 'boolean',
        'geo_manual' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function strutture()
    {
        return $this->hasMany(Struttura::class, 'proprietario_id');
    }

    public function utenti()
    {
        return $this->hasMany(User::class, 'proprietario_id');
    }

    public function servizi()
    {
        return $this->belongsToMany(AdminServizio::class, 'admin_servizio_proprietario', 'proprietario_id', 'admin_servizio_id')
            ->withPivot(['struttura_id', 'quantita', 'importo_override', 'note'])
            ->withTimestamps();
    }

    public function fatturazioni()
    {
        return $this->hasMany(ProprietarioFatturazione::class, 'proprietario_id');
    }
}
