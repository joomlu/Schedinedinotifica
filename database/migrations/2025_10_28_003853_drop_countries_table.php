<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('countries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate basic structure if rollback is needed
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('sigla_nazione', 10)->unique();
            $table->string('denominazione');
            $table->timestamps();
        });
    }
};
