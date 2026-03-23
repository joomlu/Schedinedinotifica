<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        if (!Schema::hasColumn('schedina', 'circuito')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->string('circuito', 20)->nullable()->after('scheda');
            });
        }

        DB::table('schedina')
            ->whereNull('circuito')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $circuito = 'schedina';
                    if ((int) ($row->is_arrive ?? 0) === 1) {
                        $circuito = 'arrivi';
                    } elseif (empty($row->scheda)) {
                        $circuito = 'bozza';
                    }

                    DB::table('schedina')
                        ->where('id', $row->id)
                        ->update(['circuito' => $circuito]);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedina') || !Schema::hasColumn('schedina', 'circuito')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            $table->dropColumn('circuito');
        });
    }
};
