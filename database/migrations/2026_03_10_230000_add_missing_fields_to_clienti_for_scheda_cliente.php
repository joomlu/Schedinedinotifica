<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            if (!Schema::hasColumn('clienti', 'sdi_az')) {
                $table->string('sdi_az', 120)->nullable();
            }

            if (!Schema::hasColumn('clienti', 'website')) {
                $table->string('website', 191)->nullable();
            }

            if (!Schema::hasColumn('clienti', 'rilasciato_reg') && Schema::hasColumn('clienti', 'released_reg')) {
                $table->string('rilasciato_reg')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            if (Schema::hasColumn('clienti', 'sdi_az')) {
                $table->dropColumn('sdi_az');
            }

            if (Schema::hasColumn('clienti', 'website')) {
                $table->dropColumn('website');
            }

        });
    }
};
