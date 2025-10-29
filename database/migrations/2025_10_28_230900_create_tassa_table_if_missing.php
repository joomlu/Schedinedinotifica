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
        if (! Schema::hasTable('tassa')) {
            Schema::create('tassa', function (Blueprint $table) {
                $table->id();
                $table->string('tassa_soggiorno')->nullable();
                $table->string('giorni_massimo')->nullable();
                $table->string('inizio')->nullable();
                $table->string('fine')->nullable();
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->string('max_age_children')->nullable();
                $table->string('min_age_adult')->nullable();
                $table->string('logo')->nullable();
                // Nota: tassa_essente_id verrà aggiunta da una migration separata
                // una volta che la tabella `tassa_essenti` e le sue dipendenze saranno stabili.
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tassa');
    }
};
