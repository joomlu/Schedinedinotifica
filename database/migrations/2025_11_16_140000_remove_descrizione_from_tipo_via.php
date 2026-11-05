<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tipo_via') && Schema::hasColumn('tipo_via', 'descrizione')) {
            Schema::table('tipo_via', function (Blueprint $table) {
                $table->dropColumn('descrizione');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tipo_via') && !Schema::hasColumn('tipo_via', 'descrizione')) {
            Schema::table('tipo_via', function (Blueprint $table) {
                $table->string('descrizione')->nullable()->after('nome');
            });
        }
    }
};
