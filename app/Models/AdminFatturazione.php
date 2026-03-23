<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFatturazione extends Model
{
    use HasFactory;

    protected $table = 'admin_fatturazioni';

    protected $fillable = [
        'user_id',
        'created_by',
        'numero',
        'data_documento',
        'stato',
        'intestazione',
        'partita_iva',
        'codice_fiscale',
        'pec',
        'indirizzo',
        'cap',
        'citta',
        'provincia',
        'imponibile',
        'totale_sconto',
        'totale_iva',
        'totale',
        'note',
    ];

    protected $casts = [
        'data_documento' => 'date',
        'imponibile' => 'decimal:2',
        'totale_sconto' => 'decimal:2',
        'totale_iva' => 'decimal:2',
        'totale' => 'decimal:2',
    ];

    public function amministratore()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creatore()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function righe()
    {
        return $this->hasMany(AdminFatturazioneRiga::class, 'admin_fatturazione_id');
    }
}
