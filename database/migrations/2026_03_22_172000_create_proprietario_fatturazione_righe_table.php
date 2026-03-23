<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proprietario_fatturazione_righe')) {
            return;
        }

        Schema::create('proprietario_fatturazione_righe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proprietario_fatturazione_id');
            $table->unsignedBigInteger('struttura_id')->nullable();
            $table->unsignedBigInteger('admin_servizio_id')->nullable();
            $table->string('descrizione', 255);
            $table->unsignedInteger('quantita')->default(1);
            $table->decimal('prezzo_unitario', 12, 2)->default(0);
            $table->string('sconto_tipo', 20)->default('percentuale');
            $table->decimal('sconto_valore', 12, 2)->default(0);
            $table->decimal('imponibile', 12, 2)->default(0);
            $table->decimal('aliquota_iva', 6, 2)->default(22);
            $table->decimal('totale_iva', 12, 2)->default(0);
            $table->decimal('totale', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('proprietario_fatturazione_id', 'prop_fatt_righe_fatt_fk')
                ->references('id')
                ->on('proprietario_fatturazioni')
                ->onDelete('cascade');
            $table->foreign('struttura_id')->references('id')->on('struttura')->nullOnDelete();
            $table->foreign('admin_servizio_id')->references('id')->on('admin_servizi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietario_fatturazione_righe');
    }
};
