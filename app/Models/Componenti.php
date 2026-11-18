<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class Componenti extends Model
{
    use AppartieneAStruttura;

    protected static function booted(): void
    {
        static::saving(function (self $record) {
            $sid = (int) ($record->struttura_id ?: \App\Support\StrutturaCorrente::getId());
            if (!app(\App\Services\CustomerSharing::class)->groupFor($sid)) return;
            if ($record->customer_id) {
                $customer = app(\App\Services\CustomerSharing::class)->visibleQuery(Customers::withoutGlobalScopes())
                    ->where('struttura_id', $sid)->whereKey($record->customer_id)->exists();
                if (!$customer) throw \Illuminate\Validation\ValidationException::withMessages(['customer_id' => 'Cliente non disponibile nella struttura corrente.']);
            }
            if ($record->schedina_id && !Schedina::withoutGlobalScopes()->where('struttura_id', $sid)->whereKey($record->schedina_id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['schedina_id' => 'Schedina non disponibile nella struttura corrente.']);
            }
        });
    }

    protected $table = 'componenti';
    protected $fillable = ['struttura_id', 'name', 'schedina_id', 'customer_id', 'surname','sex','relationship','exent','province_nac','city_nac','date_nac','cap_nac',
    'country_nac', 'regione_nac', 'comune_nac', 'country','regione','typeaway','address','number','cap','province','city', 'created_at', 'updated_at'];
    protected $guarded = ['id'];

    public function schedina()
    {
        return $this->belongsTo(Schedina::class, 'schedina_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }
}
