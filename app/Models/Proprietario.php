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
        'attivo',
        'note',
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
}
