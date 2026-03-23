<?php

namespace App\Models;

use App\Models\Concerns\AppartieneAStruttura;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrutturaAuditLog extends Model
{
    use HasFactory;
    use AppartieneAStruttura;

    public $timestamps = false;

    protected $table = 'struttura_audit_logs';

    protected $fillable = [
        'struttura_id',
        'user_id',
        'route_name',
        'metodo',
        'entita_tipo',
        'entita_id',
        'descrizione',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function utente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
