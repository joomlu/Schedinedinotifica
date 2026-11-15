<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->string('questura_modalita', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->dropColumn('questura_modalita');
        });
    }
};
