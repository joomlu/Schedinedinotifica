<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $scheda
 * @property string|null $type
 * @property string|null $name
 * @property string|null $surname
 * @property int|null $customer_id
 * @property string|null $sex
 * @property string|null $relationship
 * @property string|null $exent
 * @property string|null $arrive
 * @property string|null $departure
 * @property int|null $cant_people
 * @property string|null $room
 * @property string|null $beds
 * @property string|null $observation
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $oa_country
 * @property string|null $oa_city
 * @property string|null $oa_region
 * @property string|null $oa_prov
 * @property string|null $oa_city_nac
 * @property string|null $oa_date_nac
 * @property string|null $or_country
 * @property string|null $group
 * @property string|null $subgroup
 * @property string|null $subgroup1
 * @property string|null $type_housed
 * @property string|null $or_city
 * @property string|null $or_region
 * @property string|null $or_prov
 * @property string|null $or_cap
 * @property string|null $or_typeaway
 * @property string|null $or_address
 * @property string|null $or_num
 * @property string|null $or_doc
 * @property string|null $or_doctype
 * @property string|null $or_published_date
 * @property string|null $or_expire
 * @property string|null $or_published
 * @property string|null $or_published_country
 * @property int|null $is_arrive
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class Schedina extends Model
{
    protected $table = 'schedina';

    protected $fillable = ['scheda', 'type', 'name', 'surname', 'customer_id', 'sex', 'relationship', 'exent', 'arrive', 'departure', 'cant_people', 'room',
        'beds', 'observation', 'country', 'city', 'region', 'oa_country', 'oa_city', 'oa_region', 'oa_prov', 'oa_city_nac', 'oa_date_nac', 'or_country', 'or_city', 'or_region', 'or_prov', 'or_cap', 'or_typeaway', 'or_address', 'or_num', 'or_doc', 'or_doctype', 'or_published_date', 'or_expire', 'or_published', 'or_published_country', 'is_arrive', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
