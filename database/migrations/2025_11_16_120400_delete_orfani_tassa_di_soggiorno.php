<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Elimina tutti i record tassa_di_soggiorno con struttura_id non valido
        DB::table('tassa_di_soggiorno')->whereNotIn('struttura_id', function($query) {
            $query->select('id')->from('struttura');
        })->delete();
    }

    public function down()
    {
        // Non serve rollback
    }
};
