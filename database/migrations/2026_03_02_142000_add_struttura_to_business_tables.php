<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schedina
        if (Schema::hasTable('schedina') && !Schema::hasColumn('schedina', 'struttura_id')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                $table->index('struttura_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            });
        }

        // Clienti
        if (Schema::hasTable('clienti') && !Schema::hasColumn('clienti', 'struttura_id')) {
            Schema::table('clienti', function (Blueprint $table) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                $table->index('struttura_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            });
        }

        // Componenti
        if (Schema::hasTable('componenti') && !Schema::hasColumn('componenti', 'struttura_id')) {
            Schema::table('componenti', function (Blueprint $table) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                $table->index('struttura_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            });
        }

        // Tassa (se esiste)
        if (Schema::hasTable('tassa') && !Schema::hasColumn('tassa', 'struttura_id')) {
            Schema::table('tassa', function (Blueprint $table) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                $table->index('struttura_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            });
        }

        // Schedina camere
        if (Schema::hasTable('schedina_camere') && !Schema::hasColumn('schedina_camere', 'struttura_id')) {
            Schema::table('schedina_camere', function (Blueprint $table) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                $table->index('struttura_id');
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina_camere') && Schema::hasColumn('schedina_camere', 'struttura_id')) {
            Schema::table('schedina_camere', function (Blueprint $table) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            });
        }

        if (Schema::hasTable('tassa') && Schema::hasColumn('tassa', 'struttura_id')) {
            Schema::table('tassa', function (Blueprint $table) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            });
        }

        if (Schema::hasTable('componenti') && Schema::hasColumn('componenti', 'struttura_id')) {
            Schema::table('componenti', function (Blueprint $table) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            });
        }

        if (Schema::hasTable('clienti') && Schema::hasColumn('clienti', 'struttura_id')) {
            Schema::table('clienti', function (Blueprint $table) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            });
        }

        if (Schema::hasTable('schedina') && Schema::hasColumn('schedina', 'struttura_id')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
                $table->dropColumn('struttura_id');
            });
        }
    }
};
