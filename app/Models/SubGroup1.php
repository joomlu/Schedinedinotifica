<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGroup1 extends Model
{
    protected $table = 'subgrupo1';
    protected $fillable = ['name', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
