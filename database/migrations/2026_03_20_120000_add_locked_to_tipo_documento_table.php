<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tipo_documento', 'locked')) {
            Schema::table('tipo_documento', function (Blueprint $table) {
                $table->boolean('locked')->default(false)->after('descrizione');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tipo_documento', 'locked')) {
            Schema::table('tipo_documento', function (Blueprint $table) {
                $table->dropColumn('locked');
            });
        }
    }
};
