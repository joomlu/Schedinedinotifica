<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('struttura')) {
            return;
        }

        Schema::table('struttura', function (Blueprint $table) {
            if (!Schema::hasColumn('struttura', 'attiva')) {
                $table->boolean('attiva')->default(true)->after('questura_password');
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
    }

    public function down(): void
    {
        if (!Schema::hasTable('struttura')) {
            return;
        }

        Schema::table('struttura', function (Blueprint $table) {
            if (Schema::hasColumn('struttura', 'attiva')) {
                $table->dropIndex(['attiva']);
            }
            if (Schema::hasColumn('struttura', 'scadenza_servizio')) {
                $table->dropIndex(['scadenza_servizio']);
            }
            $columns = array_values(array_filter([
                Schema::hasColumn('struttura', 'attiva') ? 'attiva' : null,
                Schema::hasColumn('struttura', 'scadenza_servizio') ? 'scadenza_servizio' : null,
                Schema::hasColumn('struttura', 'piano') ? 'piano' : null,
                Schema::hasColumn('struttura', 'stato_pagamento') ? 'stato_pagamento' : null,
            ]));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
