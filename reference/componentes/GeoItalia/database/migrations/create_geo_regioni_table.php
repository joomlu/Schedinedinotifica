<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geo_regioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_nazione_id')->constrained('geo_nazioni')->cascadeOnDelete();
            $table->string('codice_regione');
            $table->string('nome');
            $table->timestamps();
            $table->unique(['geo_nazione_id', 'codice_regione']);
            $table->index('geo_nazione_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_regioni');
    }
};
