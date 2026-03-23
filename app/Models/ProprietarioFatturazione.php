<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProprietarioFatturazione extends Model
{
    use HasFactory;

    protected $table = 'proprietario_fatturazioni';

    protected $fillable = [
        'proprietario_id',
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

    public function proprietario()
    {
        return $this->belongsTo(Proprietario::class, 'proprietario_id');
    }

    public function creatore()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function righe()
    {
        return $this->hasMany(ProprietarioFatturazioneRiga::class, 'proprietario_fatturazione_id');
    }
}
