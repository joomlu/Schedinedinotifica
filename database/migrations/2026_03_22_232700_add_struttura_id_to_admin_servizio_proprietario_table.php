<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_servizio_proprietario')) {
            return;
        }

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_servizio_proprietario', 'struttura_id')) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('proprietario_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->nullOnDelete();
            }
        });

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            $table->index('admin_servizio_id', 'asp_admin_servizio_id_idx');
            $table->index('proprietario_id', 'asp_proprietario_id_idx');
            $table->index('struttura_id', 'asp_struttura_id_idx');
        });

        try {
            DB::statement('ALTER TABLE admin_servizio_proprietario DROP INDEX admin_servizio_proprietario_unique');
        } catch (\Throwable $e) {
            // Index may already be gone or absent in some local states.
        }

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            $table->unique(['admin_servizio_id', 'proprietario_id', 'struttura_id'], 'admin_servizio_proprietario_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_servizio_proprietario')) {
            return;
        }

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            try {
                $table->dropUnique('admin_servizio_proprietario_unique');
            } catch (\Throwable $e) {
                // noop
            }
        });

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            $table->unique(['admin_servizio_id', 'proprietario_id'], 'admin_servizio_proprietario_unique');
            try {
                $table->dropIndex('asp_admin_servizio_id_idx');
                $table->dropIndex('asp_proprietario_id_idx');
                $table->dropIndex('asp_struttura_id_idx');
            } catch (\Throwable $e) {
                // noop
            }
        });

        Schema::table('admin_servizio_proprietario', function (Blueprint $table) {
            if (Schema::hasColumn('admin_servizio_proprietario', 'struttura_id')) {
                $table->dropForeign(['struttura_id']);
                $table->dropColumn('struttura_id');
            }
        });
    }
};
