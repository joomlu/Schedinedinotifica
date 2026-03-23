<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE schedina
            MODIFY exent VARCHAR(50) NOT NULL DEFAULT 'NO'
        ");

        DB::table('schedina')
            ->where('exent', '1')
            ->update(['exent' => 'SI']);

        DB::table('schedina')
            ->where('exent', '0')
            ->update(['exent' => 'NO']);
    }

    public function down(): void
    {
        DB::table('schedina')
            ->where('exent', 'SI')
            ->update(['exent' => '1']);

        DB::table('schedina')
            ->where('exent', 'NO')
            ->update(['exent' => '0']);

        DB::statement("
            ALTER TABLE schedina
            MODIFY exent TINYINT(1) NOT NULL DEFAULT 0
        ");
    }
};
