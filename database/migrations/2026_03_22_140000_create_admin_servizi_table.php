<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_servizi')) {
            return;
        }

        Schema::create('admin_servizi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nome', 160);
            $table->string('tipo_costo', 30)->default('per_struttura');
            $table->decimal('importo', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->boolean('attivo')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'attivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_servizi');
    }
};
