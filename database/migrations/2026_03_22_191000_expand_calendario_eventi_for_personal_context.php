<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('calendario_eventi')) {
            return;
        }

        Schema::table('calendario_eventi', function (Blueprint $table) {
            if (!Schema::hasColumn('calendario_eventi', 'ambito')) {
                $table->string('ambito', 20)->default('struttura')->after('struttura_id');
            }

            if (!Schema::hasColumn('calendario_eventi', 'user_scope_id')) {
                $table->foreignId('user_scope_id')->nullable()->after('ambito')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('calendario_eventi', function (Blueprint $table) {
            $table->foreignId('struttura_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('calendario_eventi')) {
            return;
        }

        Schema::table('calendario_eventi', function (Blueprint $table) {
            if (Schema::hasColumn('calendario_eventi', 'user_scope_id')) {
                $table->dropConstrainedForeignId('user_scope_id');
            }

            if (Schema::hasColumn('calendario_eventi', 'ambito')) {
                $table->dropColumn('ambito');
            }
        });
    }
};
