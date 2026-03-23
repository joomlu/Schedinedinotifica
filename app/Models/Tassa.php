<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\AppartieneAStruttura;

class Tassa extends Model
{
    use AppartieneAStruttura;

	protected $table = 'tassa';
	protected $guarded = ['id'];
}
