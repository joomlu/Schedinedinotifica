<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'username')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_username_unique');
            });
        } catch (Throwable $e) {
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_username_unique');
            } catch (Throwable $e) {
            }
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('username');
            });
        } catch (Throwable $e) {
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'username')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['username']);
            });
        } catch (Throwable $e) {
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        } catch (Throwable $e) {
        }
    }
};
