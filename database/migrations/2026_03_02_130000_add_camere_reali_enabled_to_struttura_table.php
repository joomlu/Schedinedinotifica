<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->boolean('camere_reali_enabled')->default(false)->after('questura_password');
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->dropColumn('camere_reali_enabled');
        });
    }
};
