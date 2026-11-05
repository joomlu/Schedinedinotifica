<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_fatturazioni')) {
            Schema::table('admin_fatturazioni', function (Blueprint $table) {
                if (!Schema::hasColumn('admin_fatturazioni', 'imponibile')) {
                    $table->decimal('imponibile', 12, 2)->default(0)->after('provincia');
                }
                if (!Schema::hasColumn('admin_fatturazioni', 'totale_sconto')) {
                    $table->decimal('totale_sconto', 12, 2)->default(0)->after('imponibile');
                }
                if (!Schema::hasColumn('admin_fatturazioni', 'totale_iva')) {
                    $table->decimal('totale_iva', 12, 2)->default(0)->after('totale_sconto');
                }
            });
        }

        if (Schema::hasTable('admin_fatturazione_righe')) {
            Schema::table('admin_fatturazione_righe', function (Blueprint $table) {
                if (!Schema::hasColumn('admin_fatturazione_righe', 'sconto_tipo')) {
                    $table->string('sconto_tipo', 20)->default('percentuale')->after('prezzo_unitario');
                }
                if (!Schema::hasColumn('admin_fatturazione_righe', 'sconto_valore')) {
                    $table->decimal('sconto_valore', 12, 2)->default(0)->after('sconto_tipo');
                }
                if (!Schema::hasColumn('admin_fatturazione_righe', 'imponibile')) {
                    $table->decimal('imponibile', 12, 2)->default(0)->after('sconto_valore');
                }
                if (!Schema::hasColumn('admin_fatturazione_righe', 'aliquota_iva')) {
                    $table->decimal('aliquota_iva', 5, 2)->default(22)->after('imponibile');
                }
                if (!Schema::hasColumn('admin_fatturazione_righe', 'totale_iva')) {
                    $table->decimal('totale_iva', 12, 2)->default(0)->after('aliquota_iva');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_fatturazione_righe')) {
            Schema::table('admin_fatturazione_righe', function (Blueprint $table) {
                foreach (['sconto_tipo', 'sconto_valore', 'imponibile', 'aliquota_iva', 'totale_iva'] as $column) {
                    if (Schema::hasColumn('admin_fatturazione_righe', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('admin_fatturazioni')) {
            Schema::table('admin_fatturazioni', function (Blueprint $table) {
                foreach (['imponibile', 'totale_sconto', 'totale_iva'] as $column) {
                    if (Schema::hasColumn('admin_fatturazioni', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
