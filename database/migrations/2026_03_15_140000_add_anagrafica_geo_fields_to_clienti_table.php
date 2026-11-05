<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            if (!Schema::hasColumn('clienti', 'region_reg')) {
                $table->string('region_reg', 150)->nullable()->after('country_reg');
            }

            if (!Schema::hasColumn('clienti', 'cap_reg')) {
                $table->string('cap_reg', 20)->nullable()->after('prov_reg');
            }

            if (!Schema::hasColumn('clienti', 'geo_manual_reg')) {
                $table->boolean('geo_manual_reg')->default(false)->after('cap_reg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            if (Schema::hasColumn('clienti', 'geo_manual_reg')) {
                $table->dropColumn('geo_manual_reg');
            }

            if (Schema::hasColumn('clienti', 'cap_reg')) {
                $table->dropColumn('cap_reg');
            }

            if (Schema::hasColumn('clienti', 'region_reg')) {
                $table->dropColumn('region_reg');
            }
        });
    }
};
