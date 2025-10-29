<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tassa_essenti')) {
            Schema::create('tassa_essenti', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comuni_tassa_esenti_id');
                $table->string('cod_esenz', 50);
                $table->string('nome', 100);
                $table->text('descrizione')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();

                // Índices
                $table->index('comuni_tassa_esenti_id');
                $table->index('cod_esenz');
                $table->unique(['comuni_tassa_esenti_id', 'cod_esenz'], 'uk_comuni_cod_esenz');
            });
        }

        // Add FK to comuni only if table exists (to avoid migration order issues)
        if (Schema::hasTable('tassa_essenti') && Schema::hasTable('comuni')) {
            Schema::table('tassa_essenti', function (Blueprint $table) {
                $table->foreign('comuni_tassa_esenti_id')
                    ->references('id')
                    ->on('comuni')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tassa_essenti');
    }
};
