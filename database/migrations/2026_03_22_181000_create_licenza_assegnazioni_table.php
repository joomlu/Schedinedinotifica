<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenza_assegnazioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articolo_id')->constrained('licenza_articoli')->cascadeOnDelete();
            $table->foreignId('proprietario_id')->nullable()->constrained('proprietari')->nullOnDelete();
            $table->foreignId('struttura_id')->nullable()->constrained('struttura')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantita')->default(1);
            $table->decimal('prezzo', 10, 2)->default(0);
            $table->string('stato_pagamento', 40)->default('da_pagare')->index();
            $table->date('data_inizio')->nullable();
            $table->date('data_scadenza')->nullable()->index();
            $table->boolean('attiva')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenza_assegnazioni');
    }
};
