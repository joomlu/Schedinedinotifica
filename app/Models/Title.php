<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Title extends Model
{
    protected $table = 'title';

    protected $fillable = ['name', 'created_at', 'updated_at'];

    protected $guarded = ['id'];
}
