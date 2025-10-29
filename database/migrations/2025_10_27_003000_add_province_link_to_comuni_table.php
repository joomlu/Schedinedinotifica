<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comuni', function (Blueprint $table) {
            if (! Schema::hasColumn('comuni', 'sigla_provincia')) {
                $table->string('sigla_provincia', 10)->nullable()->after('name');
                $table->index('sigla_provincia');
            }
            if (! Schema::hasColumn('comuni', 'province_id')) {
                // provinces.id is unsignedBigInteger; we match that type
                $table->unsignedBigInteger('province_id')->nullable()->after('sigla_provincia');
                $table->index('province_id');
                $table->foreign('province_id')->references('id')->on('provinces')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comuni', function (Blueprint $table) {
            if (Schema::hasColumn('comuni', 'province_id')) {
                $table->dropForeign(['province_id']);
                $table->dropIndex(['province_id']);
                $table->dropColumn('province_id');
            }
            if (Schema::hasColumn('comuni', 'sigla_provincia')) {
                $table->dropIndex(['sigla_provincia']);
                $table->dropColumn('sigla_provincia');
            }
        });
    }
};
