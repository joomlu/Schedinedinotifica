<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (!Schema::hasColumn('struttura', 'numero_ricevuta_pagamento')) {
                $table->string('numero_ricevuta_pagamento', 120)->nullable()->after('stato_pagamento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (Schema::hasColumn('struttura', 'numero_ricevuta_pagamento')) {
                $table->dropColumn('numero_ricevuta_pagamento');
            }
        });
    }
};
