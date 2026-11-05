<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'schedina',
            'clienti',
            'componenti',
            'tassa',
            'tassa_di_soggiorno',
            'schedina_camere',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'struttura_id')) {
                    $table->unsignedBigInteger('struttura_id')->nullable()->after('id');
                    $table->index('struttura_id');
                }
            });
        }

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            // Rimuove eventuali FK duplicate prima di aggiungere
            try {
                \DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$tableName}_struttura_id_foreign`");
            } catch (\Throwable $e) {
                // ignore
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'struttura_id')) {
                    $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('restrict');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'schedina',
            'clienti',
            'componenti',
            'tassa',
            'tassa_di_soggiorno',
            'schedina_camere',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'struttura_id')) {
                    try {
                        $table->dropForeign(['struttura_id']);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            });
        }

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'struttura_id')) {
                    try {
                        $table->dropIndex([$tableName . '_struttura_id_index']);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    $table->dropColumn('struttura_id');
                }
            });
        }
    }
};
