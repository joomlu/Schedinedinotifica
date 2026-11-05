<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedina') && !Schema::hasColumn('schedina', 'istat_non_turista')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->boolean('istat_non_turista')->default(false)->after('istat_professione');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina') && Schema::hasColumn('schedina', 'istat_non_turista')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->dropColumn('istat_non_turista');
            });
        }
    }
};
