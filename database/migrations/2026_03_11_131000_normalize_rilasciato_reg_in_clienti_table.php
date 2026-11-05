<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        if (!Schema::hasColumn('clienti', 'rilasciato_reg') && Schema::hasColumn('clienti', 'released_reg')) {
            Schema::table('clienti', function ($table) {
                $table->string('rilasciato_reg')->nullable();
            });
        }

        if (Schema::hasColumn('clienti', 'released_reg') && Schema::hasColumn('clienti', 'rilasciato_reg')) {
            DB::table('clienti')
                ->whereNull('rilasciato_reg')
                ->whereNotNull('released_reg')
                ->update(['rilasciato_reg' => DB::raw('released_reg')]);

            Schema::table('clienti', function ($table) {
                $table->dropColumn('released_reg');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        if (!Schema::hasColumn('clienti', 'released_reg')) {
            Schema::table('clienti', function ($table) {
                $table->string('released_reg')->nullable();
            });
        }

        if (Schema::hasColumn('clienti', 'released_reg') && Schema::hasColumn('clienti', 'rilasciato_reg')) {
            DB::table('clienti')
                ->whereNull('released_reg')
                ->whereNotNull('rilasciato_reg')
                ->update(['released_reg' => DB::raw('rilasciato_reg')]);
        }
    }
};

