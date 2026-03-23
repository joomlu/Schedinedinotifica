<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipo_alloggiato')) {
            Schema::create('tipo_alloggiato', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codice', 50)->unique();
                $table->string('descrizione', 191);
                $table->boolean('locked')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_alloggiato');
    }
};

