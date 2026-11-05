<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tassa_di_soggiorno', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->string('tassa_soggiorno', 200)->nullable();
            $table->string('giorni_massimo', 200)->nullable();
            $table->string('inizio', 100)->nullable();
            $table->string('fine', 100)->nullable();
            $table->string('max_age_children', 10)->nullable();
            $table->string('min_age_adult', 10)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tassa_di_soggiorno');
    }
};
