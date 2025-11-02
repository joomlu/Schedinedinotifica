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
        // If correct table exists, ensure it has the required columns
        if (Schema::hasTable('released')) {
            if (!Schema::hasColumn('released', 'name')) {
                Schema::table('released', function (Blueprint $table) {
                    $table->string('name')->after('id');
                });
            }
            return;
        }

        // If the legacy/plural table exists, align it: add missing column then rename
        if (Schema::hasTable('releaseds')) {
            if (!Schema::hasColumn('releaseds', 'name')) {
                Schema::table('releaseds', function (Blueprint $table) {
                    $table->string('name')->nullable()->after('id');
                });
            }
            Schema::rename('releaseds', 'released');
            return;
        }

        // Otherwise create fresh
        Schema::create('released', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Try to reverse rename if we renamed earlier
        if (Schema::hasTable('released') && !Schema::hasTable('releaseds')) {
            // Best-effort: rename back to legacy name
            Schema::rename('released', 'releaseds');
        }
        // Note: We purposefully do not drop columns to avoid requiring doctrine/dbal
    }
};
