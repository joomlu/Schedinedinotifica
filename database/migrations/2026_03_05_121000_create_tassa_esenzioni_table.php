<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tassa_esenzioni')) {
            return;
        }

        Schema::create('tassa_esenzioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->string('codice', 50);
            $table->string('descrizione', 255);
            $table->boolean('attivo')->default(true);
            $table->boolean('richiede_nota')->default(false);
            $table->integer('ordine')->default(100);
            $table->timestamps();

            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
            $table->unique(['struttura_id', 'codice']);
            $table->index(['struttura_id', 'ordine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tassa_esenzioni');
    }
};
