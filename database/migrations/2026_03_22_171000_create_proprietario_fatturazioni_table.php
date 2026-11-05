<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proprietario_fatturazioni')) {
            return;
        }

        Schema::create('proprietario_fatturazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proprietario_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('numero', 50);
            $table->date('data_documento')->nullable();
            $table->string('stato', 30)->default('proforma');
            $table->string('intestazione', 180)->nullable();
            $table->string('partita_iva', 32)->nullable();
            $table->string('codice_fiscale', 32)->nullable();
            $table->string('pec', 180)->nullable();
            $table->string('indirizzo', 255)->nullable();
            $table->string('cap', 16)->nullable();
            $table->string('citta', 120)->nullable();
            $table->string('provincia', 16)->nullable();
            $table->decimal('imponibile', 12, 2)->default(0);
            $table->decimal('totale_sconto', 12, 2)->default(0);
            $table->decimal('totale_iva', 12, 2)->default(0);
            $table->decimal('totale', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('proprietario_id')->references('id')->on('proprietari')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['proprietario_id', 'numero'], 'proprietario_fatturazioni_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietario_fatturazioni');
    }
};
