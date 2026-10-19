<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_checkin_richieste', function (Blueprint $table) {
            // Nessuna riclassificazione dei dati storici: ripartizione sconosciuta resta NULL.
            $table->unsignedTinyInteger('numero_adulti')->nullable();
            $table->unsignedTinyInteger('numero_bambini')->nullable();
            $table->text('messaggio_ospite')->nullable();
            $table->longText('compilazione_snapshot')->nullable();
            $table->longText('conversione_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('web_checkin_richieste', fn (Blueprint $table) => $table->dropColumn(['numero_adulti', 'numero_bambini', 'messaggio_ospite', 'compilazione_snapshot', 'conversione_snapshot']));
    }
};
