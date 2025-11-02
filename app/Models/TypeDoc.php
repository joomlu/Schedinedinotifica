<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDoc extends Model
{
    protected $table = 'typedoc';
    protected $fillable = ['name', 'code', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
