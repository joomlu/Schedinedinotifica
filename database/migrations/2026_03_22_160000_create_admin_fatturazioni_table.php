<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_fatturazioni')) {
            return;
        }

        Schema::create('admin_fatturazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('numero', 50);
            $table->date('data_documento');
            $table->string('stato', 30)->default('proforma');
            $table->string('intestazione')->nullable();
            $table->string('partita_iva', 32)->nullable();
            $table->string('codice_fiscale', 32)->nullable();
            $table->string('pec', 180)->nullable();
            $table->string('indirizzo', 180)->nullable();
            $table->string('cap', 16)->nullable();
            $table->string('citta', 120)->nullable();
            $table->string('provincia', 8)->nullable();
            $table->decimal('totale', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['user_id', 'numero'], 'admin_fatturazioni_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_fatturazioni');
    }
};
