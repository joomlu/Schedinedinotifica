<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedina extends Model
{
    protected $table = 'schedina';

    protected $fillable = ['scheda', 'type', 'name', 'surname', 'customer_id', 'sex', 'relationship', 'exent', 'arrive', 'departure', 'cant_people', 'room',
        'beds', 'observation', 'oa_country', 'oa_city', 'oa_region', 'oa_prov', 'oa_city_nac', 'oa_date_nac', 'or_country', 'or_city', 'or_region', 'or_prov', 'or_cap', 'or_typeaway', 'or_address', 'or_num', 'or_doc', 'or_doctype', 'or_published_date', 'or_expire', 'or_published', 'or_published_country', 'is_arrive', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
