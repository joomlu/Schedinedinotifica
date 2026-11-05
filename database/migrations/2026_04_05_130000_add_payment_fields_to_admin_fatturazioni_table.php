<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_fatturazioni')) {
            return;
        }

        Schema::table('admin_fatturazioni', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_fatturazioni', 'numero_fattura')) {
                $table->string('numero_fattura', 80)->nullable()->after('totale');
            }
            if (!Schema::hasColumn('admin_fatturazioni', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('numero_fattura');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_fatturazioni')) {
            return;
        }

        Schema::table('admin_fatturazioni', function (Blueprint $table) {
            foreach (['numero_fattura', 'data_pagamento'] as $column) {
                if (Schema::hasColumn('admin_fatturazioni', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
