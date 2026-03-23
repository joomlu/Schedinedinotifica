<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('geo_comuni') || Schema::hasColumn('geo_comuni', 'logo_citta')) {
            return;
        }

        Schema::table('geo_comuni', function (Blueprint $table) {
            $table->string('logo_citta', 255)->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('geo_comuni') || !Schema::hasColumn('geo_comuni', 'logo_citta')) {
            return;
        }

        Schema::table('geo_comuni', function (Blueprint $table) {
            $table->dropColumn('logo_citta');
        });
    }
};
