<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'ruolo') || Schema::hasColumn('users', 'struttura_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('ruolo', 30)->default('struttura_user')->after('password');
            $table->unsignedBigInteger('struttura_id')->nullable()->after('ruolo');
            $table->index('ruolo');
            $table->index('struttura_id');
            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'struttura_id')) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id']);
            }
            if (Schema::hasColumn('users', 'ruolo')) {
                $table->dropIndex(['ruolo']);
            }
            $table->dropColumn(array_values(array_filter(['ruolo', 'struttura_id'], fn ($col) => Schema::hasColumn('users', $col))));
        });
    }
};
