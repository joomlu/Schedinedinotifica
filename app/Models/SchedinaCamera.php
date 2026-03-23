<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class SchedinaCamera extends Model
{
    use AppartieneAStruttura;

    protected $table = 'schedina_camere';

    protected $fillable = [
        'struttura_id',
        'schedina_id',
        'numero_camera',
        'posti_letto',
        'note',
        'fonte_camera',
        'camera_esterna_id',
    ];

    public function schedina()
    {
        return $this->belongsTo(Schedina::class, 'schedina_id');
    }
}
