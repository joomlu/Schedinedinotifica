<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        $strutturaId = DB::table('struttura')->value('id');
        if ($strutturaId) {
            DB::table('tassa_di_soggiorno')->whereNull('struttura_id')->update(['struttura_id' => $strutturaId]);
        }
    }

    public function down()
    {
        // Non serve rollback
    }
};
