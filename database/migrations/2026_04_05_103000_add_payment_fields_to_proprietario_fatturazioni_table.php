<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proprietario_fatturazioni', function (Blueprint $table) {
            if (!Schema::hasColumn('proprietario_fatturazioni', 'numero_fattura')) {
                $table->string('numero_fattura', 80)->nullable()->after('totale');
            }

            if (!Schema::hasColumn('proprietario_fatturazioni', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('numero_fattura');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proprietario_fatturazioni', function (Blueprint $table) {
            if (Schema::hasColumn('proprietario_fatturazioni', 'data_pagamento')) {
                $table->dropColumn('data_pagamento');
            }

            if (Schema::hasColumn('proprietario_fatturazioni', 'numero_fattura')) {
                $table->dropColumn('numero_fattura');
            }
        });
    }
};
