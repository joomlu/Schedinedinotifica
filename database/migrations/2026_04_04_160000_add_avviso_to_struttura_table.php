<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('struttura') || Schema::hasColumn('struttura', 'avviso')) {
            return;
        }

        Schema::table('struttura', function (Blueprint $table) {
            $table->string('avviso', 30)->nullable()->after('attiva');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('struttura') || !Schema::hasColumn('struttura', 'avviso')) {
            return;
        }

        Schema::table('struttura', function (Blueprint $table) {
            $table->dropColumn('avviso');
        });
    }
};
