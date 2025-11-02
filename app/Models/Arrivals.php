<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arrivals extends Model
{
    protected $table = 'arrivals';
    protected $fillable = ['type','name','surname','sex','type_acommodation','arrive','departure','peoples','room','beds',
    'observations','country', 'city','region','province','city_born','date_born','doc_type','doc_num','start', 'end','released_type', 'released', 'created_at', 'updated_at'];
    protected $guarded = ['id'];
}
