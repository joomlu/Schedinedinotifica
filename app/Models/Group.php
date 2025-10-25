<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'grupo';

    protected $fillable = ['name', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
