<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedina') && ! Schema::hasColumn('schedina', 'or_internal')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->string('or_internal', 50)->nullable()->after('or_num');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina') && Schema::hasColumn('schedina', 'or_internal')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->dropColumn('or_internal');
            });
        }
    }
};
