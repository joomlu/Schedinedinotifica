<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modifica la tabella tipo_via: aggiunge descrizione dopo nome.
     */
    public function up(): void
    {
        Schema::table('tipo_via', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_via', 'descrizione')) {
                $table->string('descrizione')->nullable()->after('nome');
            }
        });
    }

    /**
     * Ripristina la tabella tipo_via allo stato precedente.
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
