<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class SubGroup extends Model
{
    protected $table = 'subgrupo';

    protected $fillable = ['name', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
