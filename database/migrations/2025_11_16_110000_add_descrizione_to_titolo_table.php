<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titolo')) {
            return; // table may not exist on fresh installs
        }

        Schema::table('titolo', function (Blueprint $table) {
            $table->string('descrizione')->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('titolo')) {
            return;
        }

        Schema::table('titolo', function (Blueprint $table) {
            $table->dropColumn('descrizione');
        });
    }
};
