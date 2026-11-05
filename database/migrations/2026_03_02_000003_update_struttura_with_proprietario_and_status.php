<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (!Schema::hasColumn('struttura', 'proprietario_id')) {
                $table->unsignedBigInteger('proprietario_id')->nullable()->after('id');
                $table->index('proprietario_id');
            }
            if (!Schema::hasColumn('struttura', 'attiva')) {
                if (Schema::hasColumn('struttura', 'camere_reali_enabled')) {
                    $table->boolean('attiva')->default(true)->after('camere_reali_enabled');
                } else {
                    $table->boolean('attiva')->default(true);
                }
                $table->index('attiva');
            }
            if (!Schema::hasColumn('struttura', 'scadenza_servizio')) {
                $table->date('scadenza_servizio')->nullable()->after('attiva');
                $table->index('scadenza_servizio');
            }
            if (!Schema::hasColumn('struttura', 'piano')) {
                $table->string('piano', 30)->nullable()->after('scadenza_servizio');
            }
            if (!Schema::hasColumn('struttura', 'stato_pagamento')) {
                $table->string('stato_pagamento', 30)->nullable()->after('piano');
            }
        });

        Schema::table('struttura', function (Blueprint $table) {
            if (Schema::hasColumn('struttura', 'proprietario_id')) {
                $table->foreign('proprietario_id')->references('id')->on('proprietari')->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (Schema::hasColumn('struttura', 'proprietario_id')) {
                $table->dropForeign(['proprietario_id']);
                $table->dropIndex(['proprietario_id']);
                $table->dropColumn('proprietario_id');
            }
            if (Schema::hasColumn('struttura', 'stato_pagamento')) {
                $table->dropColumn('stato_pagamento');
            }
            if (Schema::hasColumn('struttura', 'piano')) {
                $table->dropColumn('piano');
            }
            if (Schema::hasColumn('struttura', 'scadenza_servizio')) {
                $table->dropIndex(['scadenza_servizio']);
                $table->dropColumn('scadenza_servizio');
            }
            if (Schema::hasColumn('struttura', 'attiva')) {
                $table->dropIndex(['attiva']);
                $table->dropColumn('attiva');
            }
        });
    }
};
