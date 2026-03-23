<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geo_nazioni', function (Blueprint $table) {
            $table->id();
            $table->string('codice_iso2', 2)->unique();
            $table->string('nome');
            $table->string('cittadinanza')->nullable();
            $table->boolean('is_italia')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_nazioni');
    }
};
