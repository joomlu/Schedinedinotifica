<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_servizio_proprietario')) {
            return;
        }

        Schema::create('admin_servizio_proprietario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_servizio_id');
            $table->unsignedBigInteger('proprietario_id');
            $table->unsignedInteger('quantita')->default(1);
            $table->decimal('importo_override', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('admin_servizio_id')->references('id')->on('admin_servizi')->onDelete('cascade');
            $table->foreign('proprietario_id')->references('id')->on('proprietari')->onDelete('cascade');
            $table->unique(['admin_servizio_id', 'proprietario_id'], 'admin_servizio_proprietario_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_servizio_proprietario');
    }
};
