<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('geo_comuni') || Schema::hasColumn('geo_comuni', 'logo')) {
            return;
        }

        Schema::table('geo_comuni', function (Blueprint $table) {
            $table->string('logo', 255)->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('geo_comuni') || !Schema::hasColumn('geo_comuni', 'logo')) {
            return;
        }

        Schema::table('geo_comuni', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
