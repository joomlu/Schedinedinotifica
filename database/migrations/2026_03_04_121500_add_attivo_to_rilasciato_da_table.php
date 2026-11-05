<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rilasciato_da') && ! Schema::hasColumn('rilasciato_da', 'attivo')) {
            Schema::table('rilasciato_da', function (Blueprint $table) {
                $table->boolean('attivo')->default(true)->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rilasciato_da') && Schema::hasColumn('rilasciato_da', 'attivo')) {
            Schema::table('rilasciato_da', function (Blueprint $table) {
                $table->dropColumn('attivo');
            });
        }
    }
};
