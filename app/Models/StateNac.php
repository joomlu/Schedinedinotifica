<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateNac extends Model
{
    protected $table = 'state_nac';

    protected $fillable = ['name', 'code', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
