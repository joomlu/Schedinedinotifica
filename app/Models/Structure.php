<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    protected $table = 'structure';

    protected $fillable = ['name', 'phone', 'city', 'fax', 'street', 'email', 'cp', 'web', 'cf', 'piva', 'startact', 'typology',
        'closeact', 'clasification', 'numshedine', 'roomdisp', 'ref', 'beddisp', 'refpass', 'updatedbed', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
