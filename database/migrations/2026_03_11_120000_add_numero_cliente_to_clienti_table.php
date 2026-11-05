<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            if (!Schema::hasColumn('clienti', 'numero_cliente')) {
                $table->string('numero_cliente', 20)->nullable()->after('id');
            }
        });

        Schema::table('clienti', function (Blueprint $table) {
            // Codice univoco per struttura (evita collisioni tra strutture diverse).
            $table->unique(['struttura_id', 'numero_cliente'], 'clienti_struttura_numero_cliente_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            $table->dropUnique('clienti_struttura_numero_cliente_unique');
            if (Schema::hasColumn('clienti', 'numero_cliente')) {
                $table->dropColumn('numero_cliente');
            }
        });
    }
};
