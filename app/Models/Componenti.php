<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $schedina_id
 * @property int|null $customer_id
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $sex
 * @property string|null $relationship
 * @property string|null $exent
 * @property string|null $province_nac
 * @property string|null $city_nac
 * @property string|null $date_nac
 * @property string|null $country
 * @property string|null $regione
 * @property string|null $typeaway
 * @property string|null $address
 * @property string|null $number
 * @property string|null $cap
 * @property string|null $province
 * @property string|null $city
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class Componenti extends Model
{
    protected $table = 'componenti';

    protected $fillable = ['name', 'schedina_id', 'customer_id', 'surname', 'sex', 'relationship', 'exent', 'province_nac', 'city_nac', 'date_nac',
        'country', 'regione', 'typeaway', 'address', 'number', 'cap', 'province', 'city', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
