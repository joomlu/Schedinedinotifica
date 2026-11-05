<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Aggiunge la colonna descrizione alla tabella tipo_via.
     */
    public function up(): void
    {
        Schema::table('tipo_via', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_via', 'nome')) {
                $table->string('descrizione')->nullable()->after('nome');
            } elseif (Schema::hasColumn('tipo_via', 'name')) {
                $table->string('descrizione')->nullable()->after('name');
            } else {
                $table->string('descrizione')->nullable();
            }
        });
    }

    /**
     * Rimuove la colonna descrizione dalla tabella tipo_via.
     */
    public function down(): void
    {
        Schema::table('tipo_via', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_via', 'descrizione')) {
                $table->dropColumn('descrizione');
            }
        });
    }
};
