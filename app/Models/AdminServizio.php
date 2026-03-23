<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminServizio extends Model
{
    use HasFactory;

    protected $table = 'admin_servizi';

    protected $fillable = [
        'user_id',
        'nome',
        'tipo_costo',
        'importo',
        'quantita_default',
        'note',
        'attivo',
    ];

    protected $casts = [
        'importo' => 'decimal:2',
        'quantita_default' => 'integer',
        'attivo' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proprietari()
    {
        return $this->belongsToMany(Proprietario::class, 'admin_servizio_proprietario', 'admin_servizio_id', 'proprietario_id')
            ->withPivot(['struttura_id', 'quantita', 'importo_override', 'note'])
            ->withTimestamps();
    }
}
