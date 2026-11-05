<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            if (!Schema::hasColumn('clienti', 'country_doc_reg')) {
                $table->string('country_doc_reg', 150)->nullable()->after('rilasciato_reg');
            }

            if (!Schema::hasColumn('clienti', 'city_doc_reg')) {
                $table->string('city_doc_reg', 150)->nullable()->after('country_doc_reg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            if (Schema::hasColumn('clienti', 'city_doc_reg')) {
                $table->dropColumn('city_doc_reg');
            }

            if (Schema::hasColumn('clienti', 'country_doc_reg')) {
                $table->dropColumn('country_doc_reg');
            }
        });
    }
};
