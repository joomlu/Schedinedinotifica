<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comuni extends Model
{
    protected $table = 'comuni';

    protected $fillable = ['name', 'code', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
