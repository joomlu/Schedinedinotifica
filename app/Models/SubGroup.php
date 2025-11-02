<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGroup extends Model
{
    protected $table = 'subgrupo';
    protected $fillable = ['name', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
