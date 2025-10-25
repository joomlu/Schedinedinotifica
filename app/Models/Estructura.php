<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estructura extends Model
{
    protected $table = 'estructura';
    protected $fillable = ['name','phone','city','fax','address','email','cp','web','cf','piva','startact','typology',
    'closeact','clasification','numshedine','roomdisp','ref','beddisp','refpass','updatedbed', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
