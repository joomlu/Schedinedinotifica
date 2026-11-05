<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rilasciato_da') && ! Schema::hasColumn('rilasciato_da', 'name')) {
            Schema::table('rilasciato_da', function (Blueprint $table) {
                $table->string('name', 191)->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rilasciato_da') && Schema::hasColumn('rilasciato_da', 'name')) {
            Schema::table('rilasciato_da', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
