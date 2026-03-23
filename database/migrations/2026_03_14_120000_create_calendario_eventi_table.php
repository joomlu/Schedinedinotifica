<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendario_eventi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('struttura_id')->constrained('struttura')->cascadeOnDelete();
            $table->string('tipo', 30)->default('manuale');
            $table->string('titolo', 180);
            $table->text('descrizione')->nullable();
            $table->date('data_evento');
            $table->time('ora_evento')->nullable();
            $table->string('priorita', 20)->default('normale');
            $table->string('stato', 20)->default('da_fare');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('visto_at')->nullable();
            $table->timestamp('completato_at')->nullable();
            $table->timestamp('chiuso_at')->nullable();
            $table->timestamps();

            $table->index(['struttura_id', 'data_evento']);
            $table->index(['struttura_id', 'stato']);
            $table->index(['struttura_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_eventi');
    }
};
