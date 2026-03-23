<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proprietari')) {
            return;
        }

        Schema::create('proprietari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('nome', 120);
            $table->string('email', 120)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->boolean('attivo')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('restrict');
            $table->index(['admin_id', 'attivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietari');
    }
};
