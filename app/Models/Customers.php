<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class Customers extends Model
{
    use AppartieneAStruttura;

    // Usa la tabella rinominata "clienti"
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
}
