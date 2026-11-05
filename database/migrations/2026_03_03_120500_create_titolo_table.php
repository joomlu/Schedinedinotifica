<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('titolo')) {
            return;
        }

        Schema::create('titolo', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50);
            $table->string('descrizione', 150)->nullable();
            $table->boolean('attivo')->default(true);
            $table->timestamps();

            $table->unique('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titolo');
    }
};
