<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('struttura_zone')) {
            return;
        }

        Schema::create('struttura_zone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id')->index();
            $table->unsignedBigInteger('geo_comune_id')->nullable()->index();
            $table->string('tipo', 20);
            $table->string('nome', 150);
            $table->boolean('attiva')->default(true);
            $table->unsignedInteger('ordine')->default(0);
            $table->timestamps();

            $table->unique(['struttura_id', 'geo_comune_id', 'tipo', 'nome'], 'struttura_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struttura_zone');
    }
};
