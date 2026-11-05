<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proprietari') || Schema::hasColumn('proprietari', 'cellulare')) {
            return;
        }

        Schema::table('proprietari', function (Blueprint $table) {
            $table->string('cellulare', 50)->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('proprietari') || !Schema::hasColumn('proprietari', 'cellulare')) {
            return;
        }

        Schema::table('proprietari', function (Blueprint $table) {
            $table->dropColumn('cellulare');
        });
    }
};
