<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_checkin_richieste', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('schedina_id')->nullable();
            $table->string('codice', 30)->unique();
            $table->string('numero_prenotazione', 100);
            $table->string('email', 191);
            $table->string('whatsapp', 50)->nullable();
            $table->string('nome_referente', 150);
            $table->date('arrivo');
            $table->date('partenza');
            $table->unsignedInteger('quantita_persone')->default(1);
            $table->text('note')->nullable();
            $table->string('token', 120)->unique();
            $table->string('stato', 30)->default('da_inviare');
            $table->timestamp('ultimo_accesso_at')->nullable();
            $table->timestamp('compilato_at')->nullable();
            $table->timestamp('convertito_at')->nullable();
            $table->timestamps();

            $table->index(['struttura_id', 'stato']);
            $table->index(['struttura_id', 'numero_prenotazione']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_checkin_richieste');
    }
};
