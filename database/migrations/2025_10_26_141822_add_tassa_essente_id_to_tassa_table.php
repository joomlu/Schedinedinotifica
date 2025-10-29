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
        if (Schema::hasTable('tassa')) {
            // Add column if missing
            if (! Schema::hasColumn('tassa', 'tassa_essente_id')) {
                Schema::table('tassa', function (Blueprint $table) {
                    $table->unsignedBigInteger('tassa_essente_id')->nullable()->after('logo');
                    $table->index('tassa_essente_id');
                });
            }

            // Add FK only if referenced table exists
            if (Schema::hasTable('tassa_essenti')) {
                Schema::table('tassa', function (Blueprint $table) {
                    try {
                        $table->foreign('tassa_essente_id')
                            ->references('id')
                            ->on('tassa_essenti')
                            ->onDelete('set null');
                    } catch (\Throwable $e) {
                        // ignore if FK already exists
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tassa') && Schema::hasColumn('tassa', 'tassa_essente_id')) {
            Schema::table('tassa', function (Blueprint $table) {
                // Drop FK if exists
                try {
                    $table->dropForeign(['tassa_essente_id']);
                } catch (\Throwable $e) {
                    // ignore if FK not present
                }
                $table->dropIndex(['tassa_essente_id']);
                $table->dropColumn('tassa_essente_id');
            });
        }
    }
};
