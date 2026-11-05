<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gruppi')) {
            return;
        }

        if (! Schema::hasColumn('gruppi', 'parent_id')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('gruppi')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('gruppi', 'livello')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->tinyInteger('livello')->nullable()->after('tipo');
            });
        }

        DB::table('gruppi')
            ->whereNull('livello')
            ->where(function ($q) {
                $q->whereNull('tipo')->orWhere('tipo', 'Gruppo');
            })
            ->update([
                'livello' => 1,
                'tipo' => 'Gruppi I',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('gruppi')) {
            return;
        }

        if (Schema::hasColumn('gruppi', 'parent_id')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }

        if (Schema::hasColumn('gruppi', 'livello')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->dropColumn('livello');
            });
        }
    }
};
