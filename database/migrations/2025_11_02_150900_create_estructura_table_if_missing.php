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
        if (! Schema::hasTable('estructura')) {
            Schema::create('estructura', function (Blueprint $table) {
                $table->id();

                // Informazioni base struttura
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('fax')->nullable();
                $table->string('city')->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('cp')->nullable();
                $table->string('web')->nullable();

                // Informazioni fiscali
                $table->string('cf')->nullable();
                $table->string('piva')->nullable();

                // Dati operativi
                $table->date('startact')->nullable();
                $table->date('closeact')->nullable();
                $table->string('typology')->nullable();
                $table->string('clasification')->nullable();
                $table->integer('numshedine')->nullable();
                $table->integer('roomdisp')->nullable();
                $table->integer('beddisp')->nullable();
                $table->string('ref')->nullable();
                $table->string('refpass')->nullable();
                $table->date('updatedbed')->nullable();

                // Media
                $table->string('logo')->nullable();

                $table->timestamps();

                // Indici utili
                $table->index(['city']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non droppiamo automaticamente per evitare perdita dati involontaria
        // Durante rollback manuale si può gestire il drop se necessario
    }
};
