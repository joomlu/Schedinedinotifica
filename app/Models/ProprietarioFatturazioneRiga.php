<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProprietarioFatturazioneRiga extends Model
{
    use HasFactory;

    protected $table = 'proprietario_fatturazione_righe';

    protected $fillable = [
        'proprietario_fatturazione_id',
        'struttura_id',
        'admin_servizio_id',
        'descrizione',
        'quantita',
        'prezzo_unitario',
        'sconto_tipo',
        'sconto_valore',
        'imponibile',
        'aliquota_iva',
        'totale_iva',
        'totale',
        'note',
    ];

    protected $casts = [
        'quantita' => 'integer',
        'prezzo_unitario' => 'decimal:2',
        'sconto_valore' => 'decimal:2',
        'imponibile' => 'decimal:2',
        'aliquota_iva' => 'decimal:2',
        'totale_iva' => 'decimal:2',
        'totale' => 'decimal:2',
    ];

    public function fatturazione()
    {
        return $this->belongsTo(ProprietarioFatturazione::class, 'proprietario_fatturazione_id');
    }

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }

    public function servizio()
    {
        return $this->belongsTo(AdminServizio::class, 'admin_servizio_id');
    }
}
