<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenza_assegnazioni', function (Blueprint $table) {
            if (!Schema::hasColumn('licenza_assegnazioni', 'numero_licenza')) {
                $table->string('numero_licenza', 40)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('licenza_assegnazioni', function (Blueprint $table) {
            if (Schema::hasColumn('licenza_assegnazioni', 'numero_licenza')) {
                $table->dropUnique(['numero_licenza']);
                $table->dropColumn('numero_licenza');
            }
        });
    }
};
