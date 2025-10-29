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
        if (! Schema::hasTable('stati')) {
            Schema::create('stati', function (Blueprint $table) {
                $table->id();
                $table->string('codice_questura', 20)->unique()->comment('Codice questura (es. 100000100)');
                $table->string('sigla', 10)->nullable()->comment('Sigla nazione (IT, FR, ES, etc.)');
                $table->string('denominazione', 255)->comment('Nome stato (Italia, Francia, etc.)');
                $table->string('cittadinanza', 255)->nullable()->comment('Cittadinanza (italiana, francese, etc.)');
                $table->string('codice_istat', 20)->nullable()->comment('Codice ISTAT');
                $table->date('data_fine_validita')->nullable()->comment('Data fine validità');
                $table->timestamps();

                $table->index('denominazione');
                $table->index('sigla');
                $table->index('codice_istat');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stati');
    }
};
