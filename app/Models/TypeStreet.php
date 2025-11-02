<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeStreet extends Model
{
    protected $table = 'typestreet';
    protected $fillable = ['name', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
