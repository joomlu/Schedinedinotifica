<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('access_importazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->string('connessione', 80);
            $table->uuid('richiesta_id');
            $table->string('codice_checkin', 40);
            $table->unsignedBigInteger('schedina_id')->nullable();
            $table->char('impronta', 64);
            $table->text('avvisi');
            $table->timestamps();
            $table->unique(['struttura_id', 'richiesta_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('access_importazioni'); }
};
