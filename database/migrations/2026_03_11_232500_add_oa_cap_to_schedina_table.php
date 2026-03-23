<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedina') || Schema::hasColumn('schedina', 'oa_cap')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            $table->string('oa_cap', 20)->nullable()->after('oa_prov');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedina') || !Schema::hasColumn('schedina', 'oa_cap')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            $table->dropColumn('oa_cap');
        });
    }
};
