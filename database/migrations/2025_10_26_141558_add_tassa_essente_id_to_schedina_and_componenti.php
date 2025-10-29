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
        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (! Schema::hasColumn('schedina', 'tassa_essente_id')) {
                    $table->unsignedBigInteger('tassa_essente_id')->nullable()->after('id');
                    $table->index('tassa_essente_id');
                }
            });
            if (Schema::hasTable('tassa_essenti')) {
                Schema::table('schedina', function (Blueprint $table) {
                    try {
                        $table->foreign('tassa_essente_id')
                            ->references('id')
                            ->on('tassa_essenti')
                            ->onDelete('set null');
                    } catch (Throwable $e) {
                        // ignore if FK already exists
                    }
                });
            }
        }

        if (Schema::hasTable('componenti')) {
            Schema::table('componenti', function (Blueprint $table) {
                if (! Schema::hasColumn('componenti', 'tassa_essente_id')) {
                    $table->unsignedBigInteger('tassa_essente_id')->nullable()->after('id');
                    $table->index('tassa_essente_id');
                }
            });
            if (Schema::hasTable('tassa_essenti')) {
                Schema::table('componenti', function (Blueprint $table) {
                    try {
                        $table->foreign('tassa_essente_id')
                            ->references('id')
                            ->on('tassa_essenti')
                            ->onDelete('set null');
                    } catch (Throwable $e) {
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
        Schema::table('schedina', function (Blueprint $table) {
            if (Schema::hasColumn('schedina', 'tassa_essente_id')) {
                $table->dropForeign(['tassa_essente_id']);
                $table->dropIndex(['tassa_essente_id']);
                $table->dropColumn('tassa_essente_id');
            }
        });

        Schema::table('componenti', function (Blueprint $table) {
            if (Schema::hasColumn('componenti', 'tassa_essente_id')) {
                $table->dropForeign(['tassa_essente_id']);
                $table->dropIndex(['tassa_essente_id']);
                $table->dropColumn('tassa_essente_id');
            }
        });
    }
};
