<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comuni', function (Blueprint $table) {
            // Add codice_regione column for denormalized region code
            $table->string('codice_regione', 10)->nullable()->after('sigla_provincia');
            $table->index('codice_regione');
        });

        // Populate codice_regione from province relationship
        $this->populateCodiceRegione();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comuni', function (Blueprint $table) {
            $table->dropIndex(['codice_regione']);
            $table->dropColumn('codice_regione');
        });
    }

    /**
     * Populate codice_regione using province data
     */
    private function populateCodiceRegione(): void
    {
        // Update in batches for performance
        DB::statement('
            UPDATE comuni c
            INNER JOIN provinces p ON c.province_id = p.id
            SET c.codice_regione = p.codice_regione
            WHERE c.codice_regione IS NULL OR c.codice_regione = ""
        ');
    }
};
