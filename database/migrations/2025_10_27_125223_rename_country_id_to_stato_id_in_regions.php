<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            if (Schema::hasColumn('regions', 'country_id')) {
                // Drop old FK if exists
                try {
                    $table->dropForeign(['country_id']);
                } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                }

                $table->renameColumn('country_id', 'stato_id');
            }

            // Add FK to stati if not exists
            if (Schema::hasColumn('regions', 'stato_id') && Schema::hasTable('stati')) {
                try {
                    $table->foreign('stato_id')->references('id')->on('stati')->onDelete('restrict');
                } catch (\Exception $e) {
                    // Ignore if FK already exists
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            if (Schema::hasColumn('regions', 'stato_id')) {
                try {
                    $table->dropForeign(['stato_id']);
                } catch (\Exception $e) {
                    // Ignore
                }

                $table->renameColumn('stato_id', 'country_id');
            }
        });
    }
};
