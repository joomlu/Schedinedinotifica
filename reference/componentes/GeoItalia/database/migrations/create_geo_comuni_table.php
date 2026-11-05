<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geo_comuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_provincia_id')->constrained('geo_province')->cascadeOnDelete();
            $table->string('codice_istat')->unique();
            $table->string('nome');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
            $table->index('geo_provincia_id');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_comuni');
    }
};
