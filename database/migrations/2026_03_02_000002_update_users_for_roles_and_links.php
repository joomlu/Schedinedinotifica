<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ruolo')) {
                $table->string('ruolo', 30)->default('struttura_user')->after('password');
            }
            if (!Schema::hasColumn('users', 'struttura_id')) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('ruolo');
                $table->index('struttura_id');
            }
            if (!Schema::hasColumn('users', 'proprietario_id')) {
                $table->unsignedBigInteger('proprietario_id')->nullable()->after('struttura_id');
                $table->index('proprietario_id');
            }
        });

        // Rimuove eventuali FK esistenti con lo stesso nome per evitare duplicati
        foreach (['users_struttura_id_foreign', 'users_proprietario_id_foreign'] as $fk) {
            try {
                DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `$fk`");
            } catch (\Throwable $e) {
                // ignore if not exists
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'struttura_id')) {
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            }
            if (Schema::hasColumn('users', 'proprietario_id')) {
                $table->foreign('proprietario_id')->references('id')->on('proprietari')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'proprietario_id')) {
                $table->dropForeign(['proprietario_id']);
                $table->dropIndex(['proprietario_id']);
                $table->dropColumn('proprietario_id');
            }
            if (Schema::hasColumn('users', 'struttura_id')) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            }
            if (Schema::hasColumn('users', 'ruolo')) {
                $table->dropColumn('ruolo');
            }
        });
    }
};
