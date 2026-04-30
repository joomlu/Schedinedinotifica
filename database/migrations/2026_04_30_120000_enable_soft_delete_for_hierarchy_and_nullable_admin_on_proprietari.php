<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'proprietari', 'struttura'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        if (Schema::hasTable('proprietari') && Schema::hasColumn('proprietari', 'admin_id')) {
            try {
                DB::statement('ALTER TABLE proprietari DROP FOREIGN KEY proprietari_admin_id_foreign');
            } catch (\Throwable $e) {
                // vincolo già assente o rinominato
            }

            DB::statement('ALTER TABLE proprietari MODIFY admin_id BIGINT UNSIGNED NULL');

            try {
                DB::statement('ALTER TABLE proprietari ADD CONSTRAINT proprietari_admin_id_foreign FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL');
            } catch (\Throwable $e) {
                // vincolo già ricreato
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('proprietari') && Schema::hasColumn('proprietari', 'admin_id')) {
            try {
                DB::statement('ALTER TABLE proprietari DROP FOREIGN KEY proprietari_admin_id_foreign');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        foreach (['users', 'proprietari', 'struttura'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
