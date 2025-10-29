<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('comune_logos')) {
            Schema::create('comune_logos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comune_id')->unique();
                $table->string('logo_filename');
                $table->timestamps();

                $table->index('comune_id');
            });

            // Aggiungi FK solo se `comuni` esiste
            if (Schema::hasTable('comuni')) {
                Schema::table('comune_logos', function (Blueprint $table) {
                    try {
                        $table->foreign('comune_id')
                            ->references('id')
                            ->on('comuni')
                            ->onDelete('cascade');
                    } catch (\Throwable $e) {
                    }
                });
            }
        }

        // Migrazione dati: inserisci il logo solo se il comune 797 esiste realmente
        // Nota: l'inserimento del logo di esempio è stato spostato in un seeder dedicato (ComuneLogoSeeder)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comune_logos');
    }
};
