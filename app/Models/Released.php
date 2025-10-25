<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Released extends Model
{
    protected $table = 'released';
    protected $fillable = ['name', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
