<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $group
 * @property string|null $subgroup
 * @property string|null $subgroup1
 * @property string|null $sex
 * @property string|null $type_housed
 * @property string|null $type
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $province
 * @property string|null $cap
 * @property string|null $typeaway
 * @property string|null $address
 * @property string|null $number
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $cellphone
 * @property string|null $observation
 * @property string|null $country_reg
 * @property string|null $city_reg
 * @property string|null $prov_reg
 * @property string|null $ciudadania_reg
 * @property string|null $nac_reg
 * @property string|null $type_doc_reg
 * @property string|null $num_doc_reg
 * @property string|null $date_pub_reg
 * @property string|null $expire_reg
 * @property string|null $released_reg
 * @property string|null $observation_reg
 * @property string|null $azienda
 * @property string|null $cap_az
 * @property string|null $cf_az
 * @property string|null $pi_az
 * @property string|null $typeaway_az
 * @property string|null $address_az
 * @property string|null $number_az
 * @property string|null $email_az
 * @property string|null $phone_az
 * @property string|null $fax_az
 * @property string|null $cellphone_az
 * @property string|null $country_az
 * @property string|null $city_az
 * @property string|null $region_az
 * @property string|null $province_az
 * @property string|null $desc_az
 * @property string|null $nota
 * @property int|null $structure_id
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class Customers extends Model
{
    protected $table = 'customers';

    protected $fillable = ['group', 'subgroup', 'subgroup1', 'sex', 'type_housed', 'type', 'name', 'surname',
        'country', 'city', 'region', 'province', 'cap', 'typeaway', 'address', 'number', 'email', 'phone', 'fax',
        'cellphone', 'observation', 'country_reg', 'city_reg', 'prov_reg', 'ciudadania_reg', 'nac_reg',
        'type_doc_reg', 'num_doc_reg', 'date_pub_reg', 'expire_reg', 'released_reg', 'observation_reg',
        'azienda', 'cap_az', 'cf_az', 'pi_az', 'typeaway_az',
        'address_az', 'number_az', 'email_az', 'phone_az', 'fax_az',
        'cellphone_az', 'country_az', 'city_az', 'region_az', 'province_az', 'desc_az', 'nota',
        'structure_id', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
