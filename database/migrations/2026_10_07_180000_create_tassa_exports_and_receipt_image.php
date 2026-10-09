<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tassa_di_soggiorno', function (Blueprint $table) {
            $table->string('ricevuta_immagine')->nullable();
        });
        Schema::create('tassa_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->date('data_da');
            $table->date('data_a');
            $table->unsignedInteger('versione');
            $table->unsignedBigInteger('precedente_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->longText('snapshot');
            $table->char('sha256', 64);
            $table->timestamp('created_at');
            $table->unique(['struttura_id', 'data_da', 'data_a', 'versione'], 'tassa_exports_periodo_versione');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tassa_exports');
        Schema::table('tassa_di_soggiorno', fn (Blueprint $table) => $table->dropColumn('ricevuta_immagine'));
    }
};
