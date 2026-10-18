<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class Schedina extends Model
{
    use AppartieneAStruttura;

    public function save(array $options = [])
    {
        if (app()->runningInConsole()) return parent::save($options);
        // Serializza classificazioni e rinomine della stessa struttura.
        return \Illuminate\Support\Facades\DB::transaction(function () use ($options) {
            $sid = $this->struttura_id ?: (\App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id);
            if ($sid) \Illuminate\Support\Facades\DB::table('struttura')->where('id', $sid)->lockForUpdate()->first();
            return parent::save($options);
        });
    }

    protected static function booted(): void
    {
        static::saving(function (self $record) {
            if ($record->isInArrivi()) $record->arrive = \App\Support\ArriviDay::today();
            if ($record->exists && ($record->getRawOriginal('is_arrive') || $record->getRawOriginal('circuito') === 'arrivi') && !$record->isInArrivi()) {
                $record->arrive = \App\Support\ArriviDay::today();
                app(\App\Services\SchedinaSoggiornoValidation::class)->validate(Struttura::withoutGlobalScopes()->findOrFail($record->struttura_id), $record->arrive, $record->departure);
            }
            if (app()->runningInConsole()) return;
            $sid = (int) ($record->struttura_id ?: (\App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id));
            app(\App\Services\TitoliStrutturaService::class)->validateSelection($record->type, $sid, $record->exists ? $record->getRawOriginal('type') : null);
            $values = []; $old = [];
            foreach (['group', 'subgroup', 'subgroup1'] as $field) {
                $values[$field] = $record->getAttribute('customer_'.$field);
                $old[$field] = $record->exists ? $record->getRawOriginal('customer_'.$field) : null;
            }
            app(\App\Services\GruppiStrutturaService::class)->validateSelection($values, $sid, $record->exists ? $old : [], 'customer_');
        });
    }

    protected $table = 'schedina';
    protected $fillable = ['scheda', 'circuito', 'type','name','surname', 'customer_id', 'customer_type_housed', 'customer_group', 'customer_subgroup', 'customer_subgroup1', 'customer_email', 'customer_phone', 'customer_cellphone', 'customer_fax', 'customer_observation', 'customer_anag_observation', 'customer_privacy_consent', 'customer_privacy_consent_at', 'customer_marketing_consent', 'customer_marketing_consent_at', 'customer_communication_consent', 'customer_communication_consent_at', 'sex','relationship','exent','arrive','departure','cant_people','room',
    'beds','observation','oa_country','oa_city','oa_region','oa_prov','oa_cap','oa_city_nac','oa_date_nac','or_country','or_city'
    ,'or_region','or_prov','or_cap','or_typeaway','or_address','or_num','or_doc','or_doctype','or_published_date'
    ,'or_expire','or_published','or_published_country', 'is_arrive', 'created_at', 'updated_at', 'struttura_id',
    'fonte_prenotazione','id_prenotazione_esterna','istat_tipo_turismo','istat_mezzo_trasporto','istat_canale_prenotazione','istat_titolo_studio','istat_professione','istat_non_turista','agganciata_il','agganciata_da', 'or_published_city', 'questura_exported_at', 'questura_export_count', 'last_questura_export_id', 'questura_sent_at', 'questura_send_count', 'last_questura_transmission_id', 'istat_exported_at', 'istat_export_count', 'last_istat_export_id', 'istat_sent_at', 'istat_send_count', 'last_istat_transmission_id'];
    protected $guarded = ['id'];

    protected $casts = [
        'agganciata_il' => 'datetime',
        'is_arrive' => 'boolean',
        'istat_non_turista' => 'boolean',
        'customer_privacy_consent' => 'boolean',
        'customer_marketing_consent' => 'boolean',
        'customer_communication_consent' => 'boolean',
        'customer_privacy_consent_at' => 'datetime',
        'customer_marketing_consent_at' => 'datetime',
        'customer_communication_consent_at' => 'datetime',
        'questura_exported_at' => 'datetime',
        'questura_sent_at' => 'datetime',
        'istat_exported_at' => 'datetime',
        'istat_sent_at' => 'datetime',
    ];

    public function isInArrivi(): bool
    {
        return (bool) $this->getAttribute('is_arrive') || $this->getAttribute('circuito') === 'arrivi';
    }

    public function scopeInArrivi($query)
    {
        return $query->where(fn ($q) => $q->where('is_arrive', true)->orWhere('circuito', 'arrivi'));
    }

    public function scopeEffettive($query)
    {
        return $query->where('is_arrive', false)
            ->where(fn ($q) => $q->where('circuito', 'schedina')->orWhereNull('circuito'));
    }

    // Data effettiva calcolata a ogni lettura: vale anche senza richieste a mezzanotte.
    // La data memorizzata viene materializzata al prossimo salvataggio, mai la partenza.
    public function getArriveAttribute($value)
    {
        return $this->isInArrivi() ? \App\Support\ArriviDay::today() : $value;
    }

    public function camere()
    {
        return $this->hasMany(SchedinaCamera::class, 'schedina_id');
    }

    public function componenti()
    {
        return $this->hasMany(Componenti::class, 'schedina_id');
    }

    public function agganciataDa()
    {
        return $this->belongsTo(User::class, 'agganciata_da');
    }
}
