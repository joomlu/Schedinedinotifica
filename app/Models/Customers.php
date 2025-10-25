<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $table = 'customers';
    protected $fillable = ['group','subgroup', 'subgroup1', 'sex','type_housed','type','name', 'surname', 
    'country','city', 'region','province','cap','typeaway','address','number','email','phone','fax',
    'cellphone', 'observation', 'country_reg', 'city_reg', 'prov_reg', 'ciudadania_reg', 'nac_reg', 
    'type_doc_reg', 'num_doc_reg', 'date_pub_reg', 'expire_reg', 'released_reg', 'observation_reg',
     'azienda', 'cap_az', 'cf_az', 'pi_az', 'typeaway_az', 
     'address_az', 'number_az', 'email_az', 'phone_az', 'fax_az', 
     'cellphone_az', 'country_az', 'city_az', 'region_az', 'province_az', 'desc_az', 'nota',
     'created_at', 'updated_at'];
    protected $guarded = ['id']; 
}
