<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_fatturazione_righe')) {
            return;
        }

        Schema::create('admin_fatturazione_righe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_fatturazione_id');
            $table->unsignedBigInteger('proprietario_id')->nullable();
            $table->unsignedBigInteger('admin_servizio_id')->nullable();
            $table->string('descrizione');
            $table->unsignedInteger('quantita')->default(1);
            $table->decimal('prezzo_unitario', 12, 2)->default(0);
            $table->decimal('totale', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('admin_fatturazione_id')->references('id')->on('admin_fatturazioni')->onDelete('cascade');
            $table->foreign('proprietario_id')->references('id')->on('proprietari')->nullOnDelete();
            $table->foreign('admin_servizio_id')->references('id')->on('admin_servizi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_fatturazione_righe');
    }
};
