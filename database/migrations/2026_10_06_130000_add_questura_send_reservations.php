<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questura_transmissions', function (Blueprint $table) {
            $table->timestamp('identity_reserved_at')->nullable();
        });
        Schema::create('questura_send_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('struttura_id')->constrained('struttura')->restrictOnDelete();
            // Identità tecnica stabile: nessuna copia anagrafica né cascade dal PMS.
            $table->unsignedInteger('schedina_id');
            $table->string('transport_mode', 10);
            $table->foreignId('questura_transmission_id')->constrained('questura_transmissions')->restrictOnDelete();
            $table->timestamp('created_at');
            $table->unique(['struttura_id', 'schedina_id', 'transport_mode'], 'questura_source_transport_unique');
        });
    }

    public function down(): void
    {
        // Le riserve sono evidenza necessaria: nessun rollback distruttivo degli invii.
    }
};
