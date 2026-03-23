<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('componenti')) {
            return;
        }

        Schema::table('componenti', function (Blueprint $table) {
            if (!Schema::hasColumn('componenti', 'country_nac')) {
                $table->string('country_nac', 150)->nullable()->after('date_nac');
            }
            if (!Schema::hasColumn('componenti', 'regione_nac')) {
                $table->string('regione_nac', 150)->nullable()->after('country_nac');
            }
            if (!Schema::hasColumn('componenti', 'comune_nac')) {
                $table->string('comune_nac', 150)->nullable()->after('regione_nac');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('componenti')) {
            return;
        }

        Schema::table('componenti', function (Blueprint $table) {
            $drop = [];
            foreach (['country_nac', 'regione_nac', 'comune_nac'] as $column) {
                if (Schema::hasColumn('componenti', $column)) {
                    $drop[] = $column;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
