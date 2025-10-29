<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
class TypeDoc extends Model
{
    protected $table = 'typedoc';

    protected $fillable = ['name', 'code', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
