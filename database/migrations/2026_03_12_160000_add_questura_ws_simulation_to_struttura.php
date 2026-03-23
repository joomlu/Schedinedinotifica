<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('struttura') && !Schema::hasColumn('struttura', 'questura_ws_simulazione')) {
            Schema::table('struttura', function (Blueprint $table) {
                $table->boolean('questura_ws_simulazione')->default(false)->after('questura_puk');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('struttura') && Schema::hasColumn('struttura', 'questura_ws_simulazione')) {
            Schema::table('struttura', function (Blueprint $table) {
                $table->dropColumn('questura_ws_simulazione');
            });
        }
    }
};
