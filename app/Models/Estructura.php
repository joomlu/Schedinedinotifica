<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $city
 * @property string|null $logo
 * @property string|null $fax
 * @property string|null $address
 * @property string|null $email
 * @property string|null $cp
 * @property string|null $web
 * @property string|null $cf
 * @property string|null $piva
 * @property string|null $startact
 * @property string|null $typology
 * @property string|null $closeact
 * @property string|null $clasification
 * @property string|null $numshedine
 * @property string|null $roomdisp
 * @property string|null $ref
 * @property string|null $beddisp
 * @property string|null $refpass
 * @property string|null $updatedbed
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class Estructura extends Model
{
    protected $table = 'estructura';

    protected $fillable = ['name', 'phone', 'city', 'logo', 'fax', 'address', 'email', 'cp', 'web', 'cf', 'piva', 'startact', 'typology',
        'closeact', 'clasification', 'numshedine', 'roomdisp', 'ref', 'beddisp', 'refpass', 'updatedbed', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
