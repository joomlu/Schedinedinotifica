<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class Customers extends Model
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
            if (app()->runningInConsole()) return;
            $sid = (int) ($record->struttura_id ?: (\App\Support\StrutturaCorrente::getId() ?? auth()->user()?->struttura_id));
            app(\App\Services\TitoliStrutturaService::class)->validateSelection($record->type, $sid, $record->exists ? $record->getRawOriginal('type') : null);
            $values = []; $old = [];
            foreach (['group', 'subgroup', 'subgroup1'] as $field) {
                $values[$field] = $record->getAttribute($field);
                $old[$field] = $record->exists ? $record->getRawOriginal($field) : null;
            }
            app(\App\Services\GruppiStrutturaService::class)->validateSelection($values, $sid, $record->exists ? $old : [], '');
        });
    }

    // Usa la tabella rinominata "clienti".
    protected $table = 'clienti';
    protected $fillable = ['struttura_id','numero_cliente','group','subgroup', 'subgroup1', 'sex','type_housed','type','name', 'surname', 
    'country','city', 'region','province','cap','typeaway','address','number','email','phone','fax',
    'cellphone', 'observation', 'country_reg', 'region_reg', 'city_reg', 'prov_reg', 'cap_reg', 'geo_manual_reg', 'ciudadania_reg', 'nac_reg', 
    'type_doc_reg', 'num_doc_reg', 'date_pub_reg', 'expire_reg', 'rilasciato_reg', 'country_doc_reg', 'city_doc_reg', 'observation_reg',
     'privacy_consent', 'privacy_consent_at', 'marketing_consent', 'marketing_consent_at', 'communication_consent', 'communication_consent_at',
     'azienda', 'cap_az', 'cf_az', 'pi_az', 'typeaway_az', 
     'address_az', 'number_az', 'email_az', 'phone_az', 'fax_az', 
     'cellphone_az', 'country_az', 'city_az', 'region_az', 'province_az', 'sdi_az', 'website', 'desc_az', 'nota',
     'created_at', 'updated_at'];
    protected $guarded = ['id']; 

    protected $casts = [
        'privacy_consent' => 'boolean',
        'marketing_consent' => 'boolean',
        'communication_consent' => 'boolean',
        'privacy_consent_at' => 'datetime',
        'marketing_consent_at' => 'datetime',
        'communication_consent_at' => 'datetime',
    ];

    public function schedine()
    {
        return $this->hasMany(Schedina::class, 'customer_id');
    }

    public function struttura()
    {
        return $this->belongsTo(Struttura::class, 'struttura_id');
    }
}
