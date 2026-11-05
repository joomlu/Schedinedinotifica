<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_checkin_modelli', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->string('iniziale', 20)->nullable();
            $table->string('nome', 191);
            $table->string('oggetto', 255);
            $table->text('corpo');
            $table->unsignedInteger('versione')->default(1);
            $table->timestamps();
            $table->unique(['struttura_id', 'iniziale']);
        });
        Schema::create('web_checkin_comunicazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('richiesta_id');
            $table->unsignedBigInteger('modello_id')->nullable();
            $table->unsignedBigInteger('utente_id');
            $table->string('idempotenza', 64);
            $table->string('canale', 20);
            $table->string('stato', 30);
            // Contenuto finale cifrato con APP_KEY: il link non finisce nei log.
            $table->longText('contenuto');
            $table->timestamps();
            $table->unique(['richiesta_id', 'idempotenza']);
            $table->index(['struttura_id', 'richiesta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_checkin_comunicazioni');
        Schema::dropIfExists('web_checkin_modelli');
    }
};
