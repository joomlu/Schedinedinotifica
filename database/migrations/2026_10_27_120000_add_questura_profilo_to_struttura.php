<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->string('questura_profilo', 20)->nullable();
            $table->unsignedInteger('questura_appartamento_id')->nullable();
        });
        Schema::table('questura_transmissions', fn (Blueprint $table) => $table->json('questura_context')->nullable());
    }

    public function down(): void
    {
        Schema::table('questura_transmissions', fn (Blueprint $table) => $table->dropColumn('questura_context'));
        Schema::table('struttura', fn (Blueprint $table) => $table->dropColumn(['questura_profilo', 'questura_appartamento_id']));
    }
};
