<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (!Schema::hasColumn('struttura', 'messaggio_offline')) {
                $table->text('messaggio_offline')->nullable()->after('avviso');
            }

            if (!Schema::hasColumn('struttura', 'messaggio_avviso')) {
                $table->text('messaggio_avviso')->nullable()->after('messaggio_offline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            foreach (['messaggio_avviso', 'messaggio_offline'] as $column) {
                if (Schema::hasColumn('struttura', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
