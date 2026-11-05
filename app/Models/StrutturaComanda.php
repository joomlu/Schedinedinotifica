<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrutturaComanda extends Model
{
    use HasFactory;
    use AppartieneAStruttura;

    protected $table = 'struttura_comande';

    protected $fillable = [
        'struttura_id',
        'mittente_id',
        'destinatario_id',
        'titolo',
        'messaggio',
        'priorita',
        'stato',
        'letto_at',
        'chiuso_at',
    ];

    protected $casts = [
        'letto_at' => 'datetime',
        'chiuso_at' => 'datetime',
    ];

    public function mittente()
    {
        return $this->belongsTo(User::class, 'mittente_id');
    }

    public function destinatario()
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function scopeVisibleForUser($query, User $user)
    {
        $query->where('struttura_id', $user->struttura_id);

        if ($user->canManageGestioneOperativa($user->struttura_id)) {
            return $query;
        }

        return $query->where(function ($inner) use ($user) {
            $inner->whereNull('destinatario_id')
                ->orWhere('destinatario_id', $user->id)
                ->orWhere('mittente_id', $user->id);
        });
    }

    public function scopeUnreadForUser($query, User $user)
    {
        return $query->visibleForUser($user)
            ->where('stato', 'da_leggere')
            ->where(function ($inner) use ($user) {
                $inner->whereNull('destinatario_id')
                    ->orWhere('destinatario_id', $user->id);
            })
            ->where('mittente_id', '!=', $user->id);
    }
}
