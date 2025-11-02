<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Componenti extends Model
{
    protected $table = 'componenti';

    protected $fillable = ['name', 'schedina_id', 'customer_id', 'surname', 'sex', 'relationship', 'exent', 'province_nac', 'city_nac', 'date_nac',
        'country', 'regione', 'typeaway', 'address', 'number', 'cap', 'province', 'city', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
