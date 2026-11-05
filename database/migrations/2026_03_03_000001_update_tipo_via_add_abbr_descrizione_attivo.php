<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_via', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_via', 'abbr')) {
                $table->string('abbr', 20)->nullable()->after('id');
            }
            if (!Schema::hasColumn('tipo_via', 'descrizione')) {
                $table->string('descrizione', 191)->nullable()->after('abbr');
            }
            if (!Schema::hasColumn('tipo_via', 'attivo')) {
                $table->boolean('attivo')->default(true)->after('descrizione');
            }
        });

        // Backfill abbr/descrizione from nome when available.
        DB::table('tipo_via')->select('id', 'nome', 'abbr', 'descrizione', 'attivo')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $abbr = $row->abbr;
                $descr = $row->descrizione;
                if (!$abbr && $row->nome) {
                    $abbr = mb_substr($row->nome, 0, 10);
                }
                if (!$descr && $row->nome) {
                    $descr = $row->nome;
                }
                DB::table('tipo_via')->where('id', $row->id)->update([
                    'abbr' => $abbr,
                    'descrizione' => $descr,
                    'attivo' => $row->attivo ?? true,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_via', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_via', 'attivo')) {
                $table->dropColumn('attivo');
            }
            if (Schema::hasColumn('tipo_via', 'descrizione')) {
                $table->dropColumn('descrizione');
            }
            if (Schema::hasColumn('tipo_via', 'abbr')) {
                $table->dropColumn('abbr');
            }
        });
    }
};
