<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenza_articoli', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('licenza_articoli')->nullOnDelete();
            $table->string('nome');
            $table->string('codice', 80)->nullable()->index();
            $table->string('accesso_key', 120)->nullable();
            $table->text('descrizione')->nullable();
            $table->decimal('prezzo_base', 10, 2)->default(0);
            $table->boolean('attivo')->default(true);
            $table->unsignedInteger('ordine')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenza_articoli');
    }
};
