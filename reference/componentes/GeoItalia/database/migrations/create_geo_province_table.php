<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geo_province', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_regione_id')->constrained('geo_regioni')->cascadeOnDelete();
            $table->string('sigla', 3)->unique();
            $table->string('nome');
            $table->string('codice_provincia')->nullable();
            $table->timestamps();
            $table->index('geo_regione_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_province');
    }
};
